<?php
require_once __DIR__ . '/db.php';
start_session_if_needed();
if (!isset($_SESSION['user_id'])) {
    send_json(['user' => null]);
}
$db = get_db_connection();
$stmt = $db->prepare('SELECT id, name, email, created_at FROM users WHERE id = ?');
$uid = intval($_SESSION['user_id']);
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
send_json(['user' => $user]);
?>



