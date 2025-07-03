<?php
namespace Core\Instagram_profiles\Controllers;

class Instagram_profiles extends \CodeIgniter\Controller
{
    public $ig;
    public $username;
    public $password;
    public $proxy;
    public $security_code;
    public $verification_code;
    public $choice;

    public function __construct(){
        $reflect = new \ReflectionClass(get_called_class());
        $this->module = strtolower( $reflect->getShortName() );
        $this->config = include realpath( __DIR__."/../Config.php" );
        include get_module_dir( __DIR__ , 'Libraries/Instagram_unofficial.php');
        $this->app_id = get_option('instagram_client_id', '');
        $this->app_secret = get_option('instagram_client_secret', '');
        $this->app_version = get_option('instagram_app_version', 'v18.0');

        if(get_option('instagram_official_status', 0) && ($this->app_id == "" || $this->app_secret == "")){
            redirect_to( base_url("social_network_settings/index/".$this->config['parent']['id']) ); 
        }

        if( get_option('instagram_official_status', 0) && $this->app_id && $this->app_secret && $this->app_version){
            $fb = new \JanuSoftware\Facebook\Facebook([
                'app_id' => $this->app_id,
                'app_secret' => $this->app_secret,
                'default_graph_version' => $this->app_version,
            ]);

            $this->fb = $fb;
        }

        $this->proxy_item = FALSE;
        $this->proxy = NULL;
        $proxy_item = asign_proxy("instagram", "profile", 1);
        if ($proxy_item) {
            $this->proxy_item = $proxy_item;
            $this->proxy = $proxy_item->proxy;
        }
    }
    
    public function index($page = "") {

        switch ($page) {
            case 'unofficial':
                $team_id = get_team("id");
                $ig_username = get_session("ig_username");
                $ig_password = get_session("ig_password");
                $ig_userinfo = get_session("ig_userinfo");

                if(!$ig_username || !$ig_password || !$ig_userinfo){
                    redirect_to( get_module_url("oauth?type=unofficial&error=1") );
                }

                $ig_auth = new \Instagram_unofficial($ig_username, $ig_password, $team_id, $this->proxy);

                if(empty($ig_userinfo)){
                    redirect_to( get_module_url("oauth?type=unofficial&error=2") );
                }

                $response = json_decode($ig_userinfo, true);

                $avatar = save_img( $response['profile_pic_url'], WRITEPATH.'avatar/' );

                $result_data = [];
                $result_data[] = (object)[
                    'id' => $response['pk'],
                    'name' => $response['full_name'],
                    'avatar' => get_file_url($avatar),
                    'desc' => $response['username']
                ];

                $profiles = [
                    "status" => "success",
                    "config" => $this->config,
                    "result" => $result_data,
                    "save_url" => get_module_url("save_unofficial")
                ];
                
                break;
            
            default:
                try {
                    if(!get_session("IG_AccessToken")){
                        if(!get('code')){
                            redirect_to( get_module_url("oauth") );
                        }

                        $callback_url = get_module_url();
                        $helper = $this->fb->getRedirectLoginHelper();
                        if ( get("state") ) {
                            $helper->getPersistentDataHandler()->set('state', get("state"));
                        }
                        $accessToken = $helper->getAccessToken($callback_url);
                        $accessToken = $accessToken->getValue();
                        set_session( ['IG_AccessToken' => $accessToken] );
                        redirect_to( $callback_url );
                    }else{
                        $accessToken = get_session("IG_AccessToken"); 
                    }

                    $response = $this->fb->get('/me/accounts?fields=instagram_business_account,id,name,username,fan_count,link,is_verified,picture,access_token,category&limit=10000', $accessToken)->getDecodedBody();
                    if(is_string($response)){
                        $response = $this->fb->get('/me/accounts?fields=instagram_business_account,id,name,username,fan_count,link,is_verified,picture,access_token,category&limit=3', $accessToken)->getDecodedBody();
                    }

                    $page_ids = [];
                    if(isset($response['data']) && !empty($response['data'])){
                        foreach ($response['data'] as $value) {
                            if(isset($value['instagram_business_account'])){
                                $page_ids[] = $value['instagram_business_account']['id'];
                            }
                        }
                    }

                    if(empty($page_ids)){
                        $profiles = [
                            "status" => "error",
                            "config" => $this->config,
                            "message" => __('No profile to add')
                        ];
                    }

                    $result_data = [];
                    if(!empty($page_ids)){
                        foreach ($page_ids as $key => $page_id) {
                            $profile = $this->fb->get('/'.$page_id.'?fields=id,name,username,profile_picture_url,ig_id', $accessToken)->getDecodedBody();
                            $result_data[] = (object)[
                                'id' => $profile['id'],
                                'name' => $profile['username'],
                                'avatar' => $profile['profile_picture_url'],
                                'desc' => isset($profile['name'])?$profile['name']:$profile['username']
                            ];
                        }
                    }

                    $profiles = [
                        "status" => "success",
                        "config" => $this->config,
                        "result" => $result_data
                    ];
                } catch (\Exception $e) {
                    $profiles = [
                        "status" => "error",
                        "config" => $this->config,
                        "message" => $e->getMessage()
                    ];
                }
                break;
        }

        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "content" => view('Core\Instagram_profiles\Views\add', $profiles)
        ];

