<?php
namespace Core\Facebook_post\Models;
use CodeIgniter\Model;
use GuzzleHttp\Client;

class Facebook_postModel extends Model
{
    public function __construct(){
        $this->config = include realpath( __DIR__."/../Config.php" );
        include get_module_dir( __DIR__ , '../Facebook_profiles/Libraries/FacebookCookieApi.php');
        $app_id = get_option('facebook_client_id', '');
        $app_secret = get_option('facebook_client_secret', '');
        $app_version = get_option('facebook_app_version', 'v18.0');

        try {
            $fb = new \JanuSoftware\Facebook\Facebook([
                'app_id' => $app_id,
                'app_secret' => $app_secret,
                'default_graph_version' => $app_version,
            ]);

            $this->fb = $fb;
        } catch (\Exception $e) {
            
        }
    }

    public function block_can_post(){
        return true;
    }

    public function block_plans(){
        return [
            "tab" => 10,
            "position" => 100,
            "permission" => true,
            "label" => __("Planning and Scheduling"),
            "items" => [
                [
                    "id" => $this->config['id'],
                    "name" => sprintf(__("%s scheduling & report"), $this->config['name']),
                ]
            ]
        ];
    }

    public function block_frame_posts($path = ""){
        return [
            "position" => 100,
            "preview" => view( 'Core\Facebook_post\Views\preview', [ 'config' => $this->config ] ),
            "advance_options" => view( 'Core\Facebook_post\Views\advance_options', [ 'config' => $this->config ] )
        ];
    }

    public function block_profile_settings(){
        if (permission("twitter_post")) {
            return array(
                "position" => 1000,
                "content" => view( 'Core\Facebook_post\Views\settings', [ 'config' => $this->config ] )
            );
        }
    }

    public function post_validator($post){
        $errors = array();
        $data = json_decode( $post->data , 1);
        $medias = $data['medias'];


        if($post->social_network == 'facebook'){

            if($post->api_type == 1){
                if( isset( $data['advance_options'] ) && isset( $data['advance_options']['fb_post_type'] ) ){
                    switch ($data['advance_options']['fb_post_type']) {
                        case 'story':
                            $errors[] = __("Currently, Facebook API Official not support post to story");
                            break;

                         case 'reels':
                            if($post->category != "page"){
                                if( is_image($medias[0]) ){
                                    $errors[] = __("Facebook Reels only supports posting videos to Facebook pages of minimum 3 seconds and maximum 90 seconds");
                                }
                            }
                            break;
                    }
                }
            }

            if($post->api_type == 3){
                if($post->type == "media" || $post->type == "link"){
                    if( !is_image($medias[0]) ){
                        $errors[] = __("Currently, Facebook cookie method does not allow sharing videos");
                    }
                }

                if( isset( $data['advance_options'] ) && isset( $data['advance_options']['fb_post_type'] ) ){
                    switch ($data['advance_options']['fb_post_type']) {
                        case 'story':
                            if($post->category == "profile" || $post->category == "page"){
                                if($post->type != "media" && $post->type != "link"){
                                    $errors[] = __("Cannot post to story with text only");
                                }else{
                                    if( !is_image($medias[0]) ){
                                        $errors[] = __("Currently, Facebook cookie method just allow sharing images to story of Facebook pages and Facebook profiles");
                                    }
                                }
                            }else{
                                $errors[] = __("Currently, Facebook cookie method just allow sharing images to story of Facebook pages and Facebook profiles");
                            }
                            break;

                        case 'reels':
                            if($post->category != "page"){
                                if( is_image($medias[0]) ){
                                    $errors[] = __("Currently, Facebook cookie method does not allow sharing to reels");
                                }
                            }
                            break;
                    }
                }
            }
        }

        return $errors;
    }

