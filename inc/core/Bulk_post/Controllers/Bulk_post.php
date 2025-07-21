<?php
namespace Core\Bulk_post\Controllers;

use function JBZoo\Data\json;

class Bulk_post extends \CodeIgniter\Controller
{
    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
        include get_module_dir( __DIR__ , 'Libraries/vendor/autoload.php');
        $this->post_model = new \Core\Post\Models\PostModel();
        $this->account_manager_model = new \Core\Account_manager\Models\Account_managerModel();
        $this->workflows_model = new \Core\Post\Models\WorkflowsModel();
        $this->user_workflows_model = new \Core\Users\Models\UserWorkflowsModel();

        //获取当前用户id
        $this->user_id = get_user("id");
        $this->team_id = get_team('id');
    }

    public function add_widget()
    {
//      获取能运行的workflows
        $workflows = $this->workflows_model->getAllWorkflows(true);

        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "config" => $this->config,
            "content" => view('Core\Bulk_post\Views\make',[
                'workflows' => $workflows,  // 明确指定变量名
            ])
        ];
        return view('Core\Bulk_post\Views\index', $data);
    }

    public function add_workflow()
    {
        // 获取当前用户ID
        if (empty($this->user_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not authenticated'
            ]);
        }
        $team_id = get_team("id");

        // 获取输入数据
        $workflow_id = post('workflow_id');
        $workflow_name = post('workflow_name');

        // 检查用户已有多少个相同workflow_id的工作流
        $count = $this->user_workflows_model
            ->where('user_id', $this->user_id)
            ->where('workflow_id', $workflow_id)
            ->countAllResults();

        if ($count >= 10) { // 这里先默认最多允许10个，理论上这里是加用户权限，权限越高 这里数量也多，但是没做。待开发
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You can only create up to 10 instances of this workflow'
            ]);
        }

        // 准备插入数据
        $data = [
            'user_id' => $this->user_id,
            'team_id' => $team_id,
            'workflow_id' => $workflow_id,
            'workflow_name' => $workflow_name,
            'accounts' => '',
            'title' => '', // 默认空标题
            'descript' => '', // 默认空描述
            'category' => '', // 默认空分类
            'tags' => '', // 默认空标签
            'custom_data' => json_encode([]), // 默认空JSON对象
            'is_enabled' => 0, // 默认启用
            'schedule_type' => 'none', // 默认无计划
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            // 直接插入新工作流（不再检查是否已存在）
            $insert_id = $this->user_workflows_model->insert($data);

            if (!$insert_id) {
                throw new \Exception('Failed to insert workflow');
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Workflow added successfully',
                'data' => [
                    'user_workflow_id' => $insert_id
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to add workflow: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to add workflow: ' . $e->getMessage()
            ]);
        }
    }

    public function index()
    {
        // 获取当前用户的所有工作流
        $user_workflows = $this->user_workflows_model->get_list($this->user_id,null,true);
        //获取该用户是否绑定谷歌网盘
        $google_drive_token = db_get('*', 'sp_google_drive_tokens', ['user_id' => $this->user_id]);
        $is_auth_google_drive = empty($google_drive_token) ? 0 : $google_drive_token->id;

        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "config" => $this->config,
            "content" => view('Core\Bulk_post\Views\user_workflows', [
                "config" => $this->config,
                "user_workflows" => $user_workflows,
                'is_auth_google_drive' => $is_auth_google_drive
            ])
        ];

        return view('Core\Bulk_post\Views\index', $data);
    }

    public function update_workflow()
    {
        // 验证用户身份
        $user_id = $this->user_id;
        if (empty($user_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not authenticated'
            ]);
        }

        // 获取表单数据
        $workflow_id = post('workflow_id');
        $accounts = post("accounts");
        $title = post('title');
        $description = post('description');
        $category = post('category');
        $tags = post('tags');
        $config_values = post('config_values');

        // 验证必填字段
        if (empty($workflow_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Workflow ID is required'
            ]);
        }

        try {
            // 使用builder确保类型安全
            $builder = $this->user_workflows_model->builder();

            // 检查工作流是否存在且属于当前用户
            $workflow = $builder->where('user_id', $user_id)
                ->where('user_workflow_id', $workflow_id)
                ->get()
                ->getRowArray();  // 明确指定返回数组

            if (empty($workflow)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Workflow not found or you do not have permission'
                ]);
            }

            // 准备更新数据
            $updateData = [
                'title' => $title ?? $workflow['title'],
                'descript' => $description ?? $workflow['descript'],
                'category' => $category ?? $workflow['category'],
                'tags' => $tags ?? $workflow['tags'],
                'custom_data' => $config_values ?? $workflow['custom_data'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // 处理账户数据
            if (!empty($accounts)) {
                // 确保accounts是数组
                $accountsArray = is_array($accounts) ? $accounts : [$accounts];
                $updateData['accounts'] = json_encode($accountsArray);
            } else {
                // 如果没有提供accounts，保持原值
                $updateData['accounts'] = $workflow['accounts'];
            }

            // 执行更新
            $builder->where('user_workflow_id', $workflow['user_workflow_id'])
                ->set($updateData)
                ->update();

            // 获取受影响的行数 - 正确的方式
            $affectedRows = $this->user_workflows_model->db->affectedRows();
            if ($affectedRows === 0) {
                // 如果没有行被更新，可能是数据没有变化
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'No changes detected',
                    'data' => $workflow
                ]);
            }
            // 获取更新后的工作流数据
            $updatedWorkflow = $builder->where('user_workflow_id', $workflow_id)
                ->get()
                ->getRowArray();

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Workflow updated successfully',
                'data' => $updatedWorkflow
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to update workflow: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update workflow: ' . $e->getMessage()
            ]);
        }
    }

    public function update_setting() {
        // 获取当前用户ID
        $user_id = $this->user_id;
        if (!$user_id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        // 获取POST数据
        $workflow_id = $this->request->getPost('workflow_id');
        $workflow_name = $this->request->getPost('workflow_name');
        $status = $this->request->getPost('status');
        $schedule_json = $this->request->getPost('schedule');

        // 基本验证
        if (empty($workflow_id) || empty($workflow_name)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Required fields are missing'
            ], 400);
        }

        // 解析调度数据
        $schedule = json_decode($schedule_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid schedule data'
            ], 400);
        }

        // 准备更新数据
        $update_data = [
            'workflow_name' => $workflow_name,
            'is_enabled' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // 处理调度数据
        $schedule_type = 'none';
        $schedule_time = null;
        $schedule_days = null;
        $schedule_date = null;
        $next_run_at = null;

        switch ($schedule['type']) {
            case 'daily':
                $schedule_type = 'daily';
                $schedule_time = $schedule['time'] . ':00'; // 添加秒数
                // 计算下次运行时间（今天或明天同一时间）
                $next_run_at = $this->calculateNextRunTime('daily', null, $schedule['time']);
                break;

            case 'weekly':
                $schedule_type = 'weekly';
                $schedule_time = $schedule['time'] . ':00';
                $schedule_days = implode(',', $schedule['days']);
                // 计算下次运行时间（下一个选定的日期）
                $next_run_at = $this->calculateNextRunTime('weekly', $schedule['days'], $schedule['time']);
                break;

            case 'once':
                $schedule_type = 'once';
                $schedule_date = date('Y-m-d H:i:s', strtotime($schedule['datetime']));
                $next_run_at = $schedule_date; // 一次性任务的下次运行时间就是预定时间
                break;
        }

        // 添加调度数据到更新数组
        $update_data['schedule_type'] = $schedule_type;
        $update_data['schedule_time'] = $schedule_time;
        $update_data['schedule_days'] = $schedule_days;
        $update_data['schedule_date'] = $schedule_date;
        $update_data['next_run_at'] = $next_run_at;

        // 更新数据库
        $db = db_connect();
        $builder = $db->table('sp_user_workflows');
        $builder->where('user_workflow_id', $workflow_id)
            ->where('user_id', $user_id);

        $result = $builder->update($update_data);

        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Workflow settings updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update workflow settings'
            ], 500);
        }
    }

    /**
     * 计算下次运行时间
     */
    private function calculateNextRunTime($type, $days = null, $time = null) {
        if ($type === 'none') {
            return null;
        }

        $now = new \DateTime('now', new \DateTimeZone('Asia/Shanghai'));
        $next_run = clone $now;

        switch ($type) {
            case 'daily':
                list($hour, $minute) = explode(':', $time);
                $next_run->setTime($hour, $minute, 0);

                // 如果今天这个时间已经过了，就设置为明天
                if ($next_run <= $now) {
                    $next_run->modify('+1 day');
                }
                break;

            case 'weekly':
                list($hour, $minute) = explode(':', $time);
                $current_day = $now->format('N'); // 1-7 (Monday-Sunday)
                $next_day = null;

                // 找出下一个要运行的工作日
                foreach ($days as $day) {
                    $day = (int)$day;
                    if ($day > $current_day) {
                        $next_day = $day;
                        break;
                    }
                }

                // 如果本周没有更多的工作日，就选下周的第一个工作日
                if ($next_day === null) {
                    $next_day = min($days);
                    $next_run->modify('+' . (7 - $current_day + $next_day) . ' days');
                } else {
                    $next_run->modify('+' . ($next_day - $current_day) . ' days');
                }

                $next_run->setTime($hour, $minute, 0);
                break;

            case 'once':
                // 已经在主方法中处理
                break;
        }

        return $next_run->format('Y-m-d H:i:s');
    }

    public function delete_user_workflow()
    {
        // 获取当前用户ID
        $user_id = $this->user_id;
        if (empty($user_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not authenticated'
            ]);
        }

        // 获取要删除的工作流ID
        $user_workflow_id = post('user_workflow_id');
        if (empty($user_workflow_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Workflow ID is required'
            ]);
        }

        try {
            // 方法1：使用 builder 确保类型
            $builder = $this->user_workflows_model->builder();
            $workflow = $builder->where('user_id', $user_id)
                ->where('user_workflow_id', $user_workflow_id)
                ->get(1)
                ->getRowArray();

            if (empty($workflow)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Workflow not found or you do not have permission'
                ]);
            }

            // 删除工作流
            $deleted = $this->user_workflows_model->delete($user_workflow_id);

            if (!$deleted) {
                throw new \Exception('Failed to delete workflow');
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Workflow deleted successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete workflow: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete workflow: ' . $e->getMessage()
            ]);
        }
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

    public function cron()
    {
        // 在cron()方法开头添加
        $this->user_workflows_model->cleanup_stale_workflows();
        // 0. 设置无限执行时间（仅限CLI模式）
        if (php_sapi_name() === 'cli') {
            set_time_limit(0);
        }

        // 1. 获取需要执行的工作流 这里返回的是二维数组对象或者是空数组
        $max_workflows_per_minute = 5; // 根据服务器性能调整
        $workflows = $this->user_workflows_model->get_due_workflows($max_workflows_per_minute);
        if (empty($workflows)) {
            echo date('Y-m-d H:i:s') . " - 没有需要执行的工作流\n";
            return;
        }

        $childProcesses = [];
        $maxWaitTime = 45; // 设置最大等待时间（秒），确保小于cron间隔

        foreach ($workflows as $workflow) {
            try {
                // 检查进程负载
                if ($this->is_server_overloaded()) {
                    echo "服务器负载过高，暂停处理新任务\n";
                    break;
                }
                // 2. 锁定工作流（防止并发执行）
//                $this->user_workflows_model->lock_workflow($workflow->user_workflow_id);

                // 3. 执行工作流
//                $result = $this->execute_workflow_with_timeout($workflow);

                // 4. 计算下次执行时间
//                $nextRun = $this->calculateNextRunTime(
//                    $workflow->schedule_type,
//                    $workflow->schedule_days ? explode(',', $workflow->schedule_days) : null,
//                    $workflow->schedule_time
//                );
//
//                // 5. 更新工作流状态
//                $this->user_workflows_model->update_workflow_after_run(
//                    $workflow->user_workflow_id,
//                    $nextRun
//                );

//                if ($result['status'] === 'completed') {
//                    echo date('Y-m-d H:i:s') . " - 工作流标记为已完成: {$workflow->workflow_name} ".$result['message']."\n";
//                } else {
//                    echo date('Y-m-d H:i:s') . " - 成功执行工作流: {$workflow->workflow_name}\n";
//                }

                // 使用PCNTL处理超时问题
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new \Exception('无法创建子进程');
                } elseif ($pid) {
                    // 父进程记录子进程PID
                    $childProcesses[] = $pid;
                } else {
                    // 🔸 关键修改：创建守护进程
                    $daemonPid = pcntl_fork();
                    if ($daemonPid == -1) {
                        exit(1); // 创建失败直接退出
                    } elseif ($daemonPid) {
                        exit(0); // 父进程(子进程)立即退出
                    }
                    // 🔸 守护进程(孙进程)执行实际任务
                    $this->handle_child_process($workflow);
                    exit(0);
                }
            } catch (\Exception $e) {
                // 6. 错误处理
                log_message('error', "工作流执行失败: {$workflow->user_workflow_id} - " . $e->getMessage());
                $this->user_workflows_model->unlock_workflow($workflow->user_workflow_id);
                echo date('Y-m-d H:i:s') . " - 工作流执行失败: {$workflow->workflow_name} - " . $e->getMessage() . "\n";
            }
        }
        $startTime = time();
        while (count($childProcesses) > 0) {
            foreach ($childProcesses as $key => $pid) {
                $res = pcntl_waitpid($pid, $status, WNOHANG);

                if ($res == -1 || $res > 0) {
                    // 进程已退出
                    unset($childProcesses[$key]);
                }
            }

            // 超时检查（避免阻塞下次cron）
            if ((time() - $startTime) > $maxWaitTime) {
                break;
            }

            usleep(100000); // 休眠100ms减少CPU占用
        }
    }


    private function execute_workflow($workflow)
    {
        $this->temp_files_to_delete = []; // 初始化临时文件数组
        //workflow里面的accounts存的是空字符串或者空数组或者是一维数组。这里存的是YouTube的ID
        //workflow里面的custom_data存的是工作流额外需要用到的数据
        //先获取这个workflow是否是从网盘获取文件自动发布的逻辑代码
        $custom_data = json_decode($workflow->custom_data,true);
        $user_id = $workflow->user_id;
        $team_id = $workflow->team_id;
        $accounts = json_decode($workflow->accounts,true);
        if (empty($accounts)) {
            return [
                'status' => 'completed',
                'message' => 'Accounts Is Empty'
            ];
        }

        $list_data = [];
        $this->post_model = new \Core\Post\Models\PostModel();

        //这是Google Drive自动发的逻辑,如果google drive配置了，并且绑定了YouTube账号才会进入
        if (isset($custom_data['google_drive']) && !empty($custom_data['google_drive'])) {
            //1.先刷新GoogleDrive的Token
            $google_drive_id = $custom_data['google_drive'];
            $googleDriveToken = db_get('*', 'sp_google_drive_tokens', ['id' => $google_drive_id]);
            if (!empty($googleDriveToken)) {
                // 刷新谷歌token
                $google = new \Core\File_manager\Controllers\Google();
                $accessTokenArr = $google->refresh_token_from_user($user_id);
                $accessToken = $accessTokenArr['access_token'];
                //2.获取这个用户的谷歌网盘MP4文件
                $result = $google->get_mp4_file($accessToken);
                //如果网盘是空的 直接return结束就好了
                if (empty($result)){
                    return [
                        'status' => 'completed',
                        'message' => 'Google Drive is empty'
                    ];
                }
                //3. 走下载链接
                $file_names = $this->generateRandomFilename("mp4");
                // 从URL中提取文件ID
                $file_id = $result['id'];
                // 下载文件到临时目录
                $temp_file = WRITEPATH . 'uploads/' . uniqid() . '_' . $file_names;
                if (!empty($user_id)) {
                    $data = [
                        'access_token' => $accessToken,
                        'result' => $result,
                        'file_id' => $file_id,
                        'file_names' => $file_names,
                        'temp_file' => $temp_file,
                    ];
                }
                $google->download_file($file_id, $temp_file,$accessToken);
                $medias[] = str_replace(WRITEPATH, "", $temp_file);
                // 记录需要删除的临时文件
                $this->temp_files_to_delete[] = $temp_file; // 添加到清理列表
                // 准备上传到YouTube的数据
                $type = "media";

                $advance_options = [
                    'fb_post_type' => 'default',
                    'fb_story_link' => '',
                    'ig_post_type' => 'media',
                    'ig_first_comment' => '',
                    'youtube_title' => $workflow->title,
                    'youtube_category' => $workflow->category,
                    'youtube_tags' => $workflow->tags,
                ];
                $postData = [
                    "caption" => $workflow->descript,
                    "link" => '',
                    "medias" => $medias,
                    "advance_options" => $advance_options,
                ];
                $data = [
                    "team_id" => $team_id,
                    "function" => "post",
                    "type" => $type,
                    "data" => json_encode($postData),
                    "time_post" => date('d-m-Y H:i'),
                    "delay" => 5,
                    "repost_frequency" => 0,
                    "repost_until" => date('d-m-Y H:i'),
                    "result" => "",
                    "status" => 1,
                    "changed" => time(),
                    "created" => time(),
                ];
                $list_accounts = $this->account_manager_model->get_accounts_by($accounts, "ids", 1, $team_id, true);
                foreach ($list_accounts as $key => $value) {
                    $ids = post("ids") ? post("ids") : ids();
                    $data['ids'] = $ids;
                    $data['account_id'] = $value->id;
                    $data['social_network'] = $value->social_network;
                    $data['category'] = $value->category;
                    $data['api_type'] = $value->login_type;
                    $data['proxy_info'] = $value->proxy_info;
                    $list_data[] = (object)$data;
                }
                $validator = $this->post_model->validator($list_data);
                $social_can_post = json_decode($validator["can_post"]);
                if (!empty($social_can_post) || $validator["status"] == "success") {
                    $this->post_model->post($list_data, $social_can_post,$team_id,1);
                    try {
                        $google->delete_file($file_id, $accessToken);
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to delete Google Drive file: ' . $e->getMessage());
                    }
                }
            }else{
                return [
                    'status' => 'completed',
                    'message' => 'Google Drive is Not Connnected'
                ];
            }
        }
        return ['status' => 'success', 'message' => 'Workflow executed'];
    }

    function generateRandomFilename($extension = '')
    {
        $prefix = bin2hex(random_bytes(4)); // 8字符随机前缀
        $filename = uniqid($prefix . '_', true); // 生成唯一ID
        $filename = str_replace('.', '', $filename); // 移除uniqid中的点

        if (!empty($extension)) {
            $filename .= '.' . ltrim($extension, '.');
        }

        return $filename;
    }

    private function extractGoogleDriveFileId($url)
    {
        $patterns = [
            '/\/file\/d\/([^\/]+)/',
            '/id=([^&]+)/',
            '/([A-Za-z0-9_-]{25,})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return false;
    }


    /**
     * 删除临时文件
     * @param array $files 要删除的文件路径数组
     */
    private function deleteTempFiles(array $files)
    {
        if (empty($files)) {
            return true;
        }
        foreach ($files as $file) {
            if (file_exists($file)) {
                try {
                    unlink($file);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to delete temp file: ' . $file . ' - ' . $e->getMessage());
                }
            }
        }
    }
    private function is_server_overloaded()
    {
        // 获取内存限制（带单位的字符串，如 "128M"）
        $memory_limit_str = ini_get('memory_limit');

        // 获取当前内存使用量（字节）
        $used_memory = memory_get_usage(true);

        // 将内存限制转换为字节
        $memory_limit_bytes = $this->convert_memory_to_bytes($memory_limit_str);
        // 检查是否超过85%限制
        return $used_memory > ($memory_limit_bytes * 0.85);
    }

    /**
     * 将带单位的内存字符串转换为字节数
     */
    private function convert_memory_to_bytes($memory_limit)
    {
        // 去除空格并获取单位
        $unit = strtoupper(substr(trim($memory_limit), -1));
        $number = (float)trim($memory_limit);

        // 根据单位转换
        switch ($unit) {
            case 'G':
                return $number * 1024 * 1024 * 1024;
            case 'M':
                return $number * 1024 * 1024;
            case 'K':
                return $number * 1024;
            default: // 无单位的情况（直接返回字节数）
                return $number;
        }
    }

    private function execute_workflow_with_timeout($workflow)
    {
        // 使用PCNTL扩展（如果可用）
        if (function_exists('pcntl_fork')) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                // Fork失败，回退到普通执行
                $this->execute_workflow($workflow);
            } elseif ($pid) {
                // 父进程：等待子进程完成
                $status = null;
                pcntl_waitpid($pid, $status);
            } else {
                // 子进程：执行任务
                $this->execute_workflow($workflow);
                exit(0); // 子进程结束
            }
        } else {
            // 没有PCNTL：使用简单超时保护
            $start = time();
            $this->execute_workflow($workflow);

            // 记录执行时间用于监控
            $duration = time() - $start;
            if ($duration > 30) {
                log_message('warning', "工作流 {$workflow->workflow_id} 执行超长: {$duration}秒");
            }
        }
    }








    /**
     * 子进程处理逻辑（解决数据库连接和文件清理问题）
     */
    private function handle_child_process($workflow)
    {
        try {
            // 关闭父进程的数据库连接
            $this->close_parent_db_connections();

            // 重新初始化数据库连接
            $this->reinitialize_db_connection();

            // 锁定工作流（在子进程中锁定）
            $this->user_workflows_model->lock_workflow($workflow->user_workflow_id);

            // 执行工作流
            $result = $this->execute_workflow($workflow);

            // 计算下次执行时间
            $nextRun = $this->calculateNextRunTime(
                $workflow->schedule_type,
                $workflow->schedule_days ? explode(',', $workflow->schedule_days) : null,
                $workflow->schedule_time
            );

            // 更新工作流状态
            $this->user_workflows_model->update_workflow_after_run(
                $workflow->user_workflow_id,
                $nextRun
            );

            if ($result['status'] === 'completed') {
                echo date('Y-m-d H:i:s') . " - 工作流完成: {$workflow->workflow_name}\n";
            }
        } catch (\Exception $e) {
            // 错误处理
            log_message('error', "工作流执行失败: {$workflow->user_workflow_id} - " . $e->getMessage());

            // 确保异常时解锁工作流
            $this->user_workflows_model->unlock_workflow($workflow->user_workflow_id);
        } finally {
            // 确保临时文件被清理（即使发生异常）
            $this->cleanup_temp_files();
        }
    }

    /**
     * 关闭父进程的数据库连接
     */
    private function close_parent_db_connections()
    {
        // 关闭CodeIgniter默认连接
        if ($db = \Config\Database::connect()) {
            $db->close();
        }

        // 关闭模型中可能存在的连接
        $models = [
            $this->post_model,
            $this->account_manager_model,
            $this->workflows_model,
            $this->user_workflows_model
        ];

        foreach ($models as $model) {
            if (property_exists($model, 'db') && $model->db instanceof \CodeIgniter\Database\BaseConnection) {
                $model->db->close();
            }
        }
    }

    /**
     * 重新初始化数据库连接
     */
    private function reinitialize_db_connection()
    {
        // 重新实例化模型
        $this->post_model = new \Core\Post\Models\PostModel();
        $this->account_manager_model = new \Core\Account_manager\Models\Account_managerModel();
        $this->workflows_model = new \Core\Post\Models\WorkflowsModel();
        $this->user_workflows_model = new \Core\Users\Models\UserWorkflowsModel();
    }

    /**
     * 清理临时文件（解决文件残留问题）
     */
    private function cleanup_temp_files()
    {
        if (!empty($this->temp_files_to_delete)) {
            foreach ($this->temp_files_to_delete as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
            $this->temp_files_to_delete = [];
        }
    }
}