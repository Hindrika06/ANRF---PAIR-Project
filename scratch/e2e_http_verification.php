<?php
$baseUrl = 'http://127.0.0.1:8080';

function httpReq($url, $cookies = '', $postData = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookies) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }
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

echo "=== TEST 1: SPOKE ADMIN LOGIN (CUK) ===\n";
$loginResp = httpReq("$baseUrl/login.php", '', [
    'username' => 'Idsathyan@cuk.ac.in',
    'password' => 'admin123'
]);
echo "Login HTTP Code: " . $loginResp['code'] . "\n";
$spokeCookies = $loginResp['cookies'];
preg_match('/Location:\s*admin\/dashboard\.php\?tab_token=([a-f0-9]+)/i', $loginResp['headers'], $tokenMatch);
$tabToken = $tokenMatch[1] ?? '';

echo "=== TEST 2: SPOKE ADMIN PROGRESS REPORTS VIEW & INST. LOCK ===\n";
$prResp = httpReq("$baseUrl/admin/progress_reports.php?tab_token=$tabToken", $spokeCookies);
echo "PR Page Code: " . $prResp['code'] . "\n";
echo "Contains 'Institute Locked' Badge: " . (strpos($prResp['body'], 'Institute Locked') !== false ? 'YES' : 'NO') . "\n";

echo "=== TEST 3: SPOKE ADMIN URL TAMPERING ATTEMPT (?prefix=uoh) ===\n";
$tamperResp = httpReq("$baseUrl/admin/progress_reports.php?tab_token=$tabToken&prefix=uoh", $spokeCookies);
echo "Tamper Request Code: " . $tamperResp['code'] . "\n";
echo "Active Prefix in UI remains CUK: " . (strpos($tamperResp['body'], 'Central University of Karnataka') !== false ? 'YES' : 'NO') . "\n";

echo "=== TEST 4: SPOKE ADMIN CROSS-INSTITUTE PDF EXPORT REJECTION ===\n";
$pdfTamper = httpReq("$baseUrl/admin/export_progress_report_pdf.php?tab_token=$tabToken&id=19&prefix=uoh", $spokeCookies);
echo "PDF Cross-Institute Export Code: " . $pdfTamper['code'] . " (Expected: 403 Forbidden)\n";

echo "=== TEST 5: SUPER ADMIN LOGIN & DUPLICATE TASK ID REPORT INSTANCES ===\n";
$superLogin = httpReq("$baseUrl/login.php", '', [
    'username' => 'superadmin',
    'password' => 'admin123'
]);
$superCookies = $superLogin['cookies'];
preg_match('/Location:\s*admin\/dashboard\.php\?tab_token=([a-f0-9]+)/i', $superLogin['headers'], $superTokenMatch);
$superToken = $superTokenMatch[1] ?? '';

$superPr = httpReq("$baseUrl/admin/progress_reports.php?tab_token=$superToken&prefix=uoh&task_no=2", $superCookies);
echo "Super Admin PR Page Code: " . $superPr['code'] . "\n";
echo "Contains Hub Admin Access Badge: " . (strpos($superPr['body'], 'Hub Admin Access') !== false ? 'YES' : 'NO') . "\n";
echo "Contains Historical Reports Instance Selector: " . (strpos($superPr['body'], 'Historical Reports for Task ID') !== false ? 'YES' : 'NO') . "\n";
echo "Contains Report #19 Option: " . (strpos($superPr['body'], 'Report #19') !== false ? 'YES' : 'NO') . "\n";
echo "Contains Report #13 Option: " . (strpos($superPr['body'], 'Report #13') !== false ? 'YES' : 'NO') . "\n";

echo "=== TEST 6: PUBLIC LIVEBOARD EXCLUSION ===\n";
$indexResp = httpReq("$baseUrl/index.php");
$statsResp = httpReq("$baseUrl/stats.php");
echo "Index Page Code: " . $indexResp['code'] . "\n";
echo "Stats Page Code: " . $statsResp['code'] . "\n";