    public function post_handler($post){
        $data = json_decode($post->data, false);
        $medias = $data->medias;
        $endpoint = "/".$post->account->pid."/";
        $shortlink_by = shortlink_by($data);

        try
        {
            $caption = shortlink( spintax($data->caption), $shortlink_by );
            $link = shortlink( $data->link, $shortlink_by );
            switch ($post->account->login_type) {
                case 1:

                    $post_type = "default";
                    if( isset( $data->advance_options ) && isset( $data->advance_options->fb_post_type ) ){
                        $post_type = $data->advance_options->fb_post_type;
                    }

                    switch ($post_type) {
                        case 'reels':
                            
                            switch ($post->type)
                            {
                                case 'media':

                                    if(is_image($medias[0]))
                                    {
                                        return [
                                            "status" => "error",
                                            "message" => __( "Facebook reels just support post with videos to Facebook pages" ),
                                            "type" => $post->type
                                        ];
                                    }

                                    if(is_video($medias[0])){
                                        $upload_session_params = [
                                            "upload_phase" => "start",
                                            "access_token" => $post->account->token
                                        ];

                                        $create_upload_session = $this->fb->post( $endpoint.'video_reels', $upload_session_params, $post->account->token)->getDecodedBody();

                                        if( !isset($create_upload_session['video_id']) ){
                                            return [
                                                "status" => "error",
                                                "message" => __( "Cannot create an upload session for uploading reels video to the Facebook page" ),
                                                "type" => $post->type
                                            ];
                                        }

                                        $upload = post_curl($create_upload_session['upload_url'], [], 
                                            [ 
                                                "Authorization: OAuth ".$post->account->token, 
                                                "file_url: ".get_file_url($medias[0])
                                            ]
                                        );

                                        if(isset($upload['success']) && $upload['success'] == 1){
                                            $attempts = 0;
                                            $check_after_secs = 3;
                                            $accessToken = $post->account->token;
                                            do {
                                                try {
                                                    $attempts++;
                                                    sleep($check_after_secs);
                                                    $params = [
                                                        "video_id" => $create_upload_session["video_id"],
                                                        "upload_phase" => "finish",
                                                        "video_state" => "PUBLISHED",
                                                        "description" => $caption,
                                                        "access_token" => $accessToken
                                                    ];

                                                    $response = $this->fb->post( $endpoint.'video_reels', $params, $post->account->token)->getDecodedBody();
                                                    
                                                    $post_id = $create_upload_session["video_id"]; 
                                                    return [
                                                        "status" => "success",
                                                        "message" => __('Success'),
                                                        "id" => $post_id,
                                                        "url" => "https://www.facebook.com/reel/",
                                                        "type" => $post->type
                                                    ];
                                                } catch (\Exception $e) {
                                                    return [
                                                        "status" => "error",
                                                        "message" => $e->getMessage(),
                                                        "type" => $post->type
                                                    ];
                                                }
                                            } while($attempts <= 15);
                                        }
                                    }
                                  
                                    break;

                                case 'link':
                                    return [
                                        "status" => "error",
                                        "message" => __( "Facebook reels not support post with link" ),
                                        "type" => $post->type
                                    ];
                                    break;

                                case 'text':
                                    return [
                                        "status" => "error",
                                        "message" => __( "Facebook reels not support post with text only" ),
                                        "type" => $post->type
                                    ];
                                    break;

                            }
                        
                            

                            break;
                        
                        default:
                            
                            switch ($post->type)
                            {
                                case 'media':

                                    if(count($medias) == 1)
                                    {
                                        if(is_image($medias[0]))
                                        {
                                            $medias[0] = watermark($medias[0], $post->team_id, $post->account_id);
                                            $endpoint .= "photos";
                                            $params = [
                                                'message' => $caption,
                                                'url' => get_file_url($medias[0])
                                            ];
                                        }

                                        if(is_video($medias[0])){
                                            $endpoint .= "videos";
                                            $params = [
                                                'description' => $caption,
                                                'file_url' => get_file_url($medias[0])
                                            ];
                                        }
                                    }
                                    else
                                    {

                                        $media_ids = [];
                                        $success_count = 0;
                                        foreach ($medias as $key => $media)
                                        {   
                                            if(is_image($media))
                                            {
                                                $media = watermark($media, $post->team_id, $post->account->id);
                                                $medias[$key] = get_file_url($media);
                                                $upload_params = [
                                                    'url' => get_file_url($media),
                                                    'published' => false
                                                ];

                                                $upload = $this->fb->post( $endpoint.'photos', $upload_params, $post->account->token)->getDecodedBody();
                                                $media_ids['attached_media['.$success_count.']'] = '{"media_fbid":"'.$upload['id'].'"}';
                                                $success_count++;
                                            }
                                            else
                                            {   
                                                //Pages not support post multi media with videos.
                                                if($post->account->category != "page"){

                                                    $upload_params = [
                                                        'file_url' => get_file_url($media),
                                                        'published' => false
                                                    ];

                                                    $upload = $this->fb->post( $endpoint.'videos', $upload_params, $post->account->token)->getDecodedBody();
                                                    $media_ids['attached_media['.$success_count.']'] = '{"media_fbid":"'.$upload['id'].'"}';
                                                    $success_count++;
                                                }
                                            }
                                        } 

                                        $endpoint .= "feed";
                                        $params = ['message' => $caption];

                                        $params += $media_ids;
                                    }

                                    break;

                                case 'link':
                                    $endpoint .= "feed";
                                    $params = [
                                        'message' => $caption,
                                        'link' => $data->link
                                    ];
                                    break;

                                case 'text':
                                    $endpoint .= "feed";
                                    $params = ['message' => $caption];
                                    break;

                            }
                        
                            $response = $this->fb->post($endpoint, $params, $post->account->token)->getDecodedBody();
                            $post_id =  $response['id'];
                            unlink_watermark($medias);
                            return [
                                "status" => "success",
                                "message" => __('Success'),
                                "id" => $post_id,
                                "url" => "https://fb.com/".$post_id,
                                "type" => $post->type
                            ];

                            break;
                    }

                    break;

                    
                case 3: 
                    if( $post->account->token == "" ){
                        db_update(TB_ACCOUNTS, [ "status" => 0 ], [ "id" => $post->account->id ] );
                        return [
                            "status" => "error",
                            "message" => __( "You have not authorized your Facebook account yet. Please re-login and try again" ),
                            "type" => $post->type
                        ];
                    }

                    $accessToken = json_decode($post->account->token);

                    if( !is_array($accessToken) && (!isset($accessToken->fb_user_id) || !isset($accessToken->fb_session)) ){
                        db_update(TB_ACCOUNTS, [ "status" => 0 ], [ "id" => $post->account->id ] );
                        return [
                            "status" => "error",
                            "message" => __( "You have not authorized your Facebook account yet. Please re-login and try again" ),
                            "type" => $post->type
                        ];
                    }

                    $proxy = get_proxy($post->account->proxy);

                    if($post->category == "profile"){
                        $fb_auth = new \FacebookCookieApi($accessToken->fb_user_id, $accessToken->fb_session, $proxy);
                    }else{
                        $fb_auth = new \FacebookCookieApi($accessToken->fb_user_id, $accessToken->fb_session, $proxy, $post->account->pid);
                    }
                    
                    if($post->type == "media" && !empty($medias)){
                        if(empty($medias)){
                            return [
                                "status" => "error",
                                "message" => __("Missing media file for facebook post stories"),
                                "type" => $post->type
                            ];
                        }

                        foreach ($medias as $key => $media) {
                            if(!is_image($media)){
                                return [
                                    "status" => "error",
                                    "message" => __("Currently, Facebook cookie method not allow sharing videos"),
                                    "type" => $post->type
                                ];
                            }

                            $medias[$key] = get_file_url($media);
                        }
                    }

                    if(
                        isset( $data->advance_options ) &&
                        isset( $data->advance_options->fb_post_type ) &&
                        $data->advance_options->fb_post_type == "story"
                    ){

                        
                        if($post->type == "text"){
                            return [
                                "status" => "error",
                                "message" => __("Cannot post to story with text only"),
                                "type" => $post->type
                            ];
                        }

                        if(empty($medias)){
                            return [
                                "status" => "error",
                                "message" => __("Missing media file for facebook post stories"),
                                "type" => $post->type
                            ];
                        }

                        $story_link = "";
                        if(
                            isset( $data->advance_options ) &&
                            isset( $data->advance_options->fb_story_link ) &&
                            $data->advance_options->fb_story_link != ""
                        ){
                            $story_link = $data->advance_options->fb_story_link;
                        }

                        $response = $fb_auth->sendStory($post->account->pid, $caption, $medias[0], $post->category, $story_link, $post->team_id);
                    }else{
                        $response = $fb_auth->sendPost($post->account->pid, $post->category, $post->type, $caption, $link, $medias, $post->account->tmp);
                    }

                    if($response['status'] == "error"){
                        return [
                            "status" => "error",
                            "message" => __( $response['message'] ),
                            "type" => $post->type
                        ];
                    }

                    $post_id =  $response['id'];
                    unlink_watermark($medias);
                    return [
                        "status" => "success",
                        "message" => __('Success'),
                        "id" => $post_id,
                        "url" => "https://fb.com/".$post_id,
                        "type" => $post->type
                    ]; 

                    break;
            }

        } catch(\Exception $e) {
            if($e->getCode() == 190){
                db_update(TB_ACCOUNTS, [ "status" => 0 ], [ "id" => $post->account->id ] );
            }
            unlink_watermark($medias);
            return [
                "status" => "error",
                "message" => __( $e->getMessage() ),
                "type" => $post->type
            ];
        }
    }
}
