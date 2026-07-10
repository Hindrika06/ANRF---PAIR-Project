<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['error' => 'Method not allowed'], 405);
}

$userId = require_auth();

$db = get_db_connection();
$stmt = $db->prepare('SELECT id, tasks_json, progress_percent, created_at FROM task_logs WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = [
        'id' => intval($row['id']),
        'tasks_json' => json_decode($row['tasks_json'], true),
        'progress_percent' => intval($row['progress_percent']),
        'created_at' => $row['created_at']
    ];
}

send_json([
    'message' => 'Task logs retrieved',
    'count' => count($logs),
    'logs' => $logs
]);
?>
