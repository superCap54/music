<?php
namespace Core\Bulk_post\Controllers;

class Bulk_post extends \CodeIgniter\Controller
{
    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
        include get_module_dir( __DIR__ , 'Libraries/vendor/autoload.php');
        $this->post_model = new \Core\Post\Models\PostModel();
        $this->account_manager_model = new \Core\Account_manager\Models\Account_managerModel();
        $this->workflows_model = new \Core\Post\Models\WorkflowsModel();
        //获取当前用户id
        $this->user_id = get_user("id");
    }

    public function index()
    {
        $post_id = get("post_id");
        $team_id = get_team("id");
        $caption = get("caption");
        //传参
        $is_enabled = get("is_enabled");
//      获取能运行的workflows
        $workflows = $this->workflows_model->getAllWorkflows(true);
//      获取当前用户的workflows状态
        $userWorkflows = $this->workflows_model->getUserWorkflows($this->user_id);
//      创建用户工作流ID到状态的映射表
        $userWorkflowStatusMap = [];
        foreach ($userWorkflows as $userWorkflow) {
            $userWorkflowStatusMap[$userWorkflow['workflow_id']] = [
                'user_workflow_id' => $userWorkflow['user_workflow_id'],
                'is_enabled' => $userWorkflow['is_enabled']
            ];
        }
//      合并数据到workflows数组
        $workflowsWithStatus = [];
        foreach ($workflows as $workflow) {
            $workflowId = $workflow['workflow_id'];

            $mergedWorkflow = $workflow;
            $mergedWorkflow['user_is_enabled'] = false; // 默认值
            $mergedWorkflow['user_workflow_id'] = null; // 默认值

            if (isset($userWorkflowStatusMap[$workflowId])) {
                $mergedWorkflow['user_is_enabled'] = (bool)$userWorkflowStatusMap[$workflowId]['is_enabled'];
                $mergedWorkflow['user_workflow_id'] = $userWorkflowStatusMap[$workflowId]['user_workflow_id'];
            }

            $workflowsWithStatus[] = $mergedWorkflow;
        }

//      根据传入的is_enabled参数过滤结果
        if ($is_enabled !== null) {
            $filterEnabled = (bool)$is_enabled;
            $workflowsWithStatus = array_filter($workflowsWithStatus, function ($workflow) use ($filterEnabled) {
                return $workflow['user_is_enabled'] === $filterEnabled;
            });
        }

//      重新索引数组（如果进行了过滤）
        $workflowsWithStatus = array_values($workflowsWithStatus);
        $post = db_get( "*", TB_POSTS, [ "ids" => $post_id, "team_id" => $team_id ] );
        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "config" => $this->config,
            "post" => json_encode($post),
            "content" => view('Core\Bulk_post\Views\make',[
                'workflows' => $workflowsWithStatus,  // 明确指定变量名
                "post" => $post
            ])
        ];
        return view('Core\Bulk_post\Views\index', $data);
    }
    
    public function index1( $page = false ) {
        $team_id = get_team("id");
        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "config" => $this->config,
            "content" => view('Core\Bulk_post\Views\content', ["config" => $this->config])
        ];

        return view('Core\Bulk_post\Views\index', $data);
    }

    public function download_bulk_template_csv(){
        $filename = FCPATH.get_module_dir(__DIR__, 'Assets/bulk_template.csv');
        if(file_exists($filename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: text/csv');
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
            header('Content-Disposition: attachment; filename="'.basename($filename).'"');
            header('Content-Length: ' . filesize($filename));
            header('Pragma: public');
            flush();
            readfile($filename);
        }else{
            redirect_to( get_module_url() );
        }
    }

    public function save(){
        $data_error = [];
        $data_success = [];
        $team_id = get_team("id");
        $accounts = post("accounts");
        $medias = post("medias");
        $delay = (int)post("delay");
        $advance_options = post("advance_options");
        $post_errors = 0;
        $post_success = 0;

        update_team_data("bulk_delay", $delay);
        
        validate('empty', __('Please select at least a profile'), $accounts);

        $list_accounts = $this->account_manager_model->get_accounts_by( $accounts, "ids" );
        if(empty($list_accounts)){
            validate('empty', __('Accounts selected is inactive. Let re-login and try again'), $list_accounts);
        }


        if(empty($medias)){
            ms([
                "status" => "error",
                "message" => __('Please select bulk template csv file')
            ]);
        }

        $csv = $medias[0];
        $headers = get_header( get_file_url($csv) );
        $headers = array_change_key_case($headers, CASE_LOWER);
        if( !isset( $headers['content-type'] ) ){
            ms([
                "status" => "error",
                "message" => __("Couldn't get file type")
            ]);
        }

        if( $headers['content-type'] != "text/csv" && $headers['content-type'] != "application/octet-stream" ){
            ms([
                "status" => "error",
                "message" => __("Please select bulk template csv file")
            ]);
        }

        $is_tmp_file = false;
        if (stripos($csv, "http://") !== false || stripos($csv, "https://") !== false) { 
            $is_tmp_file = true;
            $csv = save_file($csv);
        }

        $csvReader = new \yidas\csv\Reader( get_file_path($csv) );
        $csvFile = $csvReader->readRows();
        $count_delay = 0;
        
        if($is_tmp_file){
            @unlink(get_file_path($csv));
        }
        
        foreach($csvFile as $key => $row) {

            if( count($row) == 8 && $key != 0 ){

                $caption = trim($row[0]);
                $media = $row[6];
                $link = $row[7];

                $year = $row[1];
                $month = sprintf("%02d", $row[2]);
                $day = sprintf("%02d", $row[3]);
                $hour = sprintf("%02d", $row[4]);
                $minute = sprintf("%02d", $row[5]);

                $date = "{$year}-{$month}-{$day} {$hour}:{$minute}:00";

                if( strtotime($date) > time() ){
                    $time_post = strtotime($date);
                }else{
                    $time_post = time() + $delay*$count_delay*60;
                    $count_delay += 1;
                }

                $check_link = false;
                if (filter_var($link, FILTER_VALIDATE_URL)) {
                    $check_link = true;
                }

                $type = "text";
                if($media != ""){
                    $type = "media";
                }else if($link != ""){
                    $type= "link";
                }

                $postData = [
                    "caption" => $caption,
                    "link" => $link,
                    "medias" => ($media != "")?[$media]:null,
                    "advance_options" => $advance_options,
                ];

                $data = [
                    "team_id" => $team_id,
                    "function" => "post",
                    "type" => $type,
                    "data" => json_encode($postData),
                    "time_post" => $time_post,
                    "delay" => $delay,
                    "repost_frequency" => 0,
                    "repost_until" => NULL,
                    "result" => "",
                    "changed" => time(),
                    "created" => time(),
                ];

                foreach ($list_accounts as $key => $value) {
                    $ids = post("ids")?post("ids"):ids();
                    $data['ids'] = $ids;
                    $data['account_id'] = $value->id;
                    $data['social_network'] = $value->social_network;
                    $data['category'] = $value->category;
                    $data['api_type'] = $value->login_type;
                    $data['account'] = $value;
                    $validator = $this->post_model->validator([ (object)$data ]);

                    if($validator['status'] == "error"){
                        $post_errors++;
                        $data['status'] = 4;
                        $data['result'] = $validator['message'];
                        $data_error[] = (object)$data;
                    }else{
                        $post_success++;
                        $data['status'] = 1;
                        unset($data['account']);
                        $data_success[] = (array)$data;
                    }
                    
                }
            }
        }

        if(!empty($data_success)){
            db_insert(TB_POSTS, $data_success);
        }

        ms([
            "status"  => ($post_success != 0)?"success":"error",
            "message" => sprintf(__(" You're scheduling %s posts to %s social accounts."), $post_success, count($list_accounts))
        ]);
    }
}