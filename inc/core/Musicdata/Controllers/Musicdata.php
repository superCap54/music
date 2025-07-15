<?php

namespace Core\Musicdata\Controllers;

class Musicdata extends \CodeIgniter\Controller
{
    protected $db;

    public function __construct()
    {
        $this->config = parse_config(include realpath(__DIR__ . "/../Config.php"));
        $this->class_name = get_class_name($this);
        $this->model = new \Core\Musicdata\Models\DistrokidModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $user_id = get_user("id"); // 获取当前用户ID
        $timeRange = post("timeRange");
        //如果有值，代码是ajax过来的
        if ($timeRange) {
            var_dump($timeRange);
            exit;
        }


        // 1.获取用户授权的音乐列表
        $db = \Config\Database::connect();
        $builder = $db->table('sp_user_music_licenses as uml');
        $builder->select('ml.*, uml.expiry_date as license_expiry_date');
        $builder->join('sp_music_library as ml', 'ml.id = uml.music_id');
        $builder->where('uml.user_id', $user_id);
        $builder->where('uml.expiry_date >', time()); // 只获取未过期的授权
        $licensed_music = $builder->get()->getResultArray();

        $songsIsrcList = [];
        foreach ($licensed_music as $music) {
            $songsItem = [
                'id' => $music['id'],
                'imgSrc' => $music['cover_url'],
                'audioSrc' => $music['file_src'],
                'title' => $music['title'],
                'isrc' => $music['isrc'],
                'fileExtension' => pathinfo($music['file_src'] ?? '', PATHINFO_EXTENSION),
                'duration' => $music['duration'],
                'artist' => $music['artist'],
                'license_expiry' => date('Y-m-d', strtotime($music['license_expiry_date']))
            ];
            $songsIsrcList[$music['isrc']] = $songsItem;
        }
        //获取到当前用户拥有isrc权限的歌曲
        $licensedIsrcs = array_column($songsIsrcList, 'isrc');

        // 获取仪表盘数据
        $dashboardData = [
            'views' => 0,
            'earnings' => 0,
            'countriesReached' => 0
        ];

        $monthlyData = [];
        $songsDataList = [];
        $countryEarnings = [];
        if (!empty($licensedIsrcs)) {
            $dashboardQuery = $db->table('sp_music_royalties')
                ->select([
                    'SUM(quantity) as total_views',
                    'SUM(earnings_usd) as total_earnings',
                    'COUNT(DISTINCT country) as countries_reached'
                ])
                ->whereIn('isrc', $licensedIsrcs)
                ->whereIn('store',['YouTube (Ads)','YouTube (ContentID)','YouTube (Red)'])
                ->get();

            $dashboardResult = $dashboardQuery->getRowArray();
            // 格式化数据
            $dashboardData = [
                'views' => number_format($dashboardResult['total_views'] ?? 0),
                'earnings' => number_format($dashboardResult['total_earnings'] ?? 0, 4),
                'countriesReached' => $dashboardResult['countries_reached'] ?? 0
            ];

            //获取用户授权的歌的Monthly Data
            $builder = $db->table('sp_music_royalties');
            $monthlyData = $builder->select([
                'sale_month as month',
                'SUM(quantity) as views',
                'SUM(earnings_usd) as earnings'
            ])
                ->whereIn('isrc', $licensedIsrcs)
                ->groupBy('sale_month')
                ->orderBy('sale_month', 'DESC') // 改为降序排列，最新的月份在前
                ->limit(12) // 限制12条记录
                ->get()
                ->getResultArray();
            // 按月份升序重新排序（为了计算涨幅）
            usort($monthlyData, function ($a, $b) {
                return strcmp($a['month'], $b['month']);
            });
            // 计算涨跌幅
            $processedData = [];
            for ($i = 0; $i < count($monthlyData); $i++) {
                $currentMonth = $monthlyData[$i];
                $growth = null;

                // 如果不是第一个月，计算涨幅
                if ($i > 0) {
                    $prevMonth = $monthlyData[$i - 1];

                    // 计算播放量涨幅
                    if ($prevMonth['views'] != 0) {
                        $growth['views_growth'] =
                            (($currentMonth['views'] - $prevMonth['views']) / $prevMonth['views']) * 100;
                    }

                    // 计算收入涨幅
                    if ($prevMonth['earnings'] != 0) {
                        $growth['earnings_growth'] =
                            (($currentMonth['earnings'] - $prevMonth['earnings']) / $prevMonth['earnings']) * 100;
                    }
                }

                $processedData[] = [
                    'month' => $currentMonth['month'],
                    'views' => $currentMonth['views'],
                    'earnings' => round($currentMonth['earnings'], 4),
                    'growth_rate' => $growth
                ];
            }

            // 再次按月份降序排列（最新的在前）
            $monthlyData = array_reverse($processedData);


//            $sale_month = date('Y-m', strtotime('-2 months'));
            $builder = $db->table('sp_music_royalties a');
            // 选择需要的字段
            $builder->select([
                'a.isrc',
                'a.title',
                'a.sale_month',
                'SUM(a.quantity) AS total_plays',
                'SUM(a.earnings_usd) AS earnings',
                '(SELECT b.country 
          FROM sp_music_royalties b 
          WHERE b.isrc = a.isrc 
            AND b.sale_month = a.sale_month
          GROUP BY b.country
          ORDER BY SUM(b.earnings_usd) DESC
          LIMIT 1) AS top_country'
            ]);

            // 使用whereIn确保查询多个ISRC
            $builder->whereIn('a.isrc', $licensedIsrcs);
//            $builder->where('a.sale_month', $sale_month);  // 主查询限制月份
            $builder->whereIn('a.store', ['YouTube (Ads)', 'YouTube (ContentID)', 'YouTube (Red)']);

            // 按ISRC和月份分组
            $builder->groupBy('a.isrc', 'a.sale_month', 'a.title');

            // 按月份降序排列
            $builder->orderBy('a.sale_month', 'DESC');

            // 获取所有结果
            $results = $builder->get()->getResultArray();
            // 格式化输出
            $formattedData = [];
            foreach ($results as $row) {
                $formattedData[] = [
                    'title' => $songsIsrcList[$row['isrc']]['title'],
                    'platform' => 'All Platform',
                    'icon' => '',
                    'date' => $row['sale_month'],
                    'views' => number_format($row['total_plays']),
                    'topCountry' => $row['top_country'],
                    'earns' => number_format($row['earnings'], 4),
                    'imgSrc' => $songsIsrcList[$row['isrc']]['imgSrc'],
                ];
            }
            $songsDataList = $formattedData;


            // 新增：获取地区收入排行数据
            $countryEarningsQuery = $db->table('sp_music_royalties')
                ->select([
                    'country',
                    'SUM(quantity) as total_views',
                    'SUM(earnings_usd) as total_earnings'
                ])
                ->whereIn('isrc', $licensedIsrcs)
                ->whereIn('store',['YouTube (Ads)','YouTube (ContentID)','YouTube (Red)'])
                ->groupBy('country')
                ->orderBy('total_earnings', 'DESC')
                ->get();

            $countryEarnings = $countryEarningsQuery->getResultArray();

            // 格式化地区收入数据
            $totalEarnings = $dashboardResult['total_earnings'] ?? 1; // 避免除以0
            $i = 0;
            foreach ($countryEarnings as &$country) {
                $country['index'] = ++$i;
                $country['percentage'] = round(($country['total_earnings'] / $totalEarnings) * 100, 2);
            }
        }

        $assignData = ['songsList' => $songsIsrcList, 'songsDataList' => $songsDataList, 'dashboardData' => $dashboardData, 'monthlyData' => $monthlyData,'countryEarnings'=>$countryEarnings];

        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "content" => view('Core\Musicdata\Views\content', $assignData)
        ];
        return view('Core\Musicdata\Views\index', $data);
    }


}