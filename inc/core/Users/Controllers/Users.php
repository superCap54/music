<?php
namespace Core\Users\Controllers;

class Users extends \CodeIgniter\Controller
{
    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
        $this->model = new \Core\Users\Models\UsersModel();
    }
    
    public function index( $page = false ) {
        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            'config' => $this->config
        ];
        switch ( $page ) {
            case 'update':
                $item = false;
                $ids = uri('segment', 4);
                if( $ids ){
                    $item = db_get("*", TB_USERS, [ "ids" => $ids ]);
                }

                $plans = db_fetch("*", TB_PLANS, "", "id", "ASC");
                $group_roles = db_fetch("*", TB_ROLES, "", "id", "ASC");

                $data['content'] = view('Core\Users\Views\update', ["result" => $item, 'plans' => $plans, "group_roles" => $group_roles, 'config' => $this->config]);
                break;

            case 'music':
                $ids = uri('segment', 4);
                $page = uri('segment', 5);
                // 确保页码是整数
                $page = (int)$page;
                if ($page < 1) {
                    $page = 1;
                }
                $user = db_get("*", TB_USERS, [ "ids" => $ids ]);

                // 检查用户是否存在
                if(empty($user)) {
                    redirect_to( get_module_url() );
                }

                $this->musicModel = new \Core\Music\Models\MusicModel();

                // 获取音乐总数
                $musicTotal = $this->musicModel->getTotalMusic();

                // 分页设置
                $per_page = 30;
                $total_pages = ceil($musicTotal / $per_page);
                $page = max(1, min($page, $total_pages));
                $offset = ($page - 1) * $per_page;

                // 获取音乐列表 - 确保返回数组
                $music_list = $this->musicModel->getMusicList($per_page, $offset);
                // 确保 music_list 是数组
                if (!is_array($music_list)) {
                    $music_list = [];
                }

                // 获取当前页所有音乐的ID
                $music_ids = array_column($music_list, 'id');

                // 批量查询用户对这些音乐的授权状态
                $licenses = [];
                if (!empty($music_ids)) {
                    // 使用 CodeIgniter 4 的查询构建器
                    $db = \Config\Database::connect();
                    $builder = $db->table(TB_MUSIC_LICENSES);
                    $license_results = $builder->where('user_id', $user->id)
                        ->whereIn('music_id', $music_ids)
                        ->where('expiry_date >', time())
                        ->get()
                        ->getResult();

                    // 将授权结果转换为以music_id为键的数组
                    foreach ($license_results as $license) {
                        $licenses[$license->music_id] = $license;
                    }
                }

                // 查询用户已授权的音乐总数(不限于当前页)
                $db = \Config\Database::connect();
                $licensed_count = $db->table(TB_MUSIC_LICENSES)
                    ->where('user_id', $user->id)
                    ->where('expiry_date >', time())
                    ->countAllResults();

                // 标记每首音乐的授权状态
                foreach ($music_list as &$music) {
                    $music['licensed'] = isset($licenses[$music['id']]);
                    $music['license_data'] = $licenses[$music['id']] ?? null;
                    // 确保expiry_date是时间戳格式
                    if ($music['licensed'] && isset($music['license_data']->expiry_date)) {
                        // 如果是从数据库获取的日期字符串，转换为时间戳
                        if (is_string($music['license_data']->expiry_date)) {
                            $music['license_data']->expiry_date = strtotime($music['license_data']->expiry_date);
                        }
                    }
                }

                $datatable = [
                    "total_items" => $musicTotal,
                    "per_page" => $per_page,
                    "current_page" => $page,
                    "total_pages" => $total_pages,
                ];

                $data['content'] = view('Core\Users\Views\music',[
                    'musics' => $music_list,
                    'user' => $user,
                    'total' => $musicTotal,
                    'datatable' => $datatable,
                    'licensed_count' => $licensed_count
                ]);
                break;

            case 'role':
                if (!find_modules("payment")) {
                    redirect_to( get_module_url() );
                }

                $ids = uri('segment', 4);
                $request = \Config\Services::request();
                $result = db_fetch("*", TB_ROLES, [], "id", "ASC");
                $item = db_get("*", TB_ROLES, "ids = '{$ids}'");
                if(!is_ajax() || uri("segment", 4) == ""){
                    $data['content'] = view('Core\Users\Views\role', [
                        "roles" => $request->roles,
                        "result" => $result,
                        "item" => $item
                    ]);
                }else{
                   $data['content'] = view('Core\Users\Views\update_role', [
                        "roles" => $request->roles,
                        "result" => $result,
                        "item" => $item,
                        'config' => $this->config
                    ]); 
                }
                break;

            case 'report':
                if (!find_modules("payment")) {
                    redirect_to( get_module_url() );
                }
                $data['content'] = view('Core\Users\Views\report', [
                    "result" => $this->model->get_report(),
                    'config' => $this->config
                ]);
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
                        "user" => __("User"),
                        "admin" => __("Admin"),
                        "role" => __("Role"),
                        "plan" => __("Plan"),
                        "expiration_date" => __("Expiration date"),
                        "login_type" => __("Login type"),
                        "status" => __("Status"),
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

                $data['content'] = view('Core\Users\Views\list', $data_content);
                break;
        }

        return view('Core\Users\Views\index', $data);
    }

    public function revoke_license()
    {
        $music_id = post('music_id');
        $user_id = post('user_id');

        // 验证参数
        if(empty($music_id) || empty($user_id)) {
            ms([
                "status" => "error",
                "message" => __("Invalid parameters")
            ]);
        }

        // 删除授权记录
        $db = \Config\Database::connect();
        $deleted = $db->table(TB_MUSIC_LICENSES)
            ->where('music_id', $music_id)
            ->where('user_id', $user_id)
            ->delete();

        if($deleted) {
            ms([
                "status" => "success",
                "message" => __("License revoked successfully"),
                "callback" => "Core.reload()"
            ]);
        } else {
            ms([
                "status" => "error",
                "message" => __("Failed to revoke license")
            ]);
        }
    }

    
    public function ajax_list(){
        $total_items = $this->model->get_list(false);
        $result = $this->model->get_list(true);
        $actions = get_blocks("block_action_user", false);
        $data = [
            "result" => $result,
            "actions" => $actions
        ];
        ms( [
            "total_items" => $total_items,
            "data" => view('Core\Users\Views\ajax_list', $data)
        ] );
    }

    public function export(){
        export_csv(TB_USERS, "users");
    }

    public function view($ids = ""){

        $user = db_get("*", TB_USERS, ["ids" => $ids]);
        if(empty($user)){
            ms([
                "status" => "error",
                "message" => __("This account does not exist")
            ]);
        }

        $team = db_get("*", TB_TEAM, ["owner" => $user->id]);
        if(empty($user)){
            ms([
                "status" => "error",
                "message" => __("This account does not belong to any team")
            ]);
        }

        set_session([
            "tmp_uid" => get_session("uid"),
            "tmp_team_id" => get_session("team_id"),
            "uid" => $user->ids,
            "team_id" => $team->ids,
        ]);

        ms([
            "status" => "success",
            "message" => __("Success")
        ]);
    }
    
    public function save( $ids = '' ){

        $fullname = post('fullname');
        $username = post('username');
        $email = post('email');
        $password = post('password');
        $confirm_password = post('confirm_password');
        $plan_id = (int)post('plan');
        $expiration_date = post('expiration_date');
        $timezone = post('timezone');
        $is_admin = (int)post('is_admin');
        $role = (int)post('role');
        $status = (int)post('status');
        $item = db_get( "*", TB_USERS, ['ids' => $ids] );
        $plan = db_get("*", TB_PLANS, ['id' => $plan_id]);

        if(!$item)
        {
            $email_check = db_get( "*", TB_USERS, ['email' => $email] );
            $username_check = db_get( "*", TB_USERS, ['username' => $username] );
            validate('null', __('Fullname'), $fullname);
            validate('null', __('Email'), $email);
            validate('username', __('Username'), $username);
            validate('min_length', __('Username'), $username, 6);
            validate('not_empty', __('This email already exists'), $email_check);
            validate('not_empty', __('This username already exists'), $username_check);
            validate('null', __('Password'), $password);
            validate('min_length', __('Password'), $password, 6);
            validate('null', __('Confirm password'), $confirm_password);
            validate('other', __('Your password and confirmation password do not match'), $password, $confirm_password);
            validate('empty', __('Please select a plan'), $plan);
            validate('null', __('Expiration date'), $expiration_date);
            validate('null', __('Timezone'), $timezone);

            $avatar = save_img( get_avatar($fullname), WRITEPATH.'avatar/' );

            $id = db_insert(TB_USERS , [
                "ids" => ids(),
                "is_admin" => $is_admin,
                "role" => $role,
                "fullname" => $fullname,
                "username" => $username,
                "email" => $email,
                "password" => md5($password),
                "plan" => $plan_id,
                "expiration_date" => $expiration_date?strtotime(date_sql($expiration_date)):0,
                "timezone" => $timezone,
                "login_type" => 'direct',
                "avatar" => $avatar,
                "status" => $status,
                "changed" => time(),
                "created" => time()
            ]);

            db_insert( TB_TEAM, [
                "ids" => ids(),
                "owner" => $id,
                "pid" => $plan_id,
                "permissions" => $plan->permissions
            ]);
        }
        else
        {
            $email_check = db_get( "*", TB_USERS, ['email' => $email, 'id != ' => $item->id] );
            $username_check = db_get( "*", TB_USERS, ['username' => $username, 'id != ' => $item->id] );
            validate('null', __('Fullname'), $fullname);
            validate('username', __('Username'), $username);
            validate('min_length', __('Username'), $username, 6);
            validate('null', __('Email'), $email);
            validate('email', __('Email'), $email);
            validate('not_empty', __('This email already exists'), $email_check);
            validate('not_empty', __('This username already exists'), $username_check);
            
            if($password != "")
            {
                validate('min_length', __('Password'), $password, 6);
                validate('null', __('Confirm password'), $confirm_password);
                validate('other', __('Your password and confirmation password do not match'), $password, $confirm_password);
            }

            validate('empty', __('Please select a plan'), $plan);
            validate('null', __('Expiration date'), $expiration_date);
            validate('null', __('Timezone'), $timezone);

            $data = [
                "is_admin" => $is_admin,
                "role" => $role,
                "fullname" => $fullname,
                "username" => $username,
                "email" => $email,
                "plan" => $plan_id,
                "expiration_date" => $expiration_date?strtotime(date_sql($expiration_date)):0,
                "timezone" => $timezone,
                "status" => $status,
                "changed" => time()
            ];

            if($password != "")
            {
                $data['password'] = md5($password);
            }

            db_update(TB_USERS , $data, ["ids" => $ids]);

            if( $plan )
            {
                $team = db_get("*", TB_TEAM, ["owner" => $item->id]);
                update_team_data("number_accounts", $plan->number_accounts, $team->id);

                db_update( TB_TEAM, [
                    "permissions" => $plan->permissions,
                    "pid" => $plan->id
                ],
                [
                    "owner" => $item->id
                ]);
            }
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);
    }

    public function auth_music()
    {
        // 获取用户ID和音乐ID数组
        $user_ids = uri('segment', 3);
        $music_ids = post('music_ids');
        $expiry_date = post('expiry_date');

        // 验证用户是否存在
        $user = db_get("*", TB_USERS, ["ids" => $user_ids]);
        if(empty($user)) {
            ms([
                "status" => "error",
                "message" => __("User not found")
            ]);
        }

        // 验证至少选择了一首音乐
        if(empty($music_ids)) {
            ms([
                "status" => "error",
                "message" => __("Please select at least one music")
            ]);
        }

        // 验证过期日期
        if(empty($expiry_date)) {
            ms([
                "status" => "error",
                "message" => __("Please select an expiration date")
            ]);
        }

        // 转换日期为时间戳
        $expiry_timestamp = strtotime($expiry_date);
        $current_timestamp = time();
        if($expiry_timestamp < $current_timestamp) {
            ms([
                "status" => "error",
                "message" => __("Expiration date cannot be in the past")
            ]);
        }

        // 检查音乐是否存在并处理授权
        $success_count = 0;
        $failed_count = 0;

        foreach($music_ids as $music_id) {
            // 检查音乐是否存在
            $music = db_get("*", TB_MUSIC, ["id" => $music_id]);
            if(empty($music)) {
                $failed_count++;
                continue;
            }

            // 检查是否已经授权过
            $existing_license = db_get("*", TB_MUSIC_LICENSES, [
                "user_id" => $user->ids,
                "music_id" => $music_id
            ]);

            if($existing_license) {
                // 更新现有授权
                $result = db_update(TB_MUSIC_LICENSES, [
                    "expiry_date" => $expiry_timestamp,
                    "updated_at" => time()
                ], [
                    "id" => $existing_license->id
                ]);
            } else {
                // 创建新授权
                $result = db_insert(TB_MUSIC_LICENSES, [
                    "user_id" => $user->id,
                    "music_id" => $music_id,
                    "start_date" => date('Y-m-d H:i:s', time()),  // 转换为MySQL格式
                    "expiry_date" => date('Y-m-d H:i:s', $expiry_timestamp),
                    'allow_download' => 1,
                    'allow_streaming' => 1,
                    'allow_commercial_use' => 1,
                    "created_at" => date('Y-m-d H:i:s', time()),
                    "updated_at" => date('Y-m-d H:i:s', time())
                ]);
            }

            if($result) {
                $success_count++;
            } else {
                $failed_count++;
            }
        }

        // 返回结果
        if($success_count > 0) {
            $message = sprintf(__("Successfully licensed %d music tracks"), $success_count);
            if($failed_count > 0) {
                $message .= sprintf(__(", failed to license %d tracks"), $failed_count);
            }

            ms([
                "status" => "success",
                "message" => $message,
                "callback" => "Core.click('users-list');"
            ]);
        } else {
            ms([
                "status" => "error",
                "message" => __("Failed to license any music tracks")
            ]);
        }
    }

    public function delete( $ids = '' ){

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
                db_delete(TB_USERS, ['ids' => $id]);
            }
        }
        elseif( is_string($ids) )
        {
            db_delete(TB_USERS, ['ids' => $ids]);
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);

    }

    /*
    * ROLES
    */

    public function role_save($ids = "")
    {
        if (!find_modules("payment")) {
            redirect_to( get_module_url() );
        }

        $name = post('name');
        $permissions = post('permissions');
        $permissions['profile_status'] = 1;

        validate('null', __('Name'), $name);

        $item = db_get("*", TB_ROLES, "ids = '{$ids}'");
        if(!$item){

            db_insert(TB_ROLES, [
                "ids" => ids(),
                "name" => $name,
                "permissions" => json_encode( $permissions )
            ]);

        }else{

            db_update(
                TB_ROLES, 
                [
                    "name" => $name,
                    "permissions" => json_encode( $permissions ),
                ], 
                [ "ids" => $ids ]
            );
            
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);

    }

}