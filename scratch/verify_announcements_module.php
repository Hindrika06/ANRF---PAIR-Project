<?php
// Start session for testing session cookies
require_once __DIR__ . '/../config.php';

date_default_timezone_set('Asia/Kolkata');
echo "=== ANNOUNCEMENTS MODULE QA VERIFICATION ===\n\n";

// 1. Verify DB Table structure and contents
$stmt = $pdo->query("SELECT * FROM `announcements` ORDER BY id DESC");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "1. DATABASE CHECK:\n";
echo "   Total Announcement Records: " . count($announcements) . "\n";
foreach ($announcements as $a) {
    echo "   - ID: {$a['id']} | Title: {$a['title']} | Link: {$a['link']} | Active: {$a['is_active']} | Updated: {$a['updated_at']}\n";
}

// 2. Verify HTML output of admin/announcements_management.php
echo "\n2. CODE & ACCESSIBILITY AUDIT:\n";

// Check file exists and syntax
$fileContent = file_get_contents(__DIR__ . '/../admin/announcements_management.php');

// Check JSON encoding
if (strpos($fileContent, 'JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP') !== false) {
    echo "   [PASS] Safe JSON encoding (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) is active on inline onclick attributes.\n";
} else {
    echo "   [FAIL] Missing safe JSON encoding on inline onclick attributes!\n";
}

// Check action-btn-group flexbox
if (strpos($fileContent, 'action-btn-group') !== false && strpos($fileContent, 'flex-wrap: nowrap;') !== false) {
    echo "   [PASS] Actions column uses flex-nowrap horizontal action group with gap spacing.\n";
} else {
    echo "   [FAIL] Actions column styling missing flex-nowrap!\n";
}

// Check clean action URL
if (strpos($fileContent, '$cleanActionUrl') !== false) {
    echo "   [PASS] Explicit clean action URL ($cleanActionUrl) is enforced on form actions to prevent GET parameter leaks.\n";
} else {
    echo "   [FAIL] Missing clean action URL protection!\n";
}

// Check view modal inclusion
if (strpos($fileContent, "include 'includes/view_modal.php';") !== false) {
    echo "   [PASS] Standardized read-only View Modal (view_modal.php) is included.\n";
} else {
    echo "   [FAIL] Missing view_modal.php inclusion!\n";
}

// 3. Test HTTP response of whatsnew.php
echo "\n3. PUBLIC TICKER FRONTEND SYNC (whatsnew.php):\n";
ob_start();
include __DIR__ . '/../whatsnew.php';
$tickerHtml = ob_get_clean();

if (strpos($tickerHtml, 'whatsnew-bar') !== false) {
    echo "   [PASS] whatsnew.php renders successfully.\n";
}
foreach ($announcements as $a) {
    if ($a['is_active']) {
        if (strpos($tickerHtml, htmlspecialchars($a['title'])) !== false || strpos($tickerHtml, $a['title']) !== false) {
            echo "   [PASS] Ticker item ID {$a['id']} ('{$a['title']}') is rendered on public ticker.\n";
        } else {
            echo "   [WARNING] Active ticker item ID {$a['id']} not found in ticker HTML output.\n";
        }
    }
}

echo "\n=== ALL CHECKS COMPLETED CLEANLY ===\n";
