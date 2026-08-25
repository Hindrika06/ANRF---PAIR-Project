<?php
/**
 * Real Manual E2E Session Verification Script
 * Simulates complete user session over local Apache HTTP web server:
 * Login -> Tab Token -> Add Banner -> Verify Table -> Edit Banner -> Verify Update -> POST Delete -> Verify Removal -> Public Homepage.
 */

$baseUrl = 'http://localhost/ANRF---PAIR-Project/';
$cookieFile = __DIR__ . '/e2e_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

function makeHttpRequest($url, $postData = null) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        $hasFile = false;
        if (is_array($postData)) {
            foreach ($postData as $v) {
                if ($v instanceof CURLFile) { $hasFile = true; break; }
            }
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($hasFile || !is_array($postData)) ? $postData : http_build_query($postData));
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    return ['code' => $httpCode, 'headers' => $headers, 'body' => $body];
}

echo "=======================================================================\n";
echo "REAL MANUAL E2E HTTP USER FLOW VERIFICATION\n";
echo "=======================================================================\n\n";

// STEP 1: LOGIN
echo "1. Performing user login as 'superadmin' / 'admin123'...\n";
$loginRes = makeHttpRequest($baseUrl . 'login.php', [
    'username' => 'superadmin',
    'password' => 'admin123'
]);

echo "   Response Code: " . $loginRes['code'] . "\n";
$tabToken = '';
if (preg_match('/tab_token=([a-f0-9]+)/i', $loginRes['headers'], $matches)) {
    $tabToken = $matches[1];
    echo "   [PASS] Login successful! Extracted session tab_token: $tabToken\n";
} else {
    echo "   [FAIL] Login failed or tab_token missing.\n";
    exit(1);
}

// STEP 2: LOAD BANNER MANAGEMENT PAGE
echo "\n2. Navigating to Banner Management page (?prefix=uoh)...\n";
$bannerUrl = $baseUrl . "admin/banner_management.php?prefix=uoh&tab_token=" . urlencode($tabToken);
$pageRes = makeHttpRequest($bannerUrl);
echo "   HTTP Status: " . $pageRes['code'] . "\n";

// Extract CSRF Token
$csrfToken = '';
if (preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/i', $pageRes['body'], $cMatches)) {
    $csrfToken = $cMatches[1];
    echo "   [PASS] Page loaded cleanly. CSRF Token extracted: $csrfToken\n";
} else {
    echo "   [FAIL] Could not find CSRF token on banner_management.php\n";
    exit(1);
}

// STEP 3: CREATE BANNER VIA POST WITH IMAGE ATTACHMENT
echo "\n3. Creating temporary manual test banner via POST (with image file attachment)...\n";
$dummyImgPath = __DIR__ . '/../1.png';
$cFile = new CURLFile($dummyImgPath, 'image/png', '1.png');

$createRes = makeHttpRequest($bannerUrl, [
    'csrf_token' => $csrfToken,
    'title' => 'MANUAL_HTTP_E2E_BANNER',
    'short_description' => 'Created via manual cURL E2E verification',
    'target_url' => 'https://uoh.ac.in/test',
    'institute_prefix' => 'uoh',
    'start_datetime' => date('Y-m-d\TH:i', strtotime('-1 hour')),
    'end_datetime' => date('Y-m-d\TH:i', strtotime('+10 days')),
    'display_order' => 5,
    'status' => 'Active',
    'image' => $cFile
]);
echo "   Create Form Submission HTTP Status: " . $createRes['code'] . "\n";
if (preg_match('/<div class="alert alert-danger[^>]*>(.*?)<\/div>/s', $createRes['body'], $errMatches)) {
    echo "   [DEBUG] Backend Error Alert: " . strip_tags($errMatches[1]) . "\n";
}

// STEP 4: VERIFY CREATION IN LISTING
echo "\n4. Verifying created banner appears in admin table listing...\n";
$listRes = makeHttpRequest($bannerUrl);
if (strpos($listRes['body'], 'MANUAL_HTTP_E2E_BANNER') !== false) {
    echo "   [PASS] Created record 'MANUAL_HTTP_E2E_BANNER' is present in table!\n";
} else {
    echo "   [FAIL] Created record not found in table!\n";
    echo "--- BODY DUMP START ---\n";
    echo substr(strip_tags($createRes['body']), 0, 1000);
    echo "\n--- BODY DUMP END ---\n";
    exit(1);
}

