<?php

namespace Core\File_manager\Controllers;

use Google\Client;
use Google\Service\Drive;

class Google extends \CodeIgniter\Controller
{
    protected $client;
    protected $drive;

    public function __construct()
    {
        $this->config = parse_config(include realpath(__DIR__ . "/../Config.php"));
        $this->client = new Client();
        // 使用配置中的clientId和clientSecret
        $this->client->setClientId($this->config['clientId']);
        $this->client->setClientSecret($this->config['clientSecret']);
        $this->client->addScope(Drive::DRIVE_READONLY);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * 刷新Google访问令牌
     */
    public function refresh_token()
    {
        $userId = get_user("id");
        if (empty($userId)) {
            throw new \Exception('User not authenticated');
        }
        $db = \Config\Database::connect();
        $builder = $db->table('sp_google_drive_tokens');

        $tokenData = $builder->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if (empty($tokenData) || empty($tokenData['refresh_token'])) {
            throw new \Exception('No Google Drive refresh token found for this user');
        }
        // 2. 检查 Token 是否已过期（或即将过期）
        $currentTime = time();
        $expiresAt = $tokenData['expires_in'] ?? 0;

        // 如果 Token 未过期（且剩余时间 > 10 分钟），直接返回
        if ($expiresAt > $currentTime + 600) {
            return [
                'status' => 'success',
                'message' => 'Token is still valid',
                'expires_at' => date('Y-m-d H:i:s', $expiresAt),
                'access_token' => $tokenData['access_token'],
            ];
        }
        $refreshToken = $tokenData['refresh_token'];

        // 3. 初始化Google客户端
        $client = new \Google\Client();
        $client->setClientId($this->config['clientId']);
        $client->setClientSecret($this->config['clientSecret']);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        // 设置代理
        $proxy = '38.154.227.167:5868';
        $proxyAuth = 'iggndszq:iguhz2og7m4t';
        $httpClient = new \GuzzleHttp\Client([
            'proxy' => [
                'http' => "socks5://{$proxyAuth}@{$proxy}",
                'https' => "socks5://{$proxyAuth}@{$proxy}",
            ],
            'verify' => false // 如果需要忽略SSL验证
        ]);
        $client->setHttpClient($httpClient);

        try {
            // 从数据库或缓存获取refresh token
            $client->refreshToken($refreshToken);
            $newAccessToken = $client->getAccessToken();

// 5. 更新数据库中的token信息
            $expiresIn = time() + $newAccessToken['expires_in'];
            $updateData = [
                'access_token' => $newAccessToken['access_token'],
                'expires_in' => $expiresIn,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $builder->where('user_id', $userId)
                ->update($updateData);

            return [
                'status' => 'success',
                'message' => 'Token is valid',
                'expires_at' => date('Y-m-d H:i:s', $expiresIn),
                'access_token' => $newAccessToken['access_token'],
            ];
        } catch (\Exception $e) {
            log_message('error', 'Google token refresh failed: ' . $e->getMessage());
            throw $e; // 重新抛出异常让调用方处理
        }
    }

    /**
     * 获取Google Drive中的MP4文件
     */
    public function get_mp4_files()
    {
        try {
            // 1. 刷新访问令牌
            $tokenStatus = $this->refresh_token();
            if ($tokenStatus['status'] != 'success') {
                throw new \Exception('Failed to refresh Google access token');
            }

            // 2. 获取当前用户的访问令牌
            $userId = get_team("id");
            $db = \Config\Database::connect();
            $builder = $db->table('sp_google_drive_tokens');

            $tokenData = $builder->where('user_id', $userId)
                ->get()
                ->getRowArray();

            if (empty($tokenData) || empty($tokenData['access_token'])) {
                throw new \Exception('No valid Google Drive access token found');
            }

            $accessToken = $tokenData['access_token'];

            // 3. 初始化Google Drive服务
            $client = new \Google\Client();
            $client->setAccessToken(['access_token' => $accessToken]);
            $drive = new \Google\Service\Drive($client);

            // 4. 设置代理
            $proxy = '38.154.227.167:5868';
            $proxyAuth = 'iggndszq:iguhz2og7m4t';
            $httpClient = new \GuzzleHttp\Client([
                'proxy' => [
                    'http' => "socks5://{$proxyAuth}@{$proxy}",
                    'https' => "socks5://{$proxyAuth}@{$proxy}",
                ],
                'verify' => false // 如果需要忽略SSL验证
            ]);
            $client->setHttpClient($httpClient);

            // 5. 查询MP4文件
            $query = "mimeType='video/mp4' and trashed=false";
            $optParams = [
                'fields' => 'files(id,name,size,mimeType,modifiedTime,webViewLink,thumbnailLink)',
                'pageSize' => 100,
                'q' => $query
            ];

            $results = $drive->files->listFiles($optParams);
            $files = $results->getFiles();

            // 6. 格式化返回数据
            $formattedFiles = [];
            foreach ($files as $file) {
                $formattedFiles[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'size' => $file->getSize(),
                    'mimeType' => $file->getMimeType(),
                    'modifiedTime' => $file->getModifiedTime(),
                    'webViewLink' => $file->getWebViewLink(),
                    'thumbnailLink' => $file->getThumbnailLink(),
                    'source' => 'google_drive'
                ];
            }

            return $formattedFiles;

        } catch (\Exception $e) {
            log_message('error', 'Google Drive MP4 files fetch failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 下载Google Drive文件
     */
    public function download_file($file_id, $destination_path) {
            // 1. 刷新访问令牌
            $tokenStatus = $this->refresh_token();

            if ($tokenStatus['status'] != 'success') {
                throw new \Exception('Failed to refresh Google access token');
            }
            $accessToken = $tokenStatus['access_token'];

            // 2. 代理配置
            $proxy = '38.154.227.167:5868';
            $proxyAuth = 'iggndszq:iguhz2og7m4t';

            // 3. 构建下载URL
            $downloadUrl = "https://www.googleapis.com/drive/v3/files/{$file_id}?alt=media";

            // 4. 初始化cURL
            $ch = curl_init($downloadUrl);

            // 代理设置
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);

            // 基本设置
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);

            // 5. 执行下载
            $fileContent = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new \Exception('下载失败: ' . curl_error($ch));
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode != 200) {
                throw new \Exception('下载失败，HTTP状态码: ' . $httpCode);
            }
            // 6. 保存文件
            $bytesWritten = file_put_contents($destination_path, $fileContent);
            if ($bytesWritten === false) {
                throw new \Exception('无法写入文件到本地路径');
            }

            return true;

    }
}