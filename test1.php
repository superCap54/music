<?php
$apiToken = '05bd13dd-2621-46af-ac6b-39d83b842c5b';
$teamId = '542169'; // 改为字符串类型
$apiUrl = 'https://us1.make.com/api/v2/connections?teamId=' . urlencode($teamId); // 将teamId作为查询参数

function makeApiRequest($url, $token, $method = 'POST', $data = null) {
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
        CURLOPT_SSL_VERIFYPEER => false
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

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => $response];
}


$connectionData = [
    'name' => 'OneDrive Test',
    'accountName' => 'azure',
    'accountLabel' => 'Microsoft',
    'metadata' => [
        'type' => 'type',
        'value' => 'Hello World!'
    ],
    'teamId' => $teamId,
    'scopesCnt' => 5,
    'scoped' => 1,
    'accountType' => 'oauth',
    'editable' => 1,
    'clientId' =>'f08ecfc7-e5f3-4e24-a0a8-a579c77d392b',
    'clientSecret' => 'qIL8Q~bwieGtcYJu~s8hAYICL3ldNg.wZ2hEPbZs'
];

//$result = makeApiRequest($apiUrl, $apiToken, 'POST', $connectionData);
$result = makeApiRequest($apiUrl, $apiToken, 'GET');
if ($result['code'] === 200) {
    $response = json_decode($result['body'], true);
//    echo "连接创建成功！ID: " . $response['connection']['id'];
    print_r($response);
} else {
    echo "错误代码: {$result['code']}\n";
    echo "错误详情: " . $result['body'];

    // 调试信息
    echo "\n最终请求URL: " . $apiUrl;
    echo "\n请求体数据: " . json_encode($connectionData, JSON_PRETTY_PRINT);
}
?>