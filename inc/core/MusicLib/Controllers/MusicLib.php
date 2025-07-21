<?php
namespace Core\MusicLib\Controllers;

//用户看到的公海Library
class MusicLib extends \CodeIgniter\Controller
{
    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
        $this->model = new \Core\MusicLib\Models\MusicLibModel();
        $this->db = \Config\Database::connect();
    }

    public function index($page = 1) {
        $search = uri('segment', 4);
        // 确保页码是整数
        $page = (int)$page;
        if ($page < 1) {
            $page = 1;
        }
        // 获取音乐总数
        $total = $this->model->getTotalMusic($search);

        // 分页设置
        $per_page = 15;
        $total_pages = ceil($total / $per_page);
        $page = max(1, min($page, $total_pages));
        $offset = ($page - 1) * $per_page;


        // 获取音乐列表 - 确保返回数组
        $music_list = $this->model->getMusicList($per_page, $offset,$search);

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
            'search' => $search,
        ];

        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "content" => view('Core\MusicLib\Views\list', $data_content),
        ];

        return view('Core\MusicLib\Views\index', $data);
    }


}