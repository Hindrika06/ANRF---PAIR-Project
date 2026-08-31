<?php
$url = 'http://127.0.0.1:8000/login.php';

$testAccounts = [
    ['username' => 'Idsathyan@cuk.ac.in', 'password' => 'cuk@admin123'],
    ['username' => 'superadmin@uoh.ac.in', 'password' => 'superadmin@123']
];

foreach ($testAccounts as $account) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($account));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (strpos($response, 'Location: admin/dashboard.php') !== false || $httpCode === 302) {
        echo "[OK] HTTP Login Success for {$account['username']} -> Redirected to dashboard!\n";
    } else {
        echo "[FAIL] HTTP Login Failed for {$account['username']}!\n";
        echo "Response snippet:\n" . substr($response, 0, 300) . "\n";
    }
}
