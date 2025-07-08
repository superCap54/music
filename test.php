<?php
//$a = [
//    ['name' => 'rss', 'type' => 'text', 'value' => ''],
//    [
//        'name' => 'gender',
//        'type' => 'select',
//        'value' => 'female',
//        'options' => [
//            ['value' => 'male', 'label' => 'Male'],
//            ['value' => 'female', 'label' => 'Female']  // 注意：这里两个选项value相同
//        ]
//    ]
//];
//echo json_encode($a);exit;
$apiToken = '05bd13dd-2621-46af-ac6b-39d83b842c5b';
$scenarioId = 4113517;
$newRssUrl = 'https://rss.nytimes.com/services/xml/rss/nyt/Americas.xml';
$apiBaseUrl = 'https://us1.make.com/api/v2'; // 改为您的实际区域


/*修改 Youtube账号和信息
$a = '{"id":7,"mapper":{"data":"{{2.data}}","title":"111111","fileName":"{{2.filename}}","categoryId":"28","description":"1111111","privacyStatus":"public","containsSyntheticMedia":false,"selfDeclaredMadeForKids":false},"module":"youtube:uploadVideo","version":4,"metadata":{"expect":[{"type":"hidden"},{"name":"title","type":"text","label":"Title","required":true,"validate":{"max":100}},{"name":"fileName","type":"filename","label":"File Name","required":true},{"name":"data","type":"buffer","label":"Data","required":true},{"name":"categoryId","type":"select","label":"Video Category","required":true},{"name":"privacyStatus","type":"select","label":"Privacy Status and Scheduling","required":true,"validate":{"enum":["private","unlisted","public"]}},{"name":"description","type":"text","label":"Description","validate":{"max":5000}},{"name":"selfDeclaredMadeForKids","type":"boolean","label":"**The video is made for kids**","required":true},{"name":"containsSyntheticMedia","type":"boolean","label":"**The video contains altered\/synthetic media**","required":true},{"name":"tags","spec":{"name":"value","type":"text","label":"Tag","required":true,"validate":{"max":500}},"type":"array","label":"Tags"},{"name":"recordingDate","type":"date","label":"Recording Date"},{"name":"license","type":"select","label":"License","validate":{"enum":["youtube","creativeCommon"]}},{"name":"embeddable","type":"boolean","label":"Allow Embedding"},{"name":"notifySubscribers","type":"boolean","label":"Notify Subscribers"},{"type":"hidden"},{"name":"title","type":"text","label":"Title","required":true,"validate":{"max":100}},{"name":"fileName","type":"filename","label":"File Name","required":true},{"name":"data","type":"buffer","label":"Data","required":true},{"name":"categoryId","type":"select","label":"Video Category","required":true},{"name":"privacyStatus","type":"select","label":"Privacy Status and Scheduling","required":true,"validate":{"enum":["private","unlisted","public"]}},{"name":"description","type":"text","label":"Description","validate":{"max":5000}},{"name":"selfDeclaredMadeForKids","type":"boolean","label":"**The video is made for kids**","required":true},{"name":"containsSyntheticMedia","type":"boolean","label":"**The video contains altered\/synthetic media**","required":true},{"name":"tags","spec":{"name":"value","type":"text","label":"Tag","required":true,"validate":{"max":500}},"type":"array","label":"Tags"},{"name":"recordingDate","type":"date","label":"Recording Date"},{"name":"license","type":"select","label":"License","validate":{"enum":["youtube","creativeCommon"]}},{"name":"embeddable","type":"boolean","label":"Allow Embedding"},{"name":"notifySubscribers","type":"boolean","label":"Notify Subscribers"}],"restore":{"expect":{"tags":{"mode":"chose"},"license":{"mode":"chose","label":"Empty"},"categoryId":{"mode":"chose","label":"Science & Technology"},"embeddable":{"mode":"chose"},"privacyStatus":{"mode":"chose","label":"Public"},"notifySubscribers":{"mode":"chose"},"containsSyntheticMedia":{"mode":"chose"},"selfDeclaredMadeForKids":{"mode":"chose"}},"parameters":{"__IMTCONN__":{"data":{"scoped":"true","connection":"youtube"},"label":"ChronicleCoresu (Chau Dong )"}}},"designer":{"x":20,"y":-164},"parameters":[{"name":"__IMTCONN__","type":"account:youtube","label":"Connection","required":true}]},"parameters":{"__IMTCONN__":4332016}}';
$aArr = json_decode($a,true);
$mapper = $aArr['mapper'];
$parametersNum = $aArr['parameters'];
var_dump($aArr);exit;
*/

