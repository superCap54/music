<?php
namespace Core\Proxies\Controllers;

class Proxies extends \CodeIgniter\Controller
{
    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
        $this->model = new \Core\Proxies\Models\ProxiesModel();
    }
    
    public function index( $page = false ) {
        $team_id = get_team("id");
        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc']
        ];

        switch ( $page ) {
            case 'update':
                $item = false;
                $ids = uri('segment', 4);
                if( $ids ){
                    $item = db_get("*", TB_PROXIES, [ "ids" => $ids, "team_id" => $team_id ]);
                }

                $data['content'] = view('Core\Proxies\Views\update', ["result" => $item]);
                break;

            case 'assign':
                $start = 0;
                $limit = 1;

                $pager = \Config\Services::pager();
                $total = $this->model->get_list_assigned(false);
                $proxies = db_fetch("*", TB_PROXIES, ["status" => 1, "team_id" => $team_id], "id", "DESC");
                $proxyMap = [];
                foreach ($proxies as $proxy) {
                    $proxyMap[$proxy->id] = $proxy;
                }
                // 获取账号数据
                $accounts = db_fetch("*", TB_ACCOUNTS, ["team_id" => $team_id, "status" => 1], "id", "DESC");
                $processedAccounts = [];
                foreach ($accounts as $account) {
                    $accountData = clone $account;
                    if (!empty($account->proxy) && isset($proxyMap[$account->proxy])) {
                        $accountData->proxy_info = $proxyMap[$account->proxy];
                    } else {
                        $accountData->proxy_info = null;
                    }
                    $processedAccounts[] = $accountData;
                }


                $datatable = [
                    "responsive" => true,
                    "columns" => [
                        "id" => __("ID"),
                        "account_info" =>  __("Account info"),
                        "social_network" => __("Social network"),
                        "proxy_assigned" => __("Proxy assigned"),
                        "location" => __("Proxy location"),
                        "Status" => __("Status"),
                    ],
                    "total_items" => $total,
                    "per_page" => 50,
                    "current_page" => 1,
                ];

                $data_content = [
                    'start' => $start,
                    'limit' => $limit,
                    'total' => $total,
                    'pager' => $pager,
                    'datatable'  => $datatable,
                    'proxies'  => $proxies,
                    'config' => $this->config,
                    'accounts' => $processedAccounts, // 使用处理后的账号数据
                ];

                $data['content'] = view('Core\Proxies\Views\list_assigned', $data_content);
                break;

            case 'import':
                $data['content'] = view('Core\Proxies\Views\import', []);
                break;
            
            default:
                $start = 0;
                $limit = 1;

                $pager = \Config\Services::pager();
                $total = $this->model->get_list(false);

                $datatable = [
                    "responsive" => true,
                    "columns" => [
                        "id" => __("ID"),
                        "Proxy" =>  __("Proxy"),
                        "Location" =>  __("Location"),
                        "Status" => __("Status"),
                        "created" => __("Created"),
                    ],
                    "total_items" => $total,
                    "per_page" => 50,
                    "current_page" => 1,
                ];

                $data_content = [
                    'start' => $start,
                    'limit' => $limit,
                    'total' => $total,
                    'pager' => $pager,
                    'datatable'  => $datatable,
                    'config' => $this->config
                ];

                $data['content'] = view('Core\Proxies\Views\list', $data_content);
                break;
        }

        return view('Core\Proxies\Views\index', $data);
    }

    public function ajax_list(){
        $total_items = $this->model->get_list(false);
        $result = $this->model->get_list(true);
        // 检测每个代理的状态
        foreach($result as $item) {
            $item->is_active = $this->model->check_proxy_status($item->proxy);
        }

        $data = [
            "result" => $result
        ];
        ms( [
            "total_items" => $total_items,
            "data" => view('Core\Proxies\Views\ajax_list', $data)
        ] );
    }

    public function ajax_list_assigned(){
        $total_items = $this->model->get_list_assigned(false);
        $result = $this->model->get_list_assigned(true);
        $data = [
            "result" => $result
        ];
        ms( [
            "total_items" => $total_items,
            "data" => view('Core\Proxies\Views\ajax_list_assigned', $data)
        ] );
    }

    public function save($ids = "")
    {
        $team_id = get_team("id");
        $status = post('status');
        $proxy = post('proxy');
        $plans = post('plans');

        if(!$plans)  $plans = [];

        validate('null', __('Proxy'), $proxy);
        $location = proxy_location($proxy);
        if(!$location){
            ms([
                "status" => "error",
                "message" => __('Proxy format is incorrect')
            ]);
        }

        $item = db_get("*", TB_PROXIES, ["ids" => $ids, "team_id" => $team_id]);
        if(!$item){
            $item = db_get("*", TB_PROXIES, ["proxy" => $proxy, "team_id" => $team_id]);
            validate('not_empty', __('This proxy already exists'), $item);

            db_insert(TB_PROXIES , [
                "ids" => ids(),
                "team_id" => $team_id,
                "is_system" => 0,
                "proxy" => $proxy,
                "location" => $location,
                "plans" => "",
                "status" => $status,
                "changed" => time(),
                "created" => time()
            ]);
        }else{
            $item = db_get("*", TB_PROXIES, ["ids !=" => $ids, "proxy" => $proxy, "team_id" => $team_id]);
            validate('not_empty', __('This proxy already exists'), $item);

            db_update(
                TB_PROXIES, 
                [
                    "proxy" => $proxy,
                    "location" => $location,
                    "plans" => "",
                    "status" => $status,
                    "changed" => time()
                ], 
                ["ids" => $ids]
            );
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);
    }

    public function do_assign($ids = ""){
        $team_id = get_team("id");
        $proxy = post("proxy");
        $ids = post('ids');

        if( !$proxy ){
            ms([
                "status" => "error",
                "message" => __('Please select a proxy to can assign proxy')
            ]);
        }

        if( empty($ids) && !is_array($ids) ){
            ms([
                "status" => "error",
                "message" => __('Please select an account to can assign proxy')
            ]);
        }

  
        foreach ($ids as $id) 
        {
            $account = db_get("*", TB_ACCOUNTS, ["ids" => $id, "team_id" => $team_id]);
            validate('empty', __('Cannot find account to assign proxy'), $account);

            $proxy_item = db_get("*", TB_PROXIES, ["id" => $proxy, "team_id" => $team_id]);
            validate('empty', __('This proxy does not exist'), $proxy_item);

            db_update(
                TB_ACCOUNTS, 
                ["proxy" => $proxy_item->id], 
                [ "id" => $account->id, "team_id" => $team_id ]
            );
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);
    }

    public function remove_assign($ids = ""){
        $team_id = get_team("id");
        $ids = post('ids');

        if( empty($ids) && !is_array($ids) ){
            ms([
                "status" => "error",
                "message" => __('Please select an account to can assign proxy')
            ]);
        }

        foreach ($ids as $id) 
        {
            db_update(
                TB_ACCOUNTS, 
                ["proxy" => ""], 
                [ "ids" => $id, "team_id" => $team_id ]
            );
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);
    }

    public function download_example_upload_csv(){
        $filename = get_module_dir(__DIR__, 'Assets/csv_template.csv');
        if(file_exists($filename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
            header('Content-Disposition: attachment; filename="'.basename($filename).'"');
            header('Content-Length: ' . filesize($filename));
            header('Pragma: public');
            flush();
            readfile($filename);
            redirect_to( get_module_url() );
        }else{
            redirect_to( get_module_url() );
        }
    }

    public function do_import_proxy(){
        $team_id = get_team("id");
        $team_id = get_team("id");
        $max_size = 50*1024;
        $file_path = "";

        if(!empty($_FILES) && is_array($_FILES['files']['name'])){
            $files = $this->request->getFiles();
            if(empty( $files )){
                ms([
                    "status" => "error",
                    "message" => __('Cannot found files csv to upload')
                ]);
            }
            // 2. 获取文件对象
            $file = $files['files'][0];

            // 3. 手动验证文件后缀名
            $allowedExts = ['csv', 'xlsx','xls']; // 允许的后缀列表
            $originalName = $file->getClientName(); // 获取原始文件名
            $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)); // 提取后缀
            if (!in_array($fileExt, $allowedExts)) {
                ms([
                    "status" => "error",
                    "message" => "The filetype you are attempting to upload is not allowed"
                ]);
            }

            $file_size = $file->getSize();
            if($max_size < $file_size){
                ms([
                    "status" => "error",
                    "message" => __( sprintf("Unable to upload a file larger than %sKB, This File %sKB", $max_size,$file_size) )
                ]);
            }

            if ($files) {
                if (isset($files['files'])) {
                    $file = $files['files'][0];
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(WRITEPATH.'uploads', $newName);
                        $file_path = WRITEPATH.'uploads/'.$newName;
                    }
                }
            }
        }
        if($file_path == ""){
            ms([
                "status" => "error",
                "message" => __("Upload csv file failed.")
            ]);
        }

        $csvReader = new \yidas\csv\Reader($file_path);
        $csvFile = $csvReader->readRows();
        $headers = [];
        $proxies = [];
        $localhostList = [];

        foreach($csvFile as $key => $row) {
            if( $key != 0 ){
                if(is_array($row )){
                    $proxy = $row[0];
                    $proxy = str_replace("+", "", $proxy);
                    $proxy = str_replace(" ", "", $proxy);
                    $proxy = str_replace("'", "", $proxy);
                    $proxy = str_replace("`", "", $proxy);
                    $proxy = str_replace("\"", "", $proxy);
                    $proxy = trim($proxy);
                    //$limit = (int)$row[1];
                }
                $location = proxy_location($proxy);
                $localhostList[] = $location;
                if($location){
                    $proxies[] = [
                        "ids" => ids(),
                        "team_id" => get_team("id"),
                        "is_system" => 0,
                        "proxy" => $proxy,
                        "location" => $location,
                        //"limit" => $limit,
                        "plans" => "",
                        "status" => 1,
                        "changed" => time(),
                        "created" => time()
                    ];
                }
            }else{
                if(!empty($row)){
                    foreach ($row as $pos => $value) {
                        if($pos != 0){
                            $headers[] = $value;
                        }
                    }
                }
            }
        }
        if(!empty($proxies)){
            db_insert( TB_PROXIES, $proxies );
        }else{
            ms([
                "status" => "error",
                "message" => __("CSV内容格式有误")
            ]);
        }

        unlink($file_path);

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);
    }

    

    public function delete( $ids = '' ){
        $team_id = get_team("id");
        if($ids == ''){
            $ids = post('ids');
        }

        if( empty($ids) ){
            ms([
                "status" => "error",
                "message" => __('Please select an item to delete')
            ]);
        }

        if( is_array($ids) )
        {
            foreach ($ids as $id) 
            {
                db_delete(TB_PROXIES, ['ids' => $id, "team_id" => $team_id]);
            }
        }
        elseif( is_string($ids) )
        {
            db_delete(TB_PROXIES, ['ids' => $ids, "team_id" => $team_id]);
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);

    }
}