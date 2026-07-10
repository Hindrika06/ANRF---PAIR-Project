<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['error' => 'Method not allowed'], 405);
}

$userId = require_auth();

$db = get_db_connection();
$stmt = $db->prepare('SELECT id, goal, level, minutes, equipment, bodypart, plan_json, created_at FROM workout_plans WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$plans = [];
while ($row = $result->fetch_assoc()) {
    $plans[] = [
        'id' => intval($row['id']),
        'goal' => $row['goal'],
        'level' => $row['level'],
        'minutes' => intval($row['minutes']),
        'equipment' => $row['equipment'],
        'bodypart' => $row['bodypart'],
        'plan_json' => json_decode($row['plan_json'], true),
        'created_at' => $row['created_at']
    ];
}

send_json([
    'message' => 'Workout plans retrieved',
    'count' => count($plans),
    'plans' => $plans
]);
?>
