<?php

namespace Core\Music\Models;

class MusicModel extends \CodeIgniter\Model
{
    protected $table = 'sp_music_library';
    protected $primaryKey = 'id';
    protected $allowedFields = ['artist', 'title', 'isrc', 'upc', 'file_src', 'cover_url', 'genre', 'release_date', 'status'];
    protected $returnType = 'array'; // 明确指定返回类型
    protected $useSoftDeletes = false;

    public function __construct()
    {
        $this->config = parse_config(include realpath(__DIR__ . "/../Config.php"));
    }

    public function block_plans()
    {
        return [
            "tab" => 30,
            "position" => 200,
            "label" => __("Advanced features"),
            "items" => [
                [
                    "id" => $this->config['id'],
                    "name" => __("Draft posts")
                ]
            ]
        ];
    }

    public function list($category, $return_data = true)
    {
        $current_page = (int)(post("current_page") - 1);
        $per_page = post("per_page");
        $total_items = post("total_items");
        $keyword = post("keyword");

        $status = 0;
        $team_id = get_team("id");
        $db = \Config\Database::connect();
        $builder = $db->table(TB_POSTS . " as a");
        $builder->select("
			from_unixtime(a.time_post,'%Y-%m-%d %H:%i:%s') as time_posts, 
			from_unixtime(a.repost_until,'%Y-%m-%d %H:%i:%s') as repost_untils, 
			a.time_post, 
			a.repost_frequency, 
			a.repost_until, 
			a.team_id, 
			a.social_network, 
			a.category,
			a.type,
			a.id,
			a.ids,
			a.data,
			a.status,
			a.result,
			b.name,
			b.username,
			b.avatar,
			b.url
		");

        $builder->join(TB_ACCOUNTS . " as b", "a.account_id = b.id");

        $cate = "";
        if (strip_tags($category) != "all") {
            $cate = " a.social_network = '{$category}' AND ";
        }

        $builder->where(" {$cate} a.status = '{$status}' AND a.team_id = '{$team_id}' ");

        if (!$return_data) {
            $result = $builder->countAllResults();
        } else {
            $builder->limit($per_page, $per_page * $current_page);
            $builder->orderBy("a.id", "ASC");
            $query = $builder->get();
            $result = $query->getResult();
            $query->freeResult();

            if ($result) {
                foreach ($result as $key => $value) {
                    $config = find_modules($value->social_network);

                    /*if( !_p($value->category."_enable") ){
                        unset( $result[$key] );
                        continue;
                    }*/

                    if ($config) {
                        $result[$key]->module_name = $config['module_name'];
                        $result[$key]->icon = $config['icon'];
                        $result[$key]->color = $config['color'];

                    } else {

                        $result[$key]->module_name = "";
                        $result[$key]->icon = "";
                        $result[$key]->color = "";
                    }
                }
            }

        }

        return $result;
    }

    public function getTotalMusic()
    {
        return $this->builder()->countAllResults();
    }

    public function getMusicList($limit = 30, $offset = 0)
    {
        return $this->builder()
            ->select('sp_music_library.*, 
                 TIME_FORMAT(SEC_TO_TIME(duration), "%i:%s") AS formatted_duration,
                 IF(COUNT(sp_user_music_licenses.music_id) > 0, 1, 0) AS is_bound')
            ->join('sp_user_music_licenses', 'sp_user_music_licenses.music_id = sp_music_library.id', 'left')
            ->where('sp_music_library.status', 1)
            ->groupBy('sp_music_library.id')  // 确保按音乐ID分组
            ->orderBy('title', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    // 在 MusicModel 类中添加
    public function deleteMusic($id)
    {
        return $this->delete($id);
    }
}