        return view('Core\Instagram_profiles\Views\index', $data);
    }

    public function oauth(){
        $oauth_link = get_module_url("oauth_official");
        if( !get_option("instagram_unofficial_status", 1) && get_option("instagram_official_status", 0) ){
            redirect_to($oauth_link);
        }

        if( !get_option("instagram_unofficial_status", 1) && !get_option("instagram_official_status", 0) ){
            redirect_to( base_url("account_manager") );
        }

        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "content" => view('Core\Instagram_profiles\Views\oauth', [ "config" => $this->config, "oauth_link" => $oauth_link ])
        ];

        return view('Core\Instagram_profiles\Views\index', $data);
    }

    public function oauth_official(){
        remove_session(['IG_AccessToken']);
        if( !get_option('instagram_official_status', 0) ){
            redirect_to( base_url() );
        }

        if($this->app_id == "" || $this->app_secret == "" || $this->app_version == ""){
            redirect_to( base_url("social_network_settings/index/".$this->config['parent']['id']) ); 
        }

        $helper = $this->fb->getRedirectLoginHelper();
        $permissions = [ get_option('instagram_permissions', 'instagram_basic,instagram_content_publish,pages_read_engagement,business_management') ];
        $login_url = $helper->getLoginUrl( get_module_url() , $permissions);
        redirect_to($login_url);
    }

    public function oauth_unofficial(){
        $team_id = get_team("id");
        $ig_username = post("ig_username");
        $ig_password = post("ig_password");
        
        validate('null', __('Instagram username'), $ig_username);
        validate('null', __('Instagram password'), $ig_password);

        set_session([
            "ig_username" => $ig_username,
            "ig_password" => $ig_password
        ]);

        $ig_auth = new \Instagram_unofficial($ig_username, $ig_password, $team_id, $this->proxy);
        $login_data = $ig_auth->login();

        if(isset($login_data['message']) && $login_data['message'] == "two_factor_required"){
            ms([
                "status" => "error",
                "type" => "two_factor_auth",
                "method" => $login_data['two_factor_info']['verification_method'],
                "identifier" => $login_data['two_factor_info']['two_factor_identifier'],
                "message" => __("Instagram sent a security code to you. Please check your email or phone.")
            ]);
        }

        if(isset($login_data['message']) && $login_data['message'] == "challenge_required"){
            ms([
                "status" => "error",
                "type" => "challenge",
                "api_path" => $login_data['challenge']['api_path'],
                "message" => __("Instagram sent a security code to you. Please check your email or phone.")
            ]);
        }

        if($login_data['status'] == "fail"){
            ms([
                "status" => "error",
                "message" => $login_data['message']
            ]);
        }

        if($login_data['status'] == "ok" && isset($login_data['logged_in_user'])){
            set_session(["ig_userinfo" => json_encode($login_data['logged_in_user'])]);
        }
        
        ms([
            "status" => "success"
        ]);
    }

    public function confirm_security_code(){
        $team_id = get_team("id");
        $ig_api_path = post("ig_api_path");
        $ig_type = post("ig_type");
        $ig_method = post("ig_method");
        $ig_identifier = post("ig_identifier");
        $ig_security_code = post("ig_security_code");

        if($ig_type == "two_factor_auth"){
            $ig_username = get_session("ig_username");
            $ig_password = get_session("ig_password");

            $ig_auth = new \Instagram_unofficial($ig_username, $ig_password, $team_id, $this->proxy);
            $response = $ig_auth->finishTwoFactorLogin($ig_identifier, $ig_security_code, $ig_method);

            if($response['status'] == "fail"){
                ms([
                    "status" => "error",
                    "message" => $response['message']
                ]);
            }

            if($response['status'] == "ok" && isset($response['logged_in_user'])){
                set_session(["ig_userinfo" => json_encode($response['logged_in_user'])]);
            }
        }else{
            $verification_method = 1;

            $ig_username = get_session("ig_username");
            $ig_password = get_session("ig_password");

            $ig_auth = new \Instagram_unofficial($ig_username, $ig_password, $team_id, $this->proxy);
            $response = $ig_auth->finishChallenge($ig_api_path, $ig_security_code);
            
            if($response['status'] == "fail"){
                ms([
                    "status" => "error",
                    "message" => $response['message']
                ]);
            }

            if($response['status'] == "ok" && isset($response['logged_in_user'])){
                set_session(["ig_userinfo" => json_encode($response['logged_in_user'])]);
            }
        }

        sleep(2);

        if($response["status"] == "ok"){
            ms(["status" => "success"]);
        }
    }

    public function save()
    {
        try {
            $ids = post('id');
            $team_id = get_team("id");
            $accessToken = get_session('IG_AccessToken');

            validate('empty', __('Please select a profile to add'), $ids);

            $response = $this->fb->get('/me/accounts?fields=instagram_business_account,id,name,username,fan_count,link,is_verified,picture,access_token,category&limit=10000', $accessToken)->getDecodedBody();
            if(is_string($response)){
                $response = $this->fb->get('/me/accounts?fields=instagram_business_account,id,name,username,fan_count,link,is_verified,picture,access_token,category&limit=3', $accessToken)->getDecodedBody();
            }

            $page_ids = [];
            if(isset($response['data']) && !empty($response['data'])){
                foreach ($response['data'] as $value) {
                    if(isset($value['instagram_business_account'])){
                        $page_ids[] = $value['instagram_business_account']['id'];
                    }
                }
            }

            if(empty($page_ids)){
                ms([
                    "status" => "error",
                    "message" => __('No profile to add')
                ]);
            }

            if(!is_string($page_ids)){

                foreach ($page_ids as $page_id) {

                    $profile = $this->fb->get('/'.$page_id.'?fields=id,name,username,profile_picture_url,ig_id', $accessToken)->getDecodedBody();

                    if(in_array($profile['id'], $ids, true)){

                        $item = db_get('*', TB_ACCOUNTS, "social_network = 'instagram' AND login_type = 1 AND team_id = '{$team_id}' AND pid = '".$profile['id']."'");
                        if(!$item){
                            //Check limit number 
                            check_number_account("instagram", "profile");
                            $avatar = save_img( $profile['profile_picture_url'], WRITEPATH.'avatar/' );
                            $data = [
                                'ids' => ids(),
                                'module' => $this->module,
                                'social_network' => 'instagram',
                                'category' => 'profile',
                                'login_type' => 1,
                                'can_post' => 1,
                                'team_id' => $team_id,
                                'pid' => $profile['id'],
                                'name' => isset($profile['name'])?$profile['name']:$profile['username'],
                                'username' => $profile['username'],
                                'token' => $accessToken,
                                'avatar' => $avatar,
                                'url' => "https://www.instagram.com/".$profile['username'],
                                'data' => NULL,
                                'status' => 1,
                                'changed' => time(),
                                'created' => time()
                            ];

                            db_insert(TB_ACCOUNTS, $data);
                        }else{
                            @unlink( get_file_path($item->avatar) );
                            $avatar = save_img( $profile['profile_picture_url'], WRITEPATH.'avatar/' );
                            $data = [
                                'can_post' => 1,
                                'pid' => $profile['id'],
                                'name' => isset($profile['name'])?$profile['name']:$profile['username'],
                                'username' => $profile['username'],
                                'token' => $accessToken,
                                'avatar' => $avatar,
                                'url' => "https://www.instagram.com/".$profile['username'],
                                'status' => 1,
                                'changed' => time(),
                            ];

                            db_update(TB_ACCOUNTS, $data, ['id' => $item->id]);
                        }
                    }
                }

                ms([
                    "status" => "success",
                    "message" => __("Success")
                ]);
                
            }else{
                ms([
                    "status" => "error",
                    "message" => $response
                ]);
            }
        } catch (\Exception $e) {
            ms([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function save_unofficial()
    {
        try {
            $ids = post('id');
            $team_id = get_team("id");
            $ig_username = get_session("ig_username");
            $ig_password = get_session("ig_password");
            $ig_userinfo = get_session("ig_userinfo");

            $ig_auth = new \Instagram_unofficial($ig_username, $ig_password, $team_id, $this->proxy);
            $response = json_decode($ig_userinfo, true);

            if(!$ig_username || !$ig_password || !$ig_userinfo){
                validate('empty', __('Cannot connect to your Instagram account'), $ids);
            }

            if(empty($ig_userinfo)){
                validate('empty', __('Cannot connect to your Instagram account'), $ids);
            }

            validate('empty', __('Please select a profile to add'), $ids);
            
            if(in_array($response['pk'], $ids, FALSE)){
                $accessToken = json_encode([ "ig_username" => $ig_username, "ig_password" => encrypt_encode($ig_password) ]);
                $item = db_get('*', TB_ACCOUNTS, "social_network = 'instagram' AND login_type = 2 AND team_id = '{$team_id}' AND pid = '".$response['pk']."'");
                if(!$item){
                    //Check limit number 
                    check_number_account("instagram", "profile");
                    $avatar = save_img( $response['profile_pic_url'], WRITEPATH.'avatar/' );
                    $data = [
                        'ids' => ids(),
                        'module' => $this->module,
                        'social_network' => 'instagram',
                        'category' => 'profile',
                        'login_type' => 2,
                        'can_post' => 1,
                        'team_id' => $team_id,
                        'pid' => $response['pk'],
                        'name' => $response['full_name'],
                        'username' => $response['username'],
                        'token' => $accessToken,
                        'avatar' => $avatar,
                        'url' => 'https://www.instagram.com/'.$response['username'],
                        'proxy' => $this->proxy_item?$this->proxy_item->id:"",
                        'data' => NULL,
                        'status' => 1,
                        'changed' => time(),
                        'created' => time()
                    ];

                    db_insert(TB_ACCOUNTS, $data);
                }else{
                    @unlink( get_file_path($item->avatar) );
                    $avatar = save_img( $response['profile_pic_url'], WRITEPATH.'avatar/' );
                    $data = [
                        'can_post' => 1,
                        'pid' => $response['pk'],
                        'name' => $response['full_name'],
                        'username' => $response['username'],
                        'token' => $accessToken,
                        'avatar' => $avatar,
                        'url' => 'https://www.instagram.com/'.$response['username'],
                        'proxy' => $this->proxy_item?$this->proxy_item->id:"",
                        'status' => 1,
                        'changed' => time(),
                    ];

                    db_update(TB_ACCOUNTS, $data, ['id' => $item->id]);
                }
            }

            remove_session(["ig_password", "ig_username"]);

            ms([
                "status" => "success",
                "message" => __("Success")
            ]);
        } catch (\Exception $e) {
            ms([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
}