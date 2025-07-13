<?php
namespace Core\Music\Controllers;

class Music extends \CodeIgniter\Controller
{
    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
        $this->model = new \Core\Music\Models\MusicModel();
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
}