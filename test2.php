<?php
// 代理设置
$proxy = '59.37.173.75:35343';
$proxyAuth = 'biechao:biechao';
$testUrl = 'https://www.google.com';

// 尝试不同的代理类型
$proxyTypes = [
    'HTTPS' => CURLPROXY_HTTPS,
    'SOCKS5' => CURLPROXY_SOCKS5,
    'HTTP' => CURLPROXY_HTTP
];

foreach ($proxyTypes as $typeName => $proxyType) {
    echo "测试 {$typeName} 代理...\n";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $testUrl,
        CURLOPT_PROXY => $proxy,
        CURLOPT_PROXYTYPE => $proxyType,
        CURLOPT_PROXYUSERPWD => $proxyAuth,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_VERBOSE => true, // 启用详细输出
        CURLOPT_USERAGENT => 'Mozilla/5.0'
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

    if ($error) {
        echo "❌ {$typeName} 代理失败: {$error}\n";
        echo "耗时: ".round($totalTime,2)."秒\n\n";
    } else {
        echo "✅ {$typeName} 代理成功!\n";
        echo "状态码: {$httpCode}\n";
        echo "耗时: ".round($totalTime,2)."秒\n";
        break; // 成功则停止测试
    }

    curl_close($ch);
    sleep(1); // 每次测试间隔1秒
}

// 如果所有类型都失败
if (!empty($error)) {
    echo "\n⚠️ 所有代理类型测试均失败，建议：\n";
    echo "1. 检查代理服务器是否在线\n";
    echo "2. 确认代理认证信息正确\n";
    echo "3. 尝试更换测试URL为http网站\n";
    echo "4. 联系代理提供商确认支持类型\n";

    // 尝试HTTP网站测试
    echo "\n尝试HTTP网站测试...\n";
    $ch = curl_init('http://httpbin.org/ip');
    curl_setopt_array($ch, [
        CURLOPT_PROXY => $proxy,
        CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
        CURLOPT_PROXYUSERPWD => $proxyAuth,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5
    ]);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "❌ HTTP测试也失败: ".curl_error($ch)."\n";
    } else {
        echo "✅ HTTP测试成功:\n".$response."\n";
    }
    curl_close($ch);
}
?>