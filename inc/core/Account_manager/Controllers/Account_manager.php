<?php

namespace Core\Account_manager\Controllers;
error_reporting(E_ALL);

class Account_manager extends \CodeIgniter\Controller
{
    public function __construct()
    {
        $this->config = parse_config(include realpath(__DIR__ . "/../Config.php"));
        $this->model = new \Core\Account_manager\Models\Account_managerModel();
    }

    public function index($page = false)
    {
        $permissions = $this->model->block_permissions();
        $block_accounts = $permissions['items'];

        // 检查Google Drive token状态
        $db = \Config\Database::connect();
        $builder = $db->table('sp_google_drive_tokens');
        $token = $builder->where('user_id', get_team("id"))->get()->getRow();

        $google_drive_status = [
            'is_connected' => false,
            'expire_time' => null
        ];

        if ($token) {
            $expire_time = strtotime($token->updated_at) + $token->expires_in;
            $google_drive_status = [
                'is_connected' => ($expire_time > time()),
                'expire_time' => $expire_time
            ];
        }

        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "content" => view('Core\Account_manager\Views\content', [
                'block_accounts' => $block_accounts,
                "config" => $this->config,
                "google_drive_status" => $google_drive_status,
                "module_url" => get_module_url() // 确保这个可用
            ]),
            "block_accounts" => $block_accounts,
            "google_drive_status" => $google_drive_status
        ];

        return view('Core\Account_manager\Views\index', $data);
    }

    // 添加Google Drive授权方法
    public function google_oauth()
    {
        $clientId = $this->config['clientId'];
        $clientSecret = $this->config['clientSecret'];
        $redirectUri = 'http://localhost/account_manager/google_callback'; // 替换为你的实际域名

        try {
            // 初始化Google客户端
            $client = new \Google\Client();
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            $client->setRedirectUri($redirectUri);
            $client->addScope(\Google\Service\Drive::DRIVE_READONLY);
            $client->setAccessType('offline'); // 必须设置为offline才能获取refresh token
            $client->setPrompt('consent'); // 强制每次请求都获取新的refresh token
            $client->setIncludeGrantedScopes(true);

            // 如果没有code参数，跳转到Google授权页面
            if (!isset($_GET['code'])) {
                $authUrl = $client->createAuthUrl();
                return redirect()->to($authUrl);
            }
            // 如果有code参数，继续在google_callback()中处理
            return redirect()->to(base_url('account_manager/google_callback?'.$_SERVER['QUERY_STRING']));
        } catch (\Exception $e) {
            // 错误处理
            return redirect()->to(get_module_url())->with('error', __('Failed to connect Google Drive: ') . $e->getMessage());
        }
    }

    public function google_callback()
    {
        $clientId = $this->config['clientId'];
        $clientSecret = $this->config['clientSecret'];
        $redirectUri = base_url('account_manager/google_callback');

        // 验证授权码是否存在
        if (!$this->request->getGet('code')) {
            throw new \Exception('Authorization code not found');
        }

        $code = $this->request->getGet('code');

        // 代理配置
        $proxy = '207.244.217.165:6712';
        $proxyAuth = 'iggndszq:iguhz2og7m4t';

        // 获取访问令牌
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $tokenData = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        // 创建cURL句柄
        $ch = curl_init();

        // 设置代理
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);

        // 基本设置
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        // 调试设置
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, fopen('curl_debug.log', 'w+'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            file_put_contents('error.log', date('Y-m-d H:i:s') . " - cURL Error: $error\n", FILE_APPEND);
            die("请求失败: $error. 请查看error.log获取详细信息");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            die("HTTP请求失败，状态码: $httpCode. 响应: " . $response);
        }

        $tokenInfo = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON parsing failed');
        }

        if (!isset($tokenInfo['access_token'])) {
            throw new \Exception($tokenInfo['error_description'] ?? 'Failed to fetch access token');
        }

        // 使用 Google API 客户端获取用户信息
        $googleClient = new \Google\Client();
        $googleClient->setAccessToken($tokenInfo);

        // 准备要存储到 sp_google_drive_tokens 表的数据
        $tokenData = [
            'user_id' => get_team("id"), // 假设这是你的用户ID
            'access_token' => $tokenInfo['access_token'],
            'refresh_token' => $tokenInfo['refresh_token'] ?? null,
            'expires_in' => $tokenInfo['expires_in'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        // 使用 CI4 的数据库类插入数据
        $db = \Config\Database::connect();
        $builder = $db->table('sp_google_drive_tokens');

        // 检查是否已存在该用户的记录
        $existing = $builder->where('user_id', get_team("id"))
            ->countAllResults();

        if ($existing > 0) {
            // 更新现有记录
            $builder->where('user_id', get_team("id"))
                ->update($tokenData);
        } else {
            // 插入新记录
            $builder->insert($tokenData);
        }

        // 重定向到成功页面
        return redirect()->to(get_module_url())
            ->with('success', 'Google Drive account connected successfully');

    }

    public function google_logout()
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('sp_google_drive_tokens');

            // 删除当前用户的token记录
            $builder->where('user_id', get_team("id"))->delete();

            // 同时从账户表中移除Google Drive账户
            db_delete(TB_ACCOUNTS, [
                'team_id' => get_team("id"),
                'social_network' => 'google_drive'
            ]);

            // 设置成功消息并重定向回index
            return redirect()->to(get_module_url())->with('success', 'Google Drive账户已成功退出');

        } catch (\Exception $e) {
            // 设置错误消息并重定向回index
            return redirect()->to(get_module_url())->with('error', '退出Google Drive失败: ' . $e->getMessage());
        }
    }

    public function widget($params = [])
    {
        $team_id = get_team("id");

        if (isset($params['wheres']) && is_array($params['wheres'])) {
            $wheres["team_id"] = $team_id;
            $accounts = db_fetch("id,ids,pid,name,pid,category,social_network,avatar,login_type,module", TB_ACCOUNTS, $params['wheres']);
        } elseif (isset($params['accounts']) && is_array($params['accounts'])) {
            $field = "id";
            if (isset($params['field']) && $params['field'] != "") {
                $field = $params['field'];
            }
            $accounts = $this->model->get_accounts_by($params['accounts'], $field);
        } elseif (isset($params['account_id']) && $params['account_id'] != "") {
            $accounts = db_fetch("id,ids,pid,name,pid,category,social_network,avatar,login_type,module", TB_ACCOUNTS, ["can_post" => 1, "team_id" => $team_id, "id" => $params['account_id']], "social_network", "ASC");
        } else {
            $accounts = db_fetch("id,ids,pid,name,pid,category,social_network,avatar,login_type,module", TB_ACCOUNTS, ["can_post" => 1, "team_id" => $team_id, "status" => 1], "social_network", "ASC");
        }


        permission_accounts($accounts);

        if (isset($params['module_permission']) && is_string($params['module_permission'])) {
            permission_accounts_by_module($params['module_permission'], $accounts);
        }

        return view('Core\Account_manager\Views\widget', ['accounts' => $accounts]);
    }

    public function widget_multi_select($params = [])
    {
        $team_id = get_team("id");
        $accounts = db_fetch("id,ids,pid,name,pid,category,social_network,avatar", TB_ACCOUNTS, "can_post = 1 AND team_id = '{$team_id}' AND status = 1", "social_network", "ASC");

        $selected_accounts = [];
        if (isset($params['accounts']) && !empty($params['accounts'])) {
            $selected_accounts = $params['accounts'];
        }

        return view('Core\Account_manager\Views\widget_multi_select', ['accounts' => $accounts, "selected_accounts" => $selected_accounts]);
    }

    public function delete()
    {
        $ids = post('id');

        if (empty($ids)) {
            ms([
                "status" => "error",
                "message" => __('Please select an item to delete')
            ]);
        }

        if (is_array($ids)) {
            foreach ($ids as $id) {
                db_delete(TB_ACCOUNTS, ['ids' => $id]);
            }
        } elseif (is_string($ids)) {
            db_delete(TB_ACCOUNTS, ['ids' => $ids]);
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);
    }
}