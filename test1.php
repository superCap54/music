<?php
// 新建 proxy_test.php 文件测试代理
$proxy = '59.37.173.75:35116';
$proxyAuth = 'biechao:biechao';
$testUrl = 'https://www.google.com';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $testUrl,
    CURLOPT_PROXY => $proxy,
    CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
    CURLOPT_PROXYUSERPWD => $proxyAuth,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_RETURNTRANSFER => true
]);
$response = curl_exec($ch);

if(curl_errno($ch)) {
    die("代理测试失败: " . curl_error($ch));
} else {
    echo "代理连接成功！响应长度: " . strlen($response);
}
curl_close($ch);
?>