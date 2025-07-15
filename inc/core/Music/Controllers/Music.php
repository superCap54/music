<?php
namespace Core\Music\Controllers;

class Music extends \CodeIgniter\Controller
{
    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
        $this->model = new \Core\Music\Models\MusicModel();
        $this->db = \Config\Database::connect();
    }

    public function index($page = 1) {
        // 确保页码是整数
        $page = (int)$page;
        if ($page < 1) {
            $page = 1;
        }

        // 获取音乐总数
        $total = $this->model->getTotalMusic();

        // 分页设置
        $per_page = 30;
        $total_pages = ceil($total / $per_page);
        $page = max(1, min($page, $total_pages));
        $offset = ($page - 1) * $per_page;

        // 获取音乐列表 - 确保返回数组
        $music_list = $this->model->getMusicList($per_page, $offset);

        // 确保 music_list 是数组
        if (!is_array($music_list)) {
            $music_list = [];
        }

        $datatable = [
            "total_items" => $total,
            "per_page" => $per_page,
            "current_page" => $page,
            "total_pages" => $total_pages,
        ];

        $data_content = [
            'total' => $total,
            'datatable' => $datatable,
            'config' => $this->config,
            'music_list' => $music_list,
        ];

        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "content" => view('Core\Music\Views\list', $data_content)
        ];

        return view('Core\Music\Views\index', $data);
    }


    public function import_tsv_index( $page = false ) {
        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
        ];

        // 获取按TSV文件名分组的数据
        $tsv_files = $this->db->table('sp_music_royalties')
            ->select([
                'distrokid_tsv_file_name',
                'tsv_md5',
                'COUNT(*) as records_count',
                'MAX(created_at) as last_updated'
            ])
            ->groupBy('tsv_md5, distrokid_tsv_file_name')
            ->orderBy('last_updated', 'DESC')
            ->get()
            ->getResult();

        $data_content = [
            'tsv_files' => $tsv_files,
            'config' => $this->config
        ];
        $data['content'] = view('Core\Music\Views\import_distrokid_data', $data_content);
        return view('Core\Music\Views\import_index', $data);
    }

    public function ajax_list(){
        $page = post('page') ? post('page') : 1;
        $per_page = 30;
        $total_items = $this->model->getTotalMusic();
        $offset = ($page - 1) * $per_page;

        $result = $this->model->getMusicList($per_page, $offset);

        $data = [
            "result" => $result,
            "config" => $this->config
        ];

        ms([
            "total_items" => $total_items,
            "data" => view('Core\Music\Views\ajax_list', $data)
        ]);
    }

    public function delete( $type ="queue" ){
        $team_id = get_team("id");
        switch ($type) {
            case 'multi':
                
                $type = post("type");
                $social_network = post("social_network");

                switch ($type) {
                    case 'queue':
                        $status = 1;
                        break;

                    case 'published':
                        $status = 3;
                        break;

                    case 'unpublished':
                        $status = 4;
                        break;
                    
                    default:
                        ms([
                            "status" => "error",
                            "message" => __("Delete failed")
                        ]);
                        break;
                }

                $data = [ "team_id" => $team_id, "status" => $status ];
                if($social_network != "all"){
                    $data["social_network"] = $social_network;
                }
                db_delete( TB_POSTS, $data );

                ms([
                    "status" => "success",
                    "message" => __("Success")
                ]);
                break;
            
            default:
                $ids = post("id");
                db_delete( TB_POSTS,  [ "ids" => $ids, "team_id" => $team_id ]);
                ms([
                    "status" => "success",
                    "message" => __("Success")
                ]);
                break;
        }

    }

    public function upload()
    {
        // 确保是AJAX请求
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => __('Invalid request method')
            ]);
        }

        // 验证表单数据
        $validation = \Config\Services::validation();
        $validation->setRules([
            'artist' => 'required|max_length[255]',
            'title' => 'required|max_length[255]',
            'music_file' => 'uploaded[music_file]|max_size[music_file,10240]|ext_in[music_file,mp3,wav,aac,ogg]',
            'cover' => 'max_size[cover,2048]|is_image[cover]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode('<br>', $validation->getErrors())
            ]);
        }

        // 处理文件上传
        $musicFile = $this->request->getFile('music_file');
        $coverFile = $this->request->getFile('cover');

        // 创建上传目录
        $uploadPath = WRITEPATH . 'uploads/music/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // 保存音乐文件
        $newMusicName = $musicFile->getRandomName();
        $musicFile->move($uploadPath, $newMusicName);

        // 保存封面图片（如果有）
        $coverUrl = null;
        if ($coverFile && $coverFile->isValid()) {
            $newCoverName = $coverFile->getRandomName();
            $coverFile->move($uploadPath, $newCoverName);
            $coverUrl = 'uploads/music/' . $newCoverName;
        }

        // 准备数据
        $data = [
            'artist' => $this->request->getPost('artist'),
            'title' => $this->request->getPost('title'),
            'isrc' => $this->request->getPost('isrc'),
            'file_src' => 'uploads/music/' . $newMusicName,
            'cover_url' => $coverUrl,
            'genre' => $this->request->getPost('genre'),
            'release_date' => $this->request->getPost('release_date'),
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // 保存到数据库
        try {
            $this->model->insert($data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => __('Music uploaded successfully')
            ]);
        } catch (\Exception $e) {
            // 删除已上传的文件（如果出错）
            if (file_exists($uploadPath . $newMusicName)) {
                unlink($uploadPath . $newMusicName);
            }
            if ($coverUrl && file_exists($uploadPath . $newCoverName)) {
                unlink($uploadPath . $newCoverName);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => __('Failed to save music: ') . $e->getMessage()
            ]);
        }
    }

    public function download_template()
    {
        // 设置CSV文件名
        $filename = 'music_import_template_' . date('Ymd') . '.csv';

        // 设置CSV头部
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // 打开输出流
        $output = fopen('php://output', 'w');

        // 写入CSV头部(字段名)
        $headers = [
            'artist',         // 艺术家名称 (必填)
            'title',          // 歌曲标题 (必填)
            'cover_url',      // 歌曲封面URL (可选)
            'isrc',           // 国际标准录音代码 (可选)
            'file_src',       // 歌曲文件路径 (必填，可以是相对路径)
            'duration',       // 歌曲时长(秒) (可选)
            'genre',          // 音乐流派 (可选)
            'release_date',   // 发行日期 (YYYY-MM-DD格式) (可选)
            'status'         // 状态 (1:启用,0:禁用) (可选，默认为1)
        ];

        fputcsv($output, $headers);

        // 写入示例数据行
        $exampleData = [
            'Test Artist',    // artist
            'Test Song',      // title
            'https://www.baidu.com/covers/test.jpg', // cover_url
            'USABC1234567',   // isrc
            'https://www.baidu.com/music/test.mp3', // file_src
            '180',            // duration
            'Pop',           // genre
            '2023-01-01',     // release_date
            '1'              // status
        ];
        fputcsv($output, $exampleData);

        // 关闭文件流
        fclose($output);
        exit;
    }

    public function import()
    {
        // 确保是AJAX请求
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => __('Invalid request method')
            ]);
        }

        // 验证CSV文件
        $validation = \Config\Services::validation();
        $validation->setRules([
            'csv_file' => [
                'label' => 'CSV File',
                'rules' => 'uploaded[csv_file]|max_size[csv_file,2048]|ext_in[csv_file,csv]',
                'errors' => [
                    'uploaded' => 'Please select a CSV file',
                    'max_size' => 'File size should be less than 2MB',
                    'ext_in' => 'Only CSV files are allowed'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => __('Validation failed'),
                'errors' => $validation->getErrors()
            ]);
        }

        // 获取上传的CSV文件
        $csvFile = $this->request->getFile('csv_file');

        // 解析CSV文件
        $csvData = array_map('str_getcsv', file($csvFile->getTempName()));

        // 检查CSV是否有效
        if (count($csvData) < 2) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => __('CSV file must contain at least a header row and one data row')
            ]);
        }

        // 获取头部字段
        $headers = array_map('strtolower', $csvData[0]);
        $requiredFields = ['artist', 'title', 'file_src'];

        // 检查必要字段
        $missingFields = array_diff($requiredFields, $headers);
        if (!empty($missingFields)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => __('Missing required fields in CSV'),
                'errors' => [__('CSV must contain these fields: ') . implode(', ', $requiredFields)]
            ]);
        }

        // 处理数据行
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        // 跳过头部行
        array_shift($csvData);

        foreach ($csvData as $index => $row) {
            $lineNumber = $index + 2; // CSV行号(从1开始，头部是1)

            try {
                // 将行数据与头部关联
                $rowData = array_combine($headers, $row);

                // 准备数据
                $data = [
                    'artist' => $rowData['artist'] ?? '',
                    'title' => $rowData['title'] ?? '',
                    'cover_url' => $rowData['cover_url'] ?? null,
                    'isrc' => $rowData['isrc'] ?? null,
                    'file_src' => $rowData['file_src'] ?? '',
                    'duration' => isset($rowData['duration']) ? (int)$rowData['duration'] : null,
                    'genre' => $rowData['genre'] ?? null,
                    'release_date' => $rowData['release_date'] ?? null,
                    'status' => isset($rowData['status']) ? (int)$rowData['status'] : 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // 验证必要字段
                if (empty($data['artist']) || empty($data['title']) || empty($data['file_src'])) {
                    throw new \Exception(__('Line %s: Artist, Title and File Source are required', [$lineNumber]));
                }

                // 保存到数据库
                if (!$this->model->insert($data)) {
                    throw new \Exception(__('Line %s: Failed to save music record', [$lineNumber]));
                }

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = $e->getMessage();
            }
        }

        // 返回结果
        if ($errorCount > 0) {
            return $this->response->setJSON([
                'status' => 'partial',
                'message' => sprintf(__('Imported %d records, %d failed'), $successCount, $errorCount),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => sprintf(__('Successfully imported %d music records'), $successCount),
            'count' => $successCount
        ]);
    }



    public function import_tsv() {
        // 检查是否有文件上传
        if (empty($_FILES['tsv_file']['tmp_name'])) {
            ms([
                "status" => "error",
                "message" => __("Please select a TSV file to upload")
            ]);
        }

        // 获取上传的文件信息
        $file_path = $_FILES['tsv_file']['tmp_name'];
        $file_name = $_FILES['tsv_file']['name'];
        $file_md5 = md5_file($file_path);

        // 检查文件类型
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (strtolower($file_ext) !== 'tsv') {
            ms([
                "status" => "error",
                "message" => __("Only TSV files are allowed")
            ]);
        }

        // 检查是否已导入过该文件
        $existing_file = $this->db->table('sp_music_royalties')
            ->where('tsv_md5', $file_md5)
            ->countAllResults();

        if ($existing_file > 0) {
            ms([
                "status" => "error",
                "message" => __("This TSV file has already been imported")
            ]);
        }

        // 读取TSV文件内容
        $tsv_data = file_get_contents($file_path);
        // 替换所有换行符为 \n，再分割
        $tsv_data = str_replace(["\r\n", "\r"], "\n", $tsv_data);
        $lines = explode("\n", trim($tsv_data));

        // 移除标题行
        $header = str_getcsv(array_shift($lines), "\t");

        // 准备批量插入数据
        $batch_data = [];
        $success_count = 0;
        $error_count = 0;

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $row = str_getcsv($line, "\t");

            // 确保行数据与标题数量匹配
            if (count($row) !== count($header)) {
                $error_count++;
                continue;
            }

            // 将行数据与标题关联
            $row_data = array_combine($header, $row);

            try {
                // 格式化数据以匹配数据库结构
                $db_data = [
                    'distrokid_tsv_file_name' => $file_name,
                    'tsv_md5' => $file_md5,
                    'reporting_date' => $row_data['Reporting Date'] ?? null,
                    'sale_month' => $row_data['Sale Month'] ?? null,
                    'store' => $row_data['Store'] ?? null,
                    'artist' => $row_data['Artist'] ?? null,
                    'title' => $row_data['Title'] ?? null,
                    'isrc' => $row_data['ISRC'] ?? null,
                    'upc' => $row_data['UPC'] ?? null,
                    'quantity' => (int)($row_data['Quantity'] ?? 0),
                    'team_percentage' => (float)($row_data['Team Percentage'] ?? 100.00),
                    'product_type' => $row_data['Song/Album'] ?? 'Song',
                    'country' => $row_data['Country of Sale'] ?? null,
                    'royalties_withheld' => (bool)($row_data['Songwriter Royalties Withheld'] ?? false),
                    'earnings_usd' => (float)($row_data['Earnings (USD)'] ?? 0),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // 验证必要字段
                if (empty($db_data['reporting_date']) || empty($db_data['isrc']) || empty($db_data['title'])) {
                    $error_count++;
                    continue;
                }

                $batch_data[] = $db_data;
                $success_count++;

                // 每500条批量插入一次
                if (count($batch_data) >= 500) {
                    $this->db->table('sp_music_royalties')->insertBatch($batch_data);
                    $batch_data = [];
                }

            } catch (\Exception $e) {
                $error_count++;
                log_message('error', 'TSV导入错误: ' . $e->getMessage());
                continue;
            }
        }

        // 插入剩余数据
        if (!empty($batch_data)) {
            $this->db->table('sp_music_royalties')->insertBatch($batch_data);
        }

        // 返回导入结果
        $message = sprintf(__("Import completed. Success: %d, Failed: %d"), $success_count, $error_count);

        return redirect()->to(get_module_url())->with('success', $message);
    }

    /**
     * 删除指定 TSV 文件及其所有记录
     *
     * @param string $tsv_md5 要删除的 TSV 文件 MD5 值
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete_tsv($tsv_md5 = '')
    {
        // 验证参数
        if (empty($tsv_md5)) {
            return redirect()->to(get_module_url())->with('error', __('Invalid TSV file identifier'));
        }

        // 获取文件名用于显示消息
        $file_info = $this->db->table('sp_music_royalties')
            ->select('distrokid_tsv_file_name')
            ->where('tsv_md5', $tsv_md5)
            ->limit(1)
            ->get()
            ->getRow();

        // 开始事务以确保数据一致性
        $this->db->transStart();

        try {
            // 删除所有相关记录
            $affected_rows = $this->db->table('sp_music_royalties')
                ->where('tsv_md5', $tsv_md5)
                ->delete();

            // 提交事务
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception(__('Database error occurred while deleting records'));
            }

            // 准备成功消息
            $filename = $file_info ? $file_info->distrokid_tsv_file_name : __('Unknown file');
            $message = sprintf(__('Successfully deleted TSV file "%s" and %d associated records'),
                htmlspecialchars($filename),
                $affected_rows);

            return redirect()->to(get_module_url())->with('success', $message);

        } catch (\Exception $e) {
            // 回滚事务
            $this->db->transRollback();

            log_message('error', '删除TSV记录失败: ' . $e->getMessage());
            return redirect()->to(get_module_url())->with('error', __('Failed to delete TSV file: ' . $e->getMessage()));
        }
    }

}