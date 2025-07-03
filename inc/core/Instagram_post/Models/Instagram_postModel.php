<?php
namespace Core\Instagram_post\Models;
use CodeIgniter\Model;

class Instagram_postModel extends Model
{
    public function __construct(){
        $this->config = include realpath( __DIR__."/../Config.php" );
        include get_module_dir( __DIR__ , '../Instagram_profiles/Libraries/Instagram_unofficial.php');
        $this->app_id = get_option('instagram_client_id', '');
        $this->app_secret = get_option('instagram_client_secret', '');
        $this->app_version = get_option('instagram_app_version', 'v18.0');

        if( get_option('instagram_official_status', 0) && $this->app_id && $this->app_secret && $this->app_version){
            $fb = new \JanuSoftware\Facebook\Facebook([
                'app_id' => $this->app_id,
                'app_secret' => $this->app_secret,
                'default_graph_version' => $this->app_version,
            ]);

            $this->fb = $fb;
        }
    }

    public function block_can_post(){
        return true;
    }

    public function block_plans(){
        return [
            "tab" => 10,
            "position" => 200,
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
            "position" => 200,
            "preview" => view( 'Core\Instagram_post\Views\preview', [ 'config' => $this->config ] ),
            "advance_options" => view( 'Core\Instagram_post\Views\advance_options', [ 'config' => $this->config ] )
        ];
    }

    public function post_validator($post){
        $errors = array();
        $data = json_decode( $post->data , 1);
        $medias = $data['medias'];

        if($post->social_network == 'instagram'){

            if($post->api_type == 1){
                if($post->type != "media" && $post->type != "link"){
                    $errors[] = __("Instagram API Official just support post with Photo type");
                }

                if( isset( $data['advance_options'] ) && isset( $data['advance_options']['ig_post_type'] ) ){
                    switch ($data['advance_options']['ig_post_type']) {
                        case 'reels':
                            if( is_image($medias[0]) ){
                                $errors[] = __("Instagram Reels only supports posting videos of minimum 3 seconds and maximum 15 minutes");
                            }
                            break;

                        case 'story':
                            //$errors[] = __("Instagram API Official not support post to story");
                            break;

                        case 'igtv':
                            $errors[] = __("Instagram API Official not support post to IGTV");
                            break;
                    }
                }

            }

            if($post->api_type == 2){
                if($post->type != "media" && $post->type != "link"){
                    $errors[] = __("Instagram API Official just support post with Photo type");
                }

                if( isset( $data['advance_options'] ) && isset( $data['advance_options']['ig_post_type'] ) ){
                    switch ($data['advance_options']['ig_post_type']) {
                        case 'reels':
                            if( is_image($medias[0]) ){
                                $errors[] = __("Instagram Reels only supports posting videos of minimum 3 seconds and maximum 15 minutes");
                            }
                            break;

                        case 'story':
                            //$errors[] = __("Instagram API Official not support post to story");
                            break;

                        case 'igtv':
                            $errors[] = __("Instagram API Official not support post to IGTV");
                            break;
                    }
                }

            }

            switch ($post->type) {
                case 'text':
                    $errors[] = __("Instagram requires an image or video");
                    break;

                case 'link':
                    if(empty($data['medias'])){
                        $errors[] = __("Instagram requires an image or video");
                    }
                    break;

                case 'igtv':
                    if(!isset($data['advance_options']['ig_title']) || $data['advance_options']['ig_title'] == ""){
                        $errors[] = __("Instagram requires title for IGTV");
                    }
                    break;
            }

        }

        return $errors;
    }

    public function post_handler($post){
        if($post->api_type == 2){
            return $this->post_unofficial($post);
        }else{
            return $this->post_official($post);
        }
    }

