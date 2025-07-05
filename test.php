<?php
$apiToken = '05bd13dd-2621-46af-ac6b-39d83b842c5b';
$scenarioId = 4112338;
$newRssUrl = 'https://rss.nytimes.com/services/xml/rss/nyt/Americas.xml';
$apiBaseUrl = 'https://us1.make.com/api/v2'; // 改为您的实际区域

// 1. 获取场景蓝图（带错误重试）
function makeApiRequest($url, $token, $method = 'GET', $data = null) {
    $ch = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Token ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false // 生产环境应配置证书
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
    } elseif ($method !== 'GET') {
        $options[CURLOPT_CUSTOMREQUEST] = $method;
    }

    if ($data) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }

    curl_setopt_array($ch, $options);

    // 重试逻辑
    $maxRetries = 3;
    for ($i = 0; $i < $maxRetries; $i++) {
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode > 0) break;
        if ($i < $maxRetries - 1) sleep(2); // 等待2秒后重试
    }

    if ($httpCode === 0) {
        $error = curl_error($ch);
        curl_close($ch);
        die("API请求失败: " . $error . "\n检查网络连接和URL有效性");
    }

    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

// 2. 获取蓝图
//$blueprintResponse = makeApiRequest("$apiBaseUrl/scenarios/$scenarioId/blueprint", $apiToken);
//if ($blueprintResponse['code'] !== 200) {
//    die("获取蓝图失败: HTTP {$blueprintResponse['code']}\n" . $blueprintResponse['body']);
//}
//
//$blueprint = json_decode($blueprintResponse['body'], true)['response']['blueprint'] ?? null;
//if (!$blueprint) die("蓝图数据解析失败");
//// 3. 更新RSS模块URL
//$updated = false;
//foreach ($blueprint['flow'] as &$module) {
//    if (isset($module['mapper']['url'])) {
//        $module['mapper']['url'] = $newRssUrl;
//        $module['mapper']['maxResults'] = 1;
//        $updated = true;
//        break;
//    }
//}
//
//if (!$updated) die("未找到可配置URL的RSS模块");
//print_r($blueprint);
//// 4. 提交更新
//$updateResponse = makeApiRequest(
//    "$apiBaseUrl/scenarios/$scenarioId",
//    $apiToken,
//    'PATCH',
//    ['blueprint' => json_encode($blueprint)]
//);
//
//if ($updateResponse['code'] !== 200) {
//    die("更新场景失败: HTTP {$updateResponse['code']}\n" . $updateResponse['body']);
//}
//



//下面是执行场景的代码

$executionResponse = makeApiRequest(
    "$apiBaseUrl/scenarios/$scenarioId/run",
    $apiToken,
    'POST',
    ['responsive' => true]
);

if ($executionResponse['code'] === 200) {
    $result = json_decode($executionResponse['body'], true);
    echo "执行Make成功！\n";
    var_dump($result);
} else {
    echo "执行失败: HTTP {$executionResponse['code']}\n" . $executionResponse['body'];
}
?>