/*修改 OneDrive账号和信息
$oneDrive = '{"id":1,"mapper":{"limit":"10","folder":null,"select":"my","select1":"no","itemType":"file"},"module":"onedrive:searchFilesFolders","version":2,"metadata":{"expect":[{"name":"select","type":"select","label":"Choose your OneDrive location","required":true,"validate":{"enum":["my","share","site","group"]}},{"name":"itemType","type":"select","label":"Choose an Item Type","required":true,"validate":{"enum":["file","folder","both"]}},{"name":"limit","type":"uinteger","label":"Limit"},{"name":"select1","type":"select","label":"Enable to Enter a Drive ID","required":true,"validate":{"enum":["yes","no"]}},{"name":"folder","type":"folder","label":"Folder"},{"name":"search","type":"text","label":"Query Search"},{"name":"select","type":"select","label":"Choose your OneDrive location","required":true,"validate":{"enum":["my","share","site","group"]}},{"name":"itemType","type":"select","label":"Choose an Item Type","required":true,"validate":{"enum":["file","folder","both"]}},{"name":"limit","type":"uinteger","label":"Limit"},{"name":"select1","type":"select","label":"Enable to Enter a Drive ID","required":true,"validate":{"enum":["yes","no"]}},{"name":"folder","type":"folder","label":"Folder"},{"name":"search","type":"text","label":"Query Search"}],"restore":{"expect":{"folder":{"mode":"chose","path":[]},"select":{"label":"My Drive"},"select1":{"label":"No"},"itemType":{"label":"File"}},"parameters":{"__IMTCONN__":{"data":{"scoped":"true","connection":"azure"},"label":"My Microsoft connection (hinghoi tsang)"}}},"designer":{"x":-660,"y":-114},"parameters":[{"name":"__IMTCONN__","type":"account:azure","label":"Connection","required":true}]},"parameters":{"__IMTCONN__":4327326}}';
$oneDriveJson = json_decode($oneDrive,true);
$mapper = $oneDriveJson['mapper'];
$parametersNum = $oneDriveJson['parameters'];
*/

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
$blueprintResponse = makeApiRequest("$apiBaseUrl/scenarios/$scenarioId/blueprint", $apiToken);
if ($blueprintResponse['code'] !== 200) {
    die("获取蓝图失败: HTTP {$blueprintResponse['code']}\n" . $blueprintResponse['body']);
}

$blueprint = json_decode($blueprintResponse['body'], true)['response']['blueprint'] ?? null;
if (!$blueprint) die("蓝图数据解析失败");
// 3. 更新RSS模块URL
$updated = false;

foreach ($blueprint['flow'] as &$module) {
    if ($module['module'] == 'onedrive:searchFilesFolders') {
        $module['mapper'] = $mapper;            //改参数值
        $module['parameters'] = $parametersNum; //改绑定账户
        $updated = true;
        break;
    }
}

if (!$updated) die("11111");
// 4. 提交更新
$updateResponse = makeApiRequest(
    "$apiBaseUrl/scenarios/$scenarioId",
    $apiToken,
    'PATCH',
    ['blueprint' => json_encode($blueprint)]
);

if ($updateResponse['code'] !== 200) {
    die("更新场景失败: HTTP {$updateResponse['code']}\n" . $updateResponse['body']);
}

print_r(json_decode($updateResponse['body'],true));exit;



//下面是执行场景的代码

//$executionResponse = makeApiRequest(
//    "$apiBaseUrl/scenarios/$scenarioId/run",
//    $apiToken,
//    'POST',
//    ['responsive' => true]
//);
//
//if ($executionResponse['code'] === 200) {
//    $result = json_decode($executionResponse['body'], true);
//    echo "执行Make成功！\n";
//    var_dump($result);
//} else {
//    echo "执行失败: HTTP {$executionResponse['code']}\n" . $executionResponse['body'];
//}
?>