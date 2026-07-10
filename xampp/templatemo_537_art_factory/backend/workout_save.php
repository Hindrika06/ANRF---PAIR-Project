<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['error' => 'Method not allowed'], 405);
}

$userId = require_auth();
$data = json_input();

$goal = substr(trim($data['goal'] ?? ''), 0, 40);
$level = substr(trim($data['level'] ?? ''), 0, 20);
$minutes = intval($data['minutes'] ?? 0);
$equipment = substr(trim($data['equipment'] ?? ''), 0, 40);
$bodypart = substr(trim($data['bodypart'] ?? ''), 0, 40);
$plan_json = json_encode($data['plan'] ?? [], JSON_UNESCAPED_UNICODE);

$db = get_db_connection();
$stmt = $db->prepare('INSERT INTO workout_plans (user_id, goal, level, minutes, equipment, bodypart, plan_json) VALUES (?,?,?,?,?,?,?)');
$stmt->bind_param('ississs', $userId, $goal, $level, $minutes, $equipment, $bodypart, $plan_json);
if (!$stmt->execute()) {
    send_json(['error' => 'Failed to save workout plan'], 500);
}
send_json(['message' => 'Workout plan saved', 'id' => $stmt->insert_id]);
?>



