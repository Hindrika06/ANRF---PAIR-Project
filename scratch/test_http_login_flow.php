<?php
/**
 * Test actual HTTP login flow for all accounts against running local server http://127.0.0.1:8080/login.php
 */
date_default_timezone_set('Asia/Kolkata');

$testAccounts = [
    'cuk'        => ['user' => 'Idsathyan@cuk.ac.in', 'pass' => 'cuk@admin123'],
    'kannur'     => ['user' => 'anupkesavan@kannuriuniv.ac.in', 'pass' => 'kannur@admin123'],
    'mgu'        => ['user' => 'radhakrishnanek@mgu.ac.in', 'pass' => 'mgu@admin123'],
    'ou'         => ['user' => 'vijjulatha@osmania.ac.in', 'pass' => 'ou@admin123'],
    'svu'        => ['user' => 'balaji.meriga@gmail.com', 'pass' => 'svu@admin123'],
    'uoh'        => ['user' => 'admin@uoh.ac.in', 'pass' => 'uoh@admin123'],
    'yvu'        => ['user' => 'sarma7@yogivemanauniversity.ac.in', 'pass' => 'yvu@admin123'],
    'superadmin' => ['user' => 'superadmin@uoh.ac.in', 'pass' => 'superadmin@123']
];

echo "========================================================\n";
echo "       HTTP REAL LOGIN FLOW AUDIT (PORT 8080)           \n";
echo "========================================================\n\n";

$tmpCookie = __DIR__ . '/cookie_jar.txt';

foreach ($testAccounts as $key => $acc) {
    if (file_exists($tmpCookie)) unlink($tmpCookie);

    $ch = curl_init('http://127.0.0.1:8080/login.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'username' => $acc['user'],
        'password' => $acc['pass']
    ]));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpCookie);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Check raw redirect first

    $rawResp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);

    $loginOk = ($httpCode === 302 && strpos($redirectUrl, 'admin/dashboard.php') !== false);

    if ($loginOk) {
        // Follow redirect to dashboard to verify session persistence
        $ch2 = curl_init($redirectUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_COOKIEFILE, $tmpCookie);
        curl_setopt($ch2, CURLOPT_COOKIEJAR, $tmpCookie);
        $dashHtml = curl_exec($ch2);
        $dashCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);

        $dashOk = ($dashCode === 200 && strpos($dashHtml, 'Dashboard') !== false && strpos($dashHtml, 'login.php') === false);
        
        if ($dashOk) {
            echo sprintf("✅ [PASS] %-12s | HTTP Login 302 -> Dashboard 200 OK | Session Persisted | User: %s\n", strtoupper($key), $acc['user']);
        } else {
            echo sprintf("❌ [FAIL] %-12s | Login 302 OK, but Dashboard rejected session (HTTP %d) | User: %s\n", strtoupper($key), $dashCode, $acc['user']);
        }
    } else {
        echo sprintf("❌ [FAIL] %-12s | HTTP Login Failed (HTTP %d, Redirect: %s) | User: %s\n", strtoupper($key), $httpCode, $redirectUrl ?: 'None', $acc['user']);
    }
}

if (file_exists($tmpCookie)) unlink($tmpCookie);

echo "\n========================================================\n";
