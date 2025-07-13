<?php
namespace Core\Blog_manager\Controllers;

class Blog_manager extends \CodeIgniter\Controller
{
    protected $db;

    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
        $this->class_name = get_class_name($this);
        $this->model = new \Core\Blog_manager\Models\Blog_managerModel();
        $this->db = \Config\Database::connect();
    }

    public function index( $page = false ) {
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

        $data['content'] = view('Core\Blog_manager\Views\import_distrokid_data', $data_content);
        return view('Core\Blog_manager\Views\index', $data);
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
//        ms([
//            "status" => "success",
//            "message" => $message,
//            "result" => [
//                'success_count' => $success_count,
//                'error_count' => $error_count,
//                'file_name' => $file_name
//            ]
//        ]);

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
    
    public function index1( $page = false ) {
        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
        ];

        switch ( $page ) {
            case 'update':
                $item = false;
                $ids = uri('segment', 4);
                if( $ids ){
                    $item = db_get("*", TB_BLOGS, [ "ids" => $ids ]);
                }

                $data['content'] = view('Core\Blog_manager\Views\update', ["result" => $item]);
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
                        "user" => __("Content"),
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

                $data['content'] = view('Core\Blog_manager\Views\list', $data_content);
                break;
        }

        return view('Core\Blog_manager\Views\index', $data);
    }

    public function ajax_list(){
        $total_items = $this->model->get_list(false);
        $result = $this->model->get_list(true);
        $data = [
            "result" => $result,
            "config" => $this->config
        ];
        ms( [
            "total_items" => $total_items,
            "data" => view('Core\Blog_manager\Views\ajax_list', $data)
        ] );
    }

    public function save( $ids = "" ){
        $status = (int)post("status");
        $title = post("title");
        $desc = post("desc");
        $tags = post("tags");
        $content = post("content");
        $img = post("img");
        $item = false;

        if ($ids != "") {
            $item = db_get("*", TB_BLOGS, ["ids" => $ids]);
        }

        if (!$this->validate([
            'title' => 'required'
        ])) {
            ms([
                "status" => "error",
                "message" => __("Title is required")
            ]);
        }

        if (!$this->validate([
            'desc' => 'required'
        ])) {
            ms([
                "status" => "error",
                "message" => __("Description is required")
            ]);
        }

        if (!$this->validate([
            'content' => 'required'
        ])) {
            ms([
                "status" => "error",
                "message" => __("Content is required")
            ]);
        }

        if (!$this->validate([
            'img' => 'required'
        ])) {
            ms([
                "status" => "error",
                "message" => __("Image is required")
            ]);
        }

        $data = [
            "title" => $title,
            "desc" => $desc,
            "tags" => $tags,
            "content" => $content,
            "img" => $img,
            "status" => $status,
            "changed" => time(),
        ];

        if( empty($item) ){
            $data['status'] = $status;
            $data['ids'] = ids();
            $data['created'] = time();

            db_insert(TB_BLOGS, $data);
        }else{
            db_update(TB_BLOGS, $data, [ "id" => $item->id ]);
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);
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
                db_delete(TB_BLOGS, ['ids' => $id]);
            }
        }
        elseif( is_string($ids) )
        {
            db_delete(TB_BLOGS, ['ids' => $ids]);
        }

        ms([
            "status" => "success",
            "message" => __('Success')
        ]);
    }
}