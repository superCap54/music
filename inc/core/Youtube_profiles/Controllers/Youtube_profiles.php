<?php
namespace Core\Youtube_profiles\Controllers;
//現在是本地調用谷歌 所以開了代理，上到測試生產環境要把代理刪掉
class Youtube_profiles extends \CodeIgniter\Controller
{
    public function __construct(){
        $reflect = new \ReflectionClass(get_called_class());
        $this->module = strtolower( $reflect->getShortName() );
        $this->config = include realpath( __DIR__."/../Config.php" );

        $client_id = get_option('youtube_client_id', '');
        $client_secret = get_option('youtube_api_secret', '');
        $api_key = get_option('youtube_api_key', '');

        if($client_id == "" || $client_secret == "" || $api_key == ""){
            redirect_to( base_url("social_network_settings/index/".$this->config['parent']['id']) ); 
        }

        $this->client = new \Google\Client();
        // ==================== 代理设置开始 ====================
        $proxy = '67.227.42.189:6166';
        $proxyAuth = 'iggndszq:iguhz2og7m4t';

        try {
            $httpClient = new \GuzzleHttp\Client([
                'proxy' => [
                    'http'  => "http://{$proxyAuth}@{$proxy}",
                    'https' => "http://{$proxyAuth}@{$proxy}",
                    'no' => [] // 可选：设置不通过代理的域名/IP
                ],
                'verify' => false, // 跳过SSL验证（开发环境用）
                'timeout' => 30,
            ]);
            $this->client->setHttpClient($httpClient);
        } catch (\Exception $e) {
            log_message('error', 'Proxy setup failed: ' . $e->getMessage());
            // 继续执行但无代理
        }
        // ==================== 代理设置结束 ====================

        $this->client->setAccessType("offline");
        $this->client->setApprovalPrompt("force");
        $this->client->setApplicationName("Youtube");
        $this->client->setClientId( $client_id );
        $this->client->setClientSecret( $client_secret );
        $this->client->setRedirectUri(get_module_url());
        $this->client->setDeveloperKey( $api_key );
        $this->client->setScopes(
            [
                'https://www.googleapis.com/auth/youtube', 
                'https://www.googleapis.com/auth/userinfo.email'
            ]
        );

        $this->youtube = new \Google\Service\YouTube($this->client);
    }
    
    public function index() {

        try {
            if( !get_session("YT_AccessToken") ){
                $this->client->authenticate( post("code") );
                $oauth2 = new \Google\Service\Oauth2($this->client);
                $accessToken = $this->client->getAccessToken();
                set_session(["YT_AccessToken" => json_encode($accessToken)]);
            }else{
                $accessToken = json_decode( get_session("YT_AccessToken") , true);
            }

            $this->client->setAccessToken($accessToken);

            $part = 'brandingSettings,status,id,snippet,contentDetails,contentOwnerDetails,statistics';
            $optionalParams = array(
                'mine' => true
            );

            $response = $this->youtube->channels->listChannels($part, $optionalParams);

            if(!is_string($response)){
                if(!empty($response)){

                    if(!empty($response->items))
                    {
                        foreach ($response->items as $key => $row)
                        {
                            $result[] = (object)[
                                'id' => $row->getId(),
                                'name' => $row->getSnippet()->getLocalized()->getTitle(),
                                'avatar' => $row->getSnippet()->getThumbnails()->getDefault()->getUrl(),
                                'desc' => $row->getSnippet()->getLocalized()->getDescription()
                            ];
                        }

                        $profiles = [
                            "status" => "success",
                            "config" => $this->config,
                            "result" => $result
                        ];
                    }else{
                        $profiles = [
                            "status" => "error",
                            "config" => $this->config,
                            "message" => __('No profile to add')
                        ];
                    }
                }
            }else{
                $profiles = [
                    "status" => "error",
                    "config" => $this->config,
                    "message" => $response
                ];
            }
        } catch (\Exception $e) {

            $message = json_decode($e->getMessage());

            if($message){
                $message = $message->error->message;
            }else{
                $message = $e->getMessage();
            }

            $profiles = [
                "status" => "error",
                "config" => $this->config,
                "message" => $message
            ];
        }

        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "content" => view('Core\Youtube_profiles\Views\add', $profiles)
        ];

        return view('Core\Youtube_profiles\Views\index', $data);
    }

    public function oauth(){
        remove_session(["YT_AccessToken"]);
        $oauth_link = $this->client->createAuthUrl();
        redirect_to($oauth_link);
    }

    public function save()
    {
        $ids = post('id');
        $team_id = get_team("id");
        $accessToken = json_decode( get_session("YT_AccessToken") , true);
        validate('empty', __('Please select a profile to add'), $ids);

        $this->client->setAccessToken($accessToken);

        $part = 'brandingSettings,status,id,snippet,contentDetails,contentOwnerDetails,statistics';
        $optionalParams = array(
            'mine' => true
        );
        
        $response = $this->youtube->channels->listChannels($part, $optionalParams);
        if(!is_string($response)){
            if(!empty($response->items))
            {
                foreach ($response->items as $key => $row)
                {
                    if(in_array($row->getId(), $ids, true)){
                        $item = db_get('*', TB_ACCOUNTS, "social_network = 'youtube' AND team_id = '{$team_id}' AND pid = '".$row->getId()."'");
                        if(!$item){
                            //Check limit number
                            check_number_account("youtube", "channel");
                            //不save頭像了
//                            $avatar = save_img( $row->getSnippet()->getThumbnails()->getDefault()->getUrl(), WRITEPATH.'avatar/' );
                            $data = [
                                'ids' => ids(),
                                'module' => $this->module,
                                'social_network' => 'youtube',
                                'category' => 'channel',
                                'login_type' => 1,
                                'can_post' => 1,
                                'team_id' => $team_id,
                                'pid' => $row->getId(),
                                'name' => $row->getSnippet()->getLocalized()->getTitle(),
                                'username' => $row->getSnippet()->getLocalized()->getTitle(),
                                'token' => json_encode( $accessToken ),
                                'avatar' => '',
                                'url' => 'https://www.youtube.com/channel/'.$row->getId(),
                                'data' => NULL,
                                'status' => 1,
                                'changed' => time(),
                                'created' => time()
                            ];

                            db_insert(TB_ACCOUNTS, $data);
                        }else{
                            unlink( get_file_path($item->avatar) );
                            $avatar = save_img( $row->getSnippet()->getThumbnails()->getDefault()->getUrl(), WRITEPATH.'avatar/' );
                            var_dump($avatar,222222);exit;
                            $data = [
                                'can_post' => 1,
                                'pid' => $row->getId(),
                                'name' => $row->getSnippet()->getLocalized()->getTitle(),
                                'username' => $row->getSnippet()->getLocalized()->getTitle(),
                                'token' => json_encode( $accessToken ),
                                'avatar' => $avatar,
                                'url' => 'https://www.youtube.com/channel/'.$row->getId(),
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
                    "message" => __('No profile to add')
                ]);
            }
   
        }else{
            ms([
                "status" => "error",
                "message" => $response
            ]);
        }
    }
}