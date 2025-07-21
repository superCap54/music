<?php

namespace Core\MusicLib\Models;

class MusicLibModel extends \CodeIgniter\Model
{
    protected $table = 'sp_music_library';
    protected $primaryKey = 'id';
    protected $allowedFields = ['artist', 'title', 'isrc', 'upc', 'file_src', 'cover_url', 'genre', 'release_date', 'status'];
    protected $returnType = 'array'; // 明确指定返回类型
    protected $useSoftDeletes = false;

    public function __construct()
    {
        parent::__construct(); // 确保调用父类构造函数
        $this->config = parse_config(include realpath(__DIR__ . "/../Config.php"));
    }

    // 在 MusicLibModel 类中添加以下方法

    public function getTotalMusic($search = null) {
        $builder = $this->builder();

        // 添加子查询来排除已授权的音乐
        $subquery = $this->db->table('sp_user_music_licenses')
            ->select('music_id')
            ->where('expiry_date >', date('Y-m-d H:i:s')); // 只考虑未过期的授权

        $builder->whereNotIn('id', $subquery);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('title', $search)
                ->orLike('artist', $search)
                ->orLike('genre', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getMusicList($limit, $offset, $search = null) {
        $builder = $this->builder()
            ->select('*,TIME_FORMAT(SEC_TO_TIME(duration), "%i:%s") AS formatted_duration')
            ->limit($limit, $offset);

        // 添加子查询来排除已授权的音乐
        $subquery = $this->db->table('sp_user_music_licenses')
            ->select('music_id')
            ->where('expiry_date >', date('Y-m-d H:i:s')); // 只考虑未过期的授权

        $builder->whereNotIn('id', $subquery);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('title', $search)
                ->orLike('artist', $search)
                ->orLike('genre', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }
}
