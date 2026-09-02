<?php
require_once 'auth_check.php';
require_once 'role_access.php';
require_once 'config/db.php';
require_once 'config/approval_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$isSuper = isSuperAdmin();
$username = $_SESSION['username'];

$data = getApprovalNotifications($pdo, $username, $isSuper);

echo json_encode([
    'success'       => true,
    'is_super'      => $isSuper,
    'pending_count' => $data['pending_count'],
    'notifications' => $data['notifications']
]);