    public function post_official($post){
        $data = json_decode($post->data, false);
        $medias = $data->medias;
        $endpoint = "/".$post->account->pid."/media_publish";
        $upload_endpoint = "/".$post->account->pid."/media";
        $post_type = "media";
        $shortlink_by = shortlink_by($data);
        $comment = "";

        if( isset( $data->advance_options ) && isset( $data->advance_options->ig_post_type ) ){
            $post_type = $data->advance_options->ig_post_type;
        }

        if( isset( $data->advance_options ) && isset( $data->advance_options->ig_first_comment ) ){
            $comment = $data->advance_options->ig_first_comment;
        }

        try
        {
            $caption = shortlink( spintax($data->caption), $data );
            switch ($post_type) {
                case 'story':
                    if( is_image($medias[0]) ){
                        $medias[0] = watermark($medias[0], $post->account->team_id, $post->account->id);

                        $upload_params = [
                            'media_type' => "STORIES",
                            'image_url' => get_file_url($medias[0]),
                            'caption' => $caption,
                        ];

                        $upload_response = $this->fb->post( $upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();

                        //Publish
                        $params = [
                            'creation_id' => $upload_response['id'],
                        ];

                        $response = $this->fb->post( $endpoint, $params, $post->account->token)->getDecodedBody();
                        $media_response = $this->fb->get( "/". $response["id"]."?fields=shortcode", $post->account->token)->getDecodedBody();
                        unlink_watermark($medias);

                        try {
                            if($comment != ""){
                                $this->fb->post( "/".$response["id"]."/comments", [
                                    "message" => $comment
                                ], $post->account->token)->getDecodedBody();
                            }
                        } catch (\Exception $e) {}

                        return [
                            "status" => "success",
                            "message" => __('Success'),
                            "id" => $response["id"],
                            "url" => "https://www.instagram.com/stories/".$post->account->username,
                            "type" => $post->type
                        ]; 
                    }else{
                        try {
                            $upload_params = [
                                'media_type' => "STORIES",
                                'video_url' => get_file_url($medias[0]),
                                'caption' => $caption

                            ];
                            $upload_response = $this->fb->post( $upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();
                        } catch (\Exception $e) {
                        }

                        $attempts = 0;
                        do {
                            $attempts++;
                            sleep(2);
                            try {
                                //Publish
                                $params = [
                                    'creation_id' => $upload_response['id'],
                                ];

                                $response = $this->fb->post( $endpoint, $params, $post->account->token)->getDecodedBody();

                                if(isset($response["id"])){
                                    try {
                                        if($comment != ""){
                                            $this->fb->post( "/".$response["id"]."/comments", [
                                                "message" => $comment
                                            ], $post->account->token)->getDecodedBody();
                                        }
                                    } catch (\Exception $e) {}

                                    $media_response = $this->fb->get( "/". $response["id"]."?fields=shortcode", $post->account->token)->getDecodedBody();
                                    return [
                                        "status" => "success",
                                        "message" => __('Success'),
                                        "id" => $response["id"],
                                        "url" => "https://www.instagram.com/p/".$media_response['shortcode'],
                                        "type" => $post->type
                                    ]; 
                                }
                            } catch (\Exception $e) {}
                        } while($attempts <= 30);

                        return [
                            "status" => "error",
                            "message" => __('The media is not ready for publishing, please wait for a moment'),
                        ]; 
                    }

                    break;

                case 'reels':
                    try {
                        $upload_params = [
                            'media_type' => "REELS",
                            'video_url' => get_file_url($medias[0]),
                            'caption' => $caption

                        ];
                        $upload_response = $this->fb->post( $upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();
                    } catch (\Exception $e) {
                    }

                    $attempts = 0;
                    do {
                        $attempts++;
                        sleep(2);
                        try {
                            //Publish
                            $params = [
                                'creation_id' => $upload_response['id'],
                            ];

                            $response = $this->fb->post( $endpoint, $params, $post->account->token)->getDecodedBody();

                            if(isset($response["id"])){
                                try {
                                    if($comment != ""){
                                        $this->fb->post( "/".$response["id"]."/comments", [
                                            "message" => $comment
                                        ], $post->account->token)->getDecodedBody();
                                    }
                                } catch (\Exception $e) {}

                                $media_response = $this->fb->get( "/". $response["id"]."?fields=shortcode", $post->account->token)->getDecodedBody();
                                return [
                                    "status" => "success",
                                    "message" => __('Success'),
                                    "id" => $response["id"],
                                    "url" => "https://www.instagram.com/p/".$media_response['shortcode'],
                                    "type" => $post->type
                                ]; 
                            }
                        } catch (\Exception $e) {}
                    } while($attempts <= 30);

                    return [
                        "status" => "error",
                        "message" => __('The media is not ready for publishing, please wait for a moment'),
                    ]; 

                    break;
                
                default:
                    
                    if( count($medias) == 1 ){
                        if( is_image($medias[0]) ){
                            $medias[0] = watermark($medias[0], $post->account->team_id, $post->account->id);

                            $upload_params = [
                                'image_url' => get_file_url($medias[0]),
                                'caption' => $caption,
                            ];

                            $upload_response = $this->fb->post( $upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();

                            //Publish
                            $params = [
                                'creation_id' => $upload_response['id'],
                            ];

                            $response = $this->fb->post( $endpoint, $params, $post->account->token)->getDecodedBody();
                            $media_response = $this->fb->get( "/". $response["id"]."?fields=shortcode", $post->account->token)->getDecodedBody();
                            unlink_watermark($medias);

                            try {
                                if($comment != ""){
                                    $this->fb->post( "/".$response["id"]."/comments", [
                                        "message" => $comment
                                    ], $post->account->token)->getDecodedBody();
                                }
                            } catch (\Exception $e) {}

                            return [
                                "status" => "success",
                                "message" => __('Success'),
                                "id" => $response["id"],
                                "url" => "https://www.instagram.com/p/".$media_response['shortcode'],
                                "type" => $post->type
                            ]; 
                        }
                        else
                        {
                            try {
                                $upload_params = [
                                    'media_type' => "REELS",
                                    'video_url' => get_file_url($medias[0]),
                                    'caption' => $caption

                                ];
                                $upload_response = $this->fb->post( $upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();
                            } catch (\Exception $e) {
                            }

                            $attempts = 0;
                            do {
                                $attempts++;
                                sleep(2);
                                try {
                                    //Publish
                                    $params = [
                                        'creation_id' => $upload_response['id'],
                                    ];

                                    $response = $this->fb->post( $endpoint, $params, $post->account->token)->getDecodedBody();

                                    if(isset($response["id"])){
                                        try {
                                            if($comment != ""){
                                                $this->fb->post( "/".$response["id"]."/comments", [
                                                    "message" => $comment
                                                ], $post->account->token)->getDecodedBody();
                                            }
                                        } catch (\Exception $e) {}
                                        
                                        $media_response = $this->fb->get( "/". $response["id"]."?fields=shortcode", $post->account->token)->getDecodedBody();
                                        return [
                                            "status" => "success",
                                            "message" => __('Success'),
                                            "id" => $response["id"],
                                            "url" => "https://www.instagram.com/p/".$media_response['shortcode'],
                                            "type" => $post->type
                                        ]; 
                                    }
                                } catch (\Exception $e) {}
                            } while($attempts <= 30);

                            return [
                                "status" => "error",
                                "message" => __('The media is not ready for publishing, please wait for a moment'),
                            ]; 
                        }
                    }else{

                        $media_ids = [];

                        foreach ($medias as $key => $media) {

                            if(is_image($medias[$key])){
                                $medias[$key] = watermark($media, $post->account->team_id, $post->account->id);
                                $medias[$key] = get_file_url($medias[$key]);
                                
                                $upload_params = [
                                    'image_url' => $medias[$key],
                                    'caption' => $caption,
                                    'is_carousel_item' => true
                                ];

                            }else{
                                $medias[$key] = get_file_url($medias[$key]);
                                
                                $upload_params = [
                                    'media_type' => "VIDEO",
                                    'video_url' => $medias[$key],
                                    'caption' => $caption,
                                    'is_carousel_item' => true
                                ];
                            }

                            
                            $upload_response = $this->fb->post( $upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();

                            $media_ids[] = $upload_response['id'];
                        }

                        $upload_params = [
                            'media_type' => 'CAROUSEL',
                            'children' => $media_ids,
                            'caption' => $caption
                        ];
                        
                        $upload_response = $this->fb->post( $upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();
  
                        //Publish
                        $params = [
                            'creation_id' => $upload_response['id']
                        ];

                        $response = $this->fb->post( $endpoint, $params, $post->account->token)->getDecodedBody();
                        $media_response = $this->fb->get( "/". $response["id"]."?fields=shortcode", $post->account->token)->getDecodedBody();
                        unlink_watermark($medias);

                        try {
                            if($comment != ""){
                                $this->fb->post( "/".$response["id"]."/comments", [
                                    "message" => $comment
                                ], $post->account->token)->getDecodedBody();
                            }
                        } catch (\Exception $e) {}

                        return [
                            "status" => "success",
                            "message" => __('Success'),
                            "id" => $response["id"],
                            "url" => "https://www.instagram.com/p/".$media_response['shortcode'],
                            "type" => $post->type
                        ]; 
                    }

                    break;
            }
        }
        catch (\Exception $e)
        {
            unlink_watermark($medias);
            return [
                "status" => "error",
                "message" => __( $e->getMessage() ),
                "type" => $post->type
            ];
        }
    }

    public function post_unofficial($post){
        $data = json_decode($post->data, false);
        $medias = $data->medias;
        $post_type = "media";
        $shortlink_by = shortlink_by($data);
        $comment = "";

        if( isset( $data->advance_options ) && isset( $data->advance_options->ig_post_type ) ){
            $post_type = $data->advance_options->ig_post_type;
        }

        if( isset( $data->advance_options ) && isset( $data->advance_options->ig_first_comment ) ){
            $comment = $data->advance_options->ig_first_comment;
        }

        if( $post->account->token == "" ){
            db_update(TB_ACCOUNTS, [ "status" => 0 ], [ "id" => $post->account->id ] );
            return [
                "status" => "error",
                "message" => __( "You have not authorized your Instagram account yet. Please re-login and try again" ),
                "type" => $post->type
            ];
        }

        $accessToken = json_decode($post->account->token);

        if( !is_array($accessToken) && (!isset($accessToken->ig_username) || !isset($accessToken->ig_password)) ){
            db_update(TB_ACCOUNTS, [ "status" => 0 ], [ "id" => $post->account->id ] );
            return [
                "status" => "error",
                "message" => __( "You have not authorized your Instagram account yet. Please re-login and try again" ),
                "type" => $post->type
            ];
        }

        $ig_username = $accessToken->ig_username;
        $ig_password = encrypt_decode($accessToken->ig_password);

        $proxy = get_proxy($post->account->proxy);
        $ig_auth = new \Instagram_unofficial($ig_username, $ig_password, $post->team_id, $proxy);

        try
        {
            $caption = shortlink( spintax($data->caption), $data );
            $link = "";
            switch ($post_type) {
                case 'story':
                    if( is_image($medias[0]) ){
                        $medias[0] = watermark($medias[0], $post->account->team_id, $post->account->id);
                        $response = $ig_auth->uploadPhoto($post->account->pid, get_file_path($medias[0]), $caption, $link, 'story');
                        unlink_watermark($medias);

                        if($response['status'] == 'error'){
                            return [
                                "status" => "error",
                                "message" => $response['message'],
                                "type" => $post->type
                            ]; 
                        }

                        if($response['status'] == 'ok'){
                            if($comment != ""){
                                $ig_auth->writeComment($comment, $response["id"]);
                            }

                            return [
                                "status" => "success",
                                "message" => __('Success'),
                                "id" => $response["id"],
                                "url" => "https://www.instagram.com/p/".$response['code'],
                                "type" => $post->type
                            ]; 
                        }
                    }else{
                        $response = $ig_auth->uploadVideo($post->account->pid, get_file_path($medias[0]), $caption, $link, 'story');

                        if($response['status'] == 'error'){
                            return [
                                "status" => "error",
                                "message" => $response['message'],
                                "type" => $post->type
                            ]; 
                        }

                        if($response['status'] == 'ok'){
                            if($comment != ""){
                                $ig_auth->writeComment($comment, $response["id"]);
                            }

                            return [
                                "status" => "success",
                                "message" => __('Success'),
                                "id" => $response["id"],
                                "url" => "https://www.instagram.com/p/".$response['code'],
                                "type" => $post->type
                            ]; 
                        }
                    }

                    break;

                case 'reels':
                    $medias[0] = watermark($medias[0], $post->account->team_id, $post->account->id);
                    $response = $ig_auth->uploadVideo($post->account->pid, get_file_path($medias[0]), $caption, $link);

                    if($response['status'] == 'error'){
                        return [
                            "status" => "error",
                            "message" => $response['message'],
                            "type" => $post->type
                        ]; 
                    }

                    if($response['status'] == 'ok'){

                        if($comment != ""){
                            $ig_auth->writeComment($comment, $response["id"]);
                        }

                        return [
                            "status" => "success",
                            "message" => __('Success'),
                            "id" => $response["id"],
                            "url" => "https://www.instagram.com/p/".$response['code'],
                            "type" => $post->type
                        ]; 
                    }

                    break;
                
                default:
                    if( count($medias) == 1 ){
                        if( is_image($medias[0]) ){
                            $medias[0] = watermark($medias[0], $post->account->team_id, $post->account->id);
                            $response = $ig_auth->uploadPhoto($post->account->pid, get_file_path($medias[0]), $caption, $link);
                            unlink_watermark($medias);

                            if($response['status'] == 'error'){
                                return [
                                    "status" => "error",
                                    "message" => $response['message'],
                                    "type" => $post->type
                                ]; 
                            }

                            if($response['status'] == 'ok'){
                                if($comment != ""){
                                    $ig_auth->writeComment($comment, $response["id"]);
                                }

                                return [
                                    "status" => "success",
                                    "message" => __('Success'),
                                    "id" => $response["id"],
                                    "url" => "https://www.instagram.com/p/".$response['code'],
                                    "type" => $post->type
                                ]; 
                            }
                        }
                        else
                        {
                            $response = $ig_auth->uploadVideo($post->account->pid, get_file_path($medias[0]), $caption, $link);

                            if($response['status'] == 'error'){
                                return [
                                    "status" => "error",
                                    "message" => $response['message'],
                                    "type" => $post->type
                                ]; 
                            }

                            if($response['status'] == 'ok'){
                                if($comment != ""){
                                    $ig_auth->writeComment($comment, $response["id"]);
                                }

                                return [
                                    "status" => "success",
                                    "message" => __('Success'),
                                    "id" => $response["id"],
                                    "url" => "https://www.instagram.com/p/".$response['code'],
                                    "type" => $post->type
                                ]; 
                            }
                        }
                    }else{
                        $media_ids = [];
                        foreach ($medias as $key => $media) {
                            if(is_image($medias[$key])){
                                $medias[$key] = watermark($media, $post->account->team_id, $post->account->id);
                                $medias[$key] = get_file_path($medias[$key]);
                            }
                        }

                        $response = $ig_auth->generateAlbum($post->account->pid, $medias, $caption, 0);
                        unlink_watermark($medias);

                        if($response['status'] == 'ok'){
                            if($comment != ""){
                                $ig_auth->writeComment($comment, $response["id"]);
                            }

                            return [
                                "status" => "success",
                                "message" => __('Success'),
                                "id" => $response["id"],
                                "url" => "https://www.instagram.com/p/".$response['code'],
                                "type" => $post->type
                            ]; 
                        }
                    }

                    break;
            }
        }
        catch (\Exception $e)
        {
            unlink_watermark($medias);
            return [
                "status" => "error",
                "message" => __( $e->getMessage() ),
                "type" => $post->type
            ];
        }
    }
}