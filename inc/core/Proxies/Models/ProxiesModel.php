<?php
namespace Core\Proxies\Models;
use CodeIgniter\Model;

class ProxiesModel extends Model
{
    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
    }

    public function check_proxy_status($proxy) {
        $url = 'https://www.youtube.com';
        $timeout = 10; // 10秒超时

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_PROXY => $proxy, // 直接使用 user:pass@ip:port 格式
            CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
            CURLOPT_HTTPPROXYTUNNEL => true,
            CURLOPT_PROXYAUTH => CURLAUTH_BASIC,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);

        try {
            $startTime = microtime(true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $totalTime = round(microtime(true) - $startTime, 2);

            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                return [
                    'status' => false,
                    'error' => $error ?: 'Unknown proxy error',
                    'time' => $totalTime
                ];
            }

            // 只要能从YouTube获取响应（即使403），就认为代理可用
            return [
                'status' => true,
                'http_code' => $httpCode,
                'time' => $totalTime,
                'proxy' => $proxy
            ];

        } catch (Exception $e) {
            if (is_resource($ch)) {
                curl_close($ch);
            }
            return [
                'status' => false,
                'error' => $e->getMessage(),
                'time' => 0
            ];
        }
    }

    public function block_plans(){
        return [
            "tab" => 30,
            "position" => 600,
            "label" => __("Advanced features"),
            "items" => [
                [
                    "id" => $this->config['id'],
                    "name" => $this->config['name'],
                ]
            ]
        ];
    }

    public function get_list( $return_data = true )
    {
        $team_id = get_team("id");
        $current_page = (int)(post("current_page") - 1);
        $per_page = post("per_page");
        $total_items = post("total_items");
        $keyword = post("keyword");

        $db = \Config\Database::connect();
        $builder = $db->table(TB_PROXIES);
        $builder->select('*');
        $builder->where("( team_id = '{$team_id}' )");

        if( $keyword ){
            $builder->where("( proxy LIKE '%{$keyword}%' OR location LIKE '%{$keyword}%' )") ;
        }

        if( !$return_data )
        {
            $result =  $builder->countAllResults();
        }
        else
        {
            $builder->limit($per_page, $per_page*$current_page);
            $query = $builder->get();
            $result = $query->getResult();
            $query->freeResult();
        }
        
        return $result;
    }

    public function get_list_assigned( $return_data = true )
    {
        $team_id = get_team("id");
        $accounts = $this->get_account_use_proxies();
        $current_page = (int)(post("current_page") - 1);
        $per_page = post("per_page");
        $total_items = post("total_items");
        $keyword = post("keyword");

        if(!$accounts){
            return false;
        }

        $db = \Config\Database::connect();
        $builder = $db->table(TB_ACCOUNTS. " as a");
        $builder->select('a.id, a.ids, a.username as account_username, a.name as account_name, a.social_network, b.proxy, d.fullname, d.username, a.avatar, d.email, a.ids, b.proxy, b.location, b.team_id, b.is_system');
        $builder->join(TB_PROXIES." as b", "a.proxy = b.id", "LEFT");
        $builder->join(TB_TEAM." as c", "a.team_id = c.id");
        $builder->join(TB_USERS." as d", "c.owner = d.id");
        $builder->where("( a.team_id = '{$team_id}' AND a.id IN (".implode(",", $accounts).") )");

        if( $keyword ){
            $builder->where("( a.social_network LIKE '%{$keyword}%' OR a.name LIKE '%{$keyword}%' OR a.username LIKE '%{$keyword}%' OR b.proxy LIKE '%{$keyword}%' )") ;
        }

        if( !$return_data )
        {
            $result =  $builder->countAllResults();
        }
        else
        {
            $builder->limit($per_page, $per_page*$current_page);
            $query = $builder->get();
            $result = $query->getResult();
            $query->freeResult();
        }

        return $result;
    }

    public function get_account_use_proxies($path = ""){
        $configs = get_blocks("block_accounts", false, true);

        $items = [];
        $accounts = [];
        $result = [];

        if( ! empty($configs) ){
            $items = $configs;
            if( count($items) >= 2 ){
                usort($items, function($a, $b) {
                    if( isset($a['position']) &&  isset($b['position']) )
                        return $a['position'] <=> $b['position'];
                });
            }

            foreach ($items as $key => $value) {
                if( !isset($value['data']['can_use_proxy']) ){
                    unset($items[$key]);
                }else{
                    if(!empty( $value['data']['can_use_proxy'] )){
                        foreach ($value['data']['can_use_proxy'] as $k => $account) {
                            $accounts[] = $account->id;
                        }
                    }
                }               
            }
        }

        return $accounts;
    }
}
