<?php

namespace Core\Post\Controllers;
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

class Post extends \CodeIgniter\Controller
{
    public function __construct()
    {
        $this->config = parse_config(include realpath(__DIR__ . "/../Config.php"));
        $this->model = new \Core\Post\Models\PostModel();
        $this->account_manager_model = new \Core\Account_manager\Models\Account_managerModel();
        $this->workflows_model = new \Core\Post\Models\WorkflowsModel();
        //获取当前用户id
        $this->user_id = get_user("id");
    }

    public function index1()
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
            "content" => view('Core\Post\Views\make',[
                'workflows' => $workflowsWithStatus,  // 明确指定变量名
                "post" => $post
            ])
        ];
        return view('Core\Post\Views\index', $data);
    }

    public function index( $page = false ) {
        $post_id = get("post_id");
        $team_id = get_team("id");
        $caption = get("caption");

        if($caption != ""){
            $caption = base64_decode($caption);
            $caption = preg_replace("/U\+([0-9a-f]{4,5})/mi", '&#x${1}', $caption);
        }

        // 刷新谷歌token
        $google_drive = new \Core\File_manager\Controllers\Google();
        $google_drive->refresh_token();

        $post = db_get( "*", TB_POSTS, [ "ids" => $post_id, "team_id" => $team_id ] );

        $request = \Config\Services::request();
        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "config" => $this->config,
            "post" => json_encode($post),
            "content" => view('Core\Post\Views\composer', ['frame_posts' => $request->block_frame_posts, "post" => $post, "caption" => $caption ])
        ];

        return view('Core\Post\Views\index', $data);
    }

    public function update_workflow_status()
    {
        // 验证请求方法必须是POST
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => '仅支持POST请求'
            ]);
        }
        // 验证请求内容类型是JSON或表单数据
        $contentType = $this->request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === false &&
            strpos($contentType, 'application/x-www-form-urlencoded') === false) {
            return $this->response->setStatusCode(415)->setJSON([
                'success' => false,
                'message' => '仅支持JSON或表单格式数据'
            ]);
        }
        // 获取输入数据
        if (strpos($contentType, 'application/json') !== false) {
            $json = $this->request->getBody();
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '无效的JSON数据'
                ]);
            }
        } else {
            $data = $this->request->getPost();
        }

        // 验证必要参数
        if (empty($data['workflow_id']) || !is_numeric($data['workflow_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '无效的工作流ID'
            ]);
        }

        // 验证is_active参数是否存在
        if (!isset($data['is_active'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '缺少is_active参数'
            ]);
        }

        // 获取原始输入数据
        $json = $this->request->getBody();
        $data = json_decode($json, true);

        // 验证输入
        if (empty($data['workflow_id']) || !is_numeric($data['workflow_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '无效的工作流ID'
            ]);
        }

        $workflowId = $data['workflow_id'];
        $isActive = $data['is_active'];

        // 验证输入
        if (empty($workflowId) || !is_numeric($workflowId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '无效的工作流ID'
            ]);
        }

        try {
            // 调用模型方法更新状态
            $success = $this->workflows_model->updateUserWorkflowStatus($this->user_id, $workflowId, $isActive);

            if ($success) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => '状态更新成功'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => '更新失败，可能记录不存在'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', '更新工作流状态失败: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '服务器错误: ' . $e->getMessage()
            ]);
        }
    }

    public function url_info()
    {
        $url = post("url");
        validate("link", "", $url);
        $info = get_link_info($url);
        return ms([
            "status" => "success",
            "data" => $info
        ]);
    }

    public function save($skip_validate = false)
    {
        $list_data = [];
        $team_id = get_team("id");
        $accounts = post("accounts");
        $type = post("type");
        $medias = post("medias");
        $advance_options = post("advance_options");
        $post_by = post("post_by");
        $time_now = time();
        $time_post = (int)timestamp_sql(post("time_post"));
        $interval_per_post = (int)post("interval_per_post");
        $repost_frequency = (int)post("repost_frequency");
        $repost_until = (int)timestamp_sql(post("repost_until"));
        $time_posts = post("time_posts");
        $caption = post("caption");
        $link = post("link");

        validate('empty', __('Please select at least a profile'), $accounts);

        $temp_files_to_delete = []; // 新增：用于记录需要删除的临时文件
        switch ($type) {
            case "download":
                validate('empty', __('Please select at least one file'), post("file_ids"));
                $file_ids = post("file_ids");
                $downloaded_files = [];
                // 初始化Google Drive客户端
                $google_drive = new \Core\File_manager\Controllers\Google();
                foreach ($file_ids as $file_url) {
                    $file_names = $this->generateRandomFilename("mp4");
                    try {
                        // 从URL中提取文件ID
                        $file_id = $this->extractGoogleDriveFileId($file_url);
                        if (!$file_id) {
                            throw new \Exception(__("Invalid Google Drive file URL"));
                        }


                        // 下载文件到临时目录
                        $temp_file = WRITEPATH . 'uploads/' . uniqid() . '_' . $file_names;
                        $download_success = $google_drive->download_file($file_id, $temp_file);

                        if (!$download_success) {
                            throw new \Exception(__("Failed to download file from Google Drive"));
                        }
                        // 记录需要删除的临时文件
                        $temp_files_to_delete[] = $temp_file;
                        // 添加到下载文件列表 - 修改这里确保格式正确
                        $downloaded_files[] = str_replace(WRITEPATH, "", $temp_file);
                    } catch (\Exception $e) {
                        log_message('error', 'Google Drive download error: ' . $e->getMessage());
                        continue; // 跳过失败的文件，继续处理下一个
                    }
                }

                if (empty($downloaded_files)) {
                    $this->deleteTempFiles($temp_files_to_delete);
                    ms([
                        "status" => "error",
                        "message" => __("No files were successfully downloaded")
                    ]);
                }
                // 准备上传到YouTube的数据
                $medias = $downloaded_files;
                $type = "media";
                break;

            case "media":
                validate('empty', __('Please select at least one media'), $medias);
                break;

            case "link":
                validate('null', __('Link'), $link);
                validate('link', '', $link);
                break;

            default:
                $type = "text";
                validate('null', __('Caption'), $caption);
                break;
        }

        $postData = [
            "caption" => $caption,
            "link" => $link,
            "medias" => is_array($medias) ? $medias : [$medias],
            "advance_options" => $advance_options,
        ];

        $data = [
            "team_id" => $team_id,
            "function" => "post",
            "type" => $type,
            "data" => json_encode($postData),
            "time_post" => 0,
            "delay" => $interval_per_post,
            "repost_frequency" => $repost_frequency,
            "repost_until" => $repost_frequency == 0 ? NULL : $repost_until,
            "result" => "",
            "status" => 1,
            "changed" => time(),
            "created" => time(),
        ];

        switch ($post_by) {
            case 2:
                validate('null', __('Time post'), $time_post);
                validate('repost_frequency', __('Repost frequency'), $repost_frequency, 0);
                validate('min_number', __('Interval per post'), $interval_per_post, 1);

                if ($time_post <= $time_now) {
                    ms([
                        "status" => "error",
                        "message" => __("Time post must be greater than current time")
                    ]);
                }

                if ($repost_frequency > 0 && $time_post > $repost_until) {
                    ms([
                        "status" => "error",
                        "message" => __("Time post must be smaller than repost until")
                    ]);
                }

                if ($repost_frequency > 0) {
                    validate('null', __('Repost until'), $repost_until);
                }

                $data['time_post'] = $time_post;
                break;

            case 3:
                validate('empty', __('Please select at least a time post'), $time_posts);
                $time_posts = array_unique($time_posts);
                $data['repost_frequency'] = 0;
                $data['repost_until'] = NULL;
                $data['delay'] = 0;
                break;

            case 4:
                $data['status'] = 0;
                $data['delay'] = 5;
                $data['time_post'] = NULL;
                $data['repost_until'] = NULL;
                break;

            default:
                $data['time_post'] = $time_now;
                break;
        }

        $list_accounts = $this->account_manager_model->get_accounts_by($accounts);

        if (empty($list_accounts)) {
            validate('empty', __('Accounts selected is inactive. Let re-login and try again'), $list_accounts);
        }

        foreach ($list_accounts as $key => $value) {
            $ids = post("ids") ? post("ids") : ids();
            $data['ids'] = $ids;
            $data['account_id'] = $value->id;
            $data['social_network'] = $value->social_network;
            $data['category'] = $value->category;
            $data['api_type'] = $value->login_type;

            if ($post_by == 3) {
                foreach ($time_posts as $time) {
                    $data['time_post'] = (int)timestamp_sql($time);
                    $list_data[] = (object)$data;
                }
            } elseif ($post_by == 2) {
                $data['time_post'] = $time_post + ($interval_per_post * $key * 60);
                $list_data[] = (object)$data;
            } else {
                $list_data[] = (object)$data;
            }
        }

        $validator = $this->model->validator($list_data);

        $social_can_post = json_decode($validator["can_post"]);
        if (($skip_validate && !empty($social_can_post)) || $validator["status"] == "success") {
            $result = $this->model->post($list_data, $social_can_post);
            $this->deleteTempFiles($temp_files_to_delete);
            ms($result);
        }
        ms($validator);
    }

    /**
     * 删除临时文件
     * @param array $files 要删除的文件路径数组
     */
    private function deleteTempFiles(array $files) {
        if (empty($files)) {return true;}
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

    function generateRandomFilename($extension = '') {
        $prefix = bin2hex(random_bytes(4)); // 8字符随机前缀
        $filename = uniqid($prefix . '_', true); // 生成唯一ID
        $filename = str_replace('.', '', $filename); // 移除uniqid中的点

        if (!empty($extension)) {
            $filename .= '.' . ltrim($extension, '.');
        }

        return $filename;
    }

    /**
     * 从Google Drive URL中提取文件ID
     */
    private function extractGoogleDriveFileId($url) {
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

    public function report()
    {
        $team_id = get_team("id");
        $social_network = post("social_network");
        $configs = get_blocks("block_frame_posts", false, true);
        $items = [];
        if (!empty($configs)) {
            $items = $configs;
            if (count($items) >= 2) {
                usort($items, function ($a, $b) {
                    if (isset($a['data']['position']) && isset($b['data']['position']))
                        return $a['data']['position'] <=> $b['data']['position'];
                });
            }
        }

        $total_succeed = 0;
        $total_failed = 0;
        $total_media_succeed = 0;
        $total_link_succeed = 0;
        $total_text_succeed = 0;
        if (!empty($items)) {
            foreach ($items as $key => $value) {

                if ($social_network != "all") {
                    if ($value["parent"]["id"] == $social_network) {
                        $total_succeed += get_team_data($value["id"] . "_success_count", 0, $team_id);
                        $total_failed += get_team_data($value["id"] . "_error_count", 0, $team_id);
                        $total_media_succeed += get_team_data($value["id"] . "_media_count", 0, $team_id);
                        $total_link_succeed += get_team_data($value["id"] . "_link_count", 0, $team_id);
                        $total_text_succeed += get_team_data($value["id"] . "_text_count", 0, $team_id);
                    }
                } else {
                    $total_succeed += get_team_data($value["id"] . "_success_count", 0, $team_id);
                    $total_failed += get_team_data($value["id"] . "_error_count", 0, $team_id);
                    $total_media_succeed += get_team_data($value["id"] . "_media_count", 0, $team_id);
                    $total_link_succeed += get_team_data($value["id"] . "_link_count", 0, $team_id);
                    $total_text_succeed += get_team_data($value["id"] . "_text_count", 0, $team_id);
                }
            }
        }

        $total_post_type = $total_media_succeed + $total_link_succeed + $total_text_succeed;

        if ($total_post_type > 0) {
            $percent_media_succeed = round($total_media_succeed / $total_post_type * 100);
            $percent_link_succeed = round($total_link_succeed / $total_post_type * 100);
            $percent_text_succeed = round($total_text_succeed / $total_post_type * 100);
        } else {
            $percent_media_succeed = 0;
            $percent_link_succeed = 0;
            $percent_text_succeed = 0;
        }

        $total_post = $total_succeed + $total_failed;
        $post_succeed = $this->model->get_report_by_status(3);
        $post_failed = $this->model->get_report_by_status(4);

        //
        $recent_posts = $this->model->get_recent_posts();

        $daterange = addslashes(post("daterange"));
        if ($daterange != "") {
            $daterange = explode(",", $daterange);
        } else {
            $daterange = [];
        }


        $dashboardData = ['views' => '1,254,879', 'earnings' => '8,742.35', 'activeSong' => '10', 'countriesReached' => '42'];

        $monthlyData = [
            ['month' => 'January', 'views' => '88,543', 'earnings' => '211114.56'],
            ['month' => 'January', 'views' => '21,543', 'earnings' => '214.56'],
            ['month' => 'January', 'views' => '21,543', 'earnings' => '2111114.56'],
            ['month' => 'January', 'views' => '88,543', 'earnings' => '211114.56'],
            ['month' => 'January', 'views' => '21,543', 'earnings' => '214.56'],
            ['month' => 'January', 'views' => '21,543', 'earnings' => '2111114.56'],
            ['month' => 'January', 'views' => '88,543', 'earnings' => '211114.56'],
            ['month' => 'January', 'views' => '21,543', 'earnings' => '214.56'],
            ['month' => 'January', 'views' => '21,543', 'earnings' => '2111114.56'],
            ['month' => 'January', 'views' => '88,543', 'earnings' => '211114.56'],
            ['month' => 'January', 'views' => '21,543', 'earnings' => '214.56'],
            ['month' => 'January', 'views' => '21,543', 'earnings' => '2111114.56'],
        ];

        $songsDataList = [
            ['title' => 'Song 1', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'United States', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
            ['title' => 'Song 1', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'United Kingdom', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
            ['title' => 'Song 1', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'Germany', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
            ['title' => 'Song 1', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'United States', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
            ['title' => 'Song 1', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'United Kingdom', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
            ['title' => 'Song 2', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'Germany', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
            ['title' => 'Song 2', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'United States', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
            ['title' => 'Song 2', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'United Kingdom', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
            ['title' => 'Song 2', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'Germany', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
            ['title' => 'Song 2', 'platform' => 'All Platform', 'icon' => '', 'date' => 'May 15 - Jun 15, 2025', 'views' => '384,729', 'topCountry' => 'Germany', 'earns' => '2458.92', 'imgSrc' => "https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
        ];
        $countryList = [
            ['countryName' => 'United States', 'value' => '458325', 'color' => '#ff7a00'],
            ['countryName' => 'United Kingdom', 'value' => '287192', 'color' => '#00b4ff'],
            ['countryName' => 'Germany', 'value' => '198745', 'color' => '#64ffda'],
            ['countryName' => 'Canada', 'value' => '156387', 'color' => '#ff00ff'],
            ['countryName' => 'Other', 'value' => '156387', 'color' => '#fff012'],
        ];
        $songsList = [
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3', 'title' => 'Song 1', 'isrc' => 'QZES72032915', 'fileExtension' => pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/2.wav', 'title' => 'Song 2', 'isrc' => 'QZES99032915', 'fileExtension' => pathinfo("http://localhost/music/2.wav", PATHINFO_EXTENSION)],
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3', 'title' => 'Song 1', 'isrc' => 'QZES72032915', 'fileExtension' => pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3', 'title' => 'Song 1', 'isrc' => 'QZES72032915', 'fileExtension' => pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3', 'title' => 'Song 1', 'isrc' => 'QZES72032915', 'fileExtension' => pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3', 'title' => 'Song 1', 'isrc' => 'QZES72032915', 'fileExtension' => pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3', 'title' => 'Song 1', 'isrc' => 'QZES72032915', 'fileExtension' => pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3', 'title' => 'Song 1', 'isrc' => 'QZES72032915', 'fileExtension' => pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3', 'title' => 'Song 1', 'isrc' => 'QZES72032915', 'fileExtension' => pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
            ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3', 'title' => 'Song 1', 'isrc' => 'QZES72032915', 'fileExtension' => pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)]
        ];
        $assignData = ['songsList' => $songsList, 'countryList' => $countryList, 'songsDataList' => $songsDataList, 'dashboardData' => $dashboardData, 'monthlyData' => $monthlyData,
            "total_media_succeed" => $total_media_succeed,
            "total_link_succeed" => $total_link_succeed,
            "total_text_succeed" => $total_text_succeed,
            "percent_media_succeed" => $percent_media_succeed,
            "percent_link_succeed" => $percent_link_succeed,
            "percent_text_succeed" => $percent_text_succeed,
            "total_succeed" => $total_succeed,
            "total_failed" => $total_failed,
            "total_post" => $total_post,
            "recent_posts" => $recent_posts,
            "date" => $post_succeed["date"],
            "post_succeed" => $post_succeed["value"],
            "post_failed" => $post_failed["value"]
        ];
        return view('Core\Post\Views\insights', $assignData);
    }

    public function cron()
    {
        $posts = $this->model->get_posts();

        if (!$posts) {
            _ec("Empty schedule");
            exit(0);
        }

        foreach ($posts as $post) {

            db_update(TB_POSTS, [
                "status" => 4,
                "result" => json_encode(["message" => __("Unknow error")])
            ], ["id" => $post->id]);

            $list_data = [$post];
            $validator = $this->model->validator($list_data);
            $social_can_post = json_decode($validator["can_post"]);
            if (!empty($social_can_post) || $validator["status"] == "success") {
                $result = $this->model->post($list_data, $social_can_post);
                _ec(strtoupper(__(ucfirst($result['status']))) . ": " . __($result['message']) . "<br/>", false);
            }
        }

    }
}