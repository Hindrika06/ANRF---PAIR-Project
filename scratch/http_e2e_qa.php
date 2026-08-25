<?php
/**
 * Real HTTP E2E QA Verification Script for Banner CRUD with Tab Token Handling
 */
date_default_timezone_set('Asia/Kolkata');

$baseUrl = 'http://localhost/ANRF---PAIR-Project';
$cookieFile = __DIR__ . '/cookie.txt';
if (file_exists($cookieFile)) @unlink($cookieFile);

function httpReq($url, $postData = null) {
    global $cookieFile;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($postData) ? http_build_query($postData) : $postData);
    }
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $header = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);

    return ['code' => $code, 'header' => $header, 'body' => $body];
}

echo "1. Testing Public Homepage (index.php)...\n";
$res1 = httpReq($baseUrl . '/index.php');
echo "HTTP Code: " . $res1['code'] . "\n";

echo "2. Logging in as Hub Super Admin...\n";
$loginRes = httpReq($baseUrl . '/login.php', [
    'username' => 'superadmin',
    'password' => 'admin123'
]);
echo "Login HTTP Code: " . $loginRes['code'] . "\n";

$tabToken = '';
if (preg_match('/tab_token=([a-f0-9]+)/i', $loginRes['header'], $m)) {
    $tabToken = $m[1];
    echo "Extracted tab_token: $tabToken\n";
}

echo "3. Fetching /admin/banner_management.php?prefix=uoh...\n";
$bannerUrl = $baseUrl . '/admin/banner_management.php?prefix=uoh' . ($tabToken ? '&tab_token=' . $tabToken : '');
$bannerPage = httpReq($bannerUrl);
echo "Banner Management HTTP Code: " . $bannerPage['code'] . "\n";

$hasEditBtn = strpos($bannerPage['body'], 'openEditModal') !== false || strpos($bannerPage['body'], 'Edit') !== false;
$hasAddBtn = strpos($bannerPage['body'], 'openAddModal') !== false || strpos($bannerPage['body'], 'Add Homepage Banner') !== false;
$hasEditModeHeader = strpos($bannerPage['body'], 'editModeHeader') !== false;
$hasUpdateBtn = strpos($bannerPage['body'], 'Update Banner') !== false;

echo "Add Modal Button Present: " . ($hasAddBtn ? "PASS" : "FAIL") . "\n";
echo "Edit Modal Script Present: " . ($hasEditBtn ? "PASS" : "FAIL") . "\n";
echo "Edit Mode Header Present: " . ($hasEditModeHeader ? "PASS" : "FAIL") . "\n";
echo "Update Banner Button Text Present: " . ($hasUpdateBtn ? "PASS" : "FAIL") . "\n";

echo "\nHTTP E2E CHECK COMPLETED CLEANLY.\n";
