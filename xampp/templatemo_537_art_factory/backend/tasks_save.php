<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['error' => 'Method not allowed'], 405);
}

$userId = require_auth();
$data = json_input();

// Expecting: tasks array of { key, title, completed }
$tasks = is_array($data['tasks'] ?? null) ? $data['tasks'] : [];
$summary_percent = intval($data['progress_percent'] ?? 0);

$db = get_db_connection();
$stmt = $db->prepare('INSERT INTO task_logs (user_id, tasks_json, progress_percent) VALUES (?,?,?)');
$tasks_json = json_encode($tasks, JSON_UNESCAPED_UNICODE);
$stmt->bind_param('isi', $userId, $tasks_json, $summary_percent);
if (!$stmt->execute()) {
    send_json(['error' => 'Failed to save task log'], 500);
}
send_json(['message' => 'Task log saved', 'id' => $stmt->insert_id]);
?>



