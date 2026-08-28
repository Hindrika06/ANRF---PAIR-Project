<?php
$baseUrl = 'http://127.0.0.1:8080';

function httpReq($url, $cookies = '', $postData = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    $headerSize = $info['header_size'];
    $headerStr = substr($resp, 0, $headerSize);
    $body = substr($resp, $headerSize);

    preg_match_all('/Set-Cookie:\s*([^;]+)/i', $headerStr, $m);
    $newCookies = implode('; ', $m[1]);

    return [
        'code' => $info['http_code'],
        'headers' => $headerStr,
        'body' => $body,
        'cookies' => $newCookies
    ];
}

$login = httpReq("$baseUrl/login.php", '', ['username' => 'superadmin', 'password' => 'admin123']);
$cookies = $login['cookies'];
preg_match('/Location:\s*admin\/dashboard\.php\?tab_token=([a-f0-9]+)/i', $login['headers'], $m);
$token = $m[1] ?? '';

$res = httpReq("$baseUrl/admin/progress_reports.php?tab_token=$token&prefix=kannur&task_no=19", $cookies);

preg_match('/<select id="task_id_selector".*?<\/select>/s', $res['body'], $mSel);
echo "=== TASK ID SELECTOR HTML ===\n";
echo $mSel[0] ?? 'NOT FOUND';

echo "\n\n=== ALERT NOTICE IN BODY ===\n";
echo (strpos($res['body'], 'No Progress Report entry exists yet') !== false ? 'NOTICE FOUND!' : 'NOTICE NOT FOUND!');
