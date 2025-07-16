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

    public function index($sale_month = false)
    {
        $user_id = get_user("id");


        // 1. 获取用户授权的音乐列表
        $licensed_music = $this->model->getLicensedMusic($user_id);

        // 处理歌曲数据
        $songsIsrcList = [];
        foreach ($licensed_music as $music) {
            $songsIsrcList[$music['isrc']] = [
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
        }

        $licensedIsrcs = array_column($songsIsrcList, 'isrc');
        $dashboardData = ['views' => 0, 'earnings' => 0, 'countriesReached' => 0];
        $monthlyData = [];
        $songsDataList = [];
        $countryEarnings = [];
        $countryChart = [];

        if (!empty($licensedIsrcs)) {
            // 2. 获取仪表盘数据
            $dashboardResult = $this->model->getDashboardData($licensedIsrcs, $sale_month);
            $dashboardData = [
                'views' => $dashboardResult['total_views'] ?? 0,
                'earnings' => $dashboardResult['total_earnings'] ?? 0,
                'countriesReached' => $dashboardResult['countries_reached'] ?? 0
            ];

            // 3. 获取月度数据
            $rawMonthlyData = $this->model->getMonthlyData($licensedIsrcs, $sale_month);
            usort($rawMonthlyData, function ($a, $b) {
                return strcmp($a['month'], $b['month']);
            });
            $monthlyData = $this->completeBillingCycleData($rawMonthlyData);
            // 4. 获取歌曲表现数据
            $performanceResults = $this->model->getSongPerformanceData($licensedIsrcs, $sale_month);
            foreach ($performanceResults as $row) {
                $songsDataList[] = [
                    'title' => $songsIsrcList[$row['isrc']]['title'],
                    'platform' => 'All Platform',
                    'icon' => '',
                    'date' => $row['sale_month'],
                    'views' => $row['total_plays'],
                    'topCountry' => $row['top_country'],
                    'earns' => $row['earnings'],
                    'imgSrc' => $songsIsrcList[$row['isrc']]['imgSrc'],
                ];
            }

            // 5. 获取国家收入数据
            $countryEarnings = $this->model->getCountryEarnings($licensedIsrcs, $sale_month);

            // 处理国家图表数据
            $totalEarnings = $dashboardResult['total_earnings'] ?? 1;
            $other = ['country' => 'Other', 'total_views' => 0, 'total_earnings' => 0, 'percentage' => 0];

            foreach ($countryEarnings as $i => &$country) {
                $percentage = round(($country['total_earnings'] / $totalEarnings) * 100, 2);
                $country['index'] = $i + 1;
                $country['percentage'] = $percentage;

                if ($i < 5) {
                    $countryChart[] = [
                        'country' => $country['country'],
                        'total_views' => (int)$country['total_views'],
                        'total_earnings' => (float)$country['total_earnings'],
                        'percentage' => $percentage
                    ];
                } else {
                    $other['total_views'] += (int)$country['total_views'];
                    $other['total_earnings'] += (float)$country['total_earnings'];
                    $other['percentage'] += $percentage;
                }
            }

            if (count($countryEarnings) > 5) {
                $other['percentage'] = round(($other['total_earnings'] / $totalEarnings) * 100, 2);
                $countryChart[] = $other;
            }
        }

        $assignData = [
            'songsList' => $songsIsrcList,
            'songsDataList' => $songsDataList,
            'dashboardData' => $dashboardData,
            'monthlyData' => $monthlyData,
            'countryEarnings' => $countryEarnings,
            'countryChart' => $countryChart
        ];

        // 正常返回视图
        $data = [
            "title" => $this->config['name'],
            "desc" => $this->config['desc'],
            "content" => view('Core\Musicdata\Views\content', $assignData)
        ];

        return view('Core\Musicdata\Views\index', $data);
    }

    /**
     * 将月度数据补全为过去12个月的完整数据
     * @param array $rawData 原始数据库查询结果
     * @return array 包含过去12个月完整数据的数组
     */
    protected function completeBillingCycleData(array $rawData): array
    {
        // 1. 计算账单周期截止月份（当前月份减2个月）
        $cutoffDate = new \DateTime('first day of this month');
        $cutoffDate->modify('-2 months');

        // 2. 生成12个月的月份列表（从截止月份往前推11个月）
        $months = [];
        $startDate = clone $cutoffDate;
        $startDate->modify('-11 months'); // 总共12个月（截止月+前11个月）

        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1M'),
            11 // 11次迭代，总共12个月
        );

        foreach ($period as $date) {
            $months[] = $date->format('Y-m');
        }

        // 3. 将原始数据转换为以月份为键的关联数组
        $dataMap = [];
        foreach ($rawData as $item) {
            $dataMap[$item['month']] = [
                'views' => (int)$item['views'],
                'earnings' => round($item['earnings'],4)
            ];
        }

        // 4. 构建完整12个月的数据
        $completeData = [];
        foreach ($months as $month) {
            $completeData[] = [
                'month' => $month,
                'month_name' => date('F', strtotime($month . '-01')), // 添加可读的月份名称
                'views' => $dataMap[$month]['views'] ?? 0,
                'earnings' => $dataMap[$month]['earnings'] ?? 0.0
            ];
        }

        return $completeData;
    }

}