// Extract Edit ID
$editId = 0;
$decodedHtml = htmlspecialchars_decode($listRes['body']);
if (preg_match_all('/openEditModal\((\{.*?\})\)/s', $decodedHtml, $allMatches)) {
    foreach ($allMatches[1] as $jsonStr) {
        $data = json_decode($jsonStr, true);
        if ($data && !empty($data['title']) && strpos($data['title'], 'MANUAL_HTTP_E2E_BANNER') !== false) {
            $editId = (int)$data['id'];
            echo "   [PASS] Found banner record ID: #$editId\n";
            break;
        }
    }
}

if (!$editId) {
    echo "   [FAIL] Could not extract banner ID for edit!\n";
    exit(1);
}

// STEP 5: EDIT BANNER RECORD
echo "\n5. Submitting Edit update POST for record #$editId...\n";
$editRes = makeHttpRequest($bannerUrl, [
    'csrf_token' => $csrfToken,
    'edit_id' => $editId,
    'title' => 'MANUAL_HTTP_E2E_BANNER_UPDATED',
    'short_description' => 'Updated title via manual cURL E2E test',
    'target_url' => 'https://uoh.ac.in/updated',
    'institute_prefix' => 'uoh',
    'start_datetime' => date('Y-m-d\TH:i', strtotime('-1 hour')),
    'end_datetime' => date('Y-m-d\TH:i', strtotime('+10 days')),
    'display_order' => 1,
    'status' => 'Active'
]);
echo "   Edit Form Submission HTTP Status: " . $editRes['code'] . "\n";

// STEP 6: VERIFY UPDATE IN LISTING
echo "\n6. Verifying updated title in admin table listing...\n";
$reListRes = makeHttpRequest($bannerUrl);
if (strpos($reListRes['body'], 'MANUAL_HTTP_E2E_BANNER_UPDATED') !== false) {
    echo "   [PASS] Updated title 'MANUAL_HTTP_E2E_BANNER_UPDATED' is successfully displayed in table!\n";
} else {
    echo "   [FAIL] Updated title not found in table!\n";
    exit(1);
}

// STEP 7: POST DELETE WITH CSRF PROTECTION
echo "\n7. Executing POST Delete for record #$editId with CSRF token...\n";
$deleteRes = makeHttpRequest($bannerUrl, [
    'csrf_token' => $csrfToken,
    'action' => 'delete',
    'delete_id' => $editId
]);
echo "   POST Delete Submission HTTP Status: " . $deleteRes['code'] . "\n";

// STEP 8: VERIFY REMOVAL
echo "\n8. Verifying record #$editId has been removed from table...\n";
$afterDelRes = makeHttpRequest($bannerUrl);
if (strpos($afterDelRes['body'], 'MANUAL_HTTP_E2E_BANNER_UPDATED') === false) {
    echo "   [PASS] Record #$editId successfully deleted and removed from table listing!\n";
} else {
    echo "   [FAIL] Record still exists in table after delete!\n";
    exit(1);
}

// STEP 9: PUBLIC HOMEPAGE VERIFICATION
echo "\n9. Testing Public Homepage rendering (index.php?prefix=uoh)...\n";
$homeRes = makeHttpRequest($baseUrl . 'index.php?prefix=uoh');
if ($homeRes['code'] === 200) {
    echo "   [PASS] Public Homepage loaded cleanly with HTTP 200!\n";
} else {
    echo "   [FAIL] Public Homepage failed with HTTP " . $homeRes['code'] . "\n";
    exit(1);
}

// Clean cookie file
if (file_exists($cookieFile)) unlink($cookieFile);

echo "\n=======================================================================\n";
echo "MANUAL E2E HTTP USER FLOW COMPLETED WITH 100% SUCCESS!\n";
echo "=======================================================================\n";
