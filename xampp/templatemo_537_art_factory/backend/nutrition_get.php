<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['error' => 'Method not allowed'], 405);
}

$userId = require_auth();

$db = get_db_connection();
$stmt = $db->prepare('SELECT id, age, gender, region, goal, diet, concerns, height_cm, weight_kg, bmi, calories, carbs_g, protein_g, fat_g, plan_json, created_at FROM nutrition_profiles WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$profiles = [];
while ($row = $result->fetch_assoc()) {
    $profiles[] = [
        'id' => intval($row['id']),
        'age' => intval($row['age']),
        'gender' => $row['gender'],
        'region' => $row['region'],
        'goal' => $row['goal'],
        'diet' => $row['diet'],
        'concerns' => $row['concerns'],
        'height_cm' => floatval($row['height_cm']),
        'weight_kg' => floatval($row['weight_kg']),
        'bmi' => $row['bmi'] ? floatval($row['bmi']) : null,
        'calories' => $row['calories'] ? intval($row['calories']) : null,
        'carbs_g' => $row['carbs_g'] ? intval($row['carbs_g']) : null,
        'protein_g' => $row['protein_g'] ? intval($row['protein_g']) : null,
        'fat_g' => $row['fat_g'] ? intval($row['fat_g']) : null,
        'plan_json' => json_decode($row['plan_json'], true),
        'created_at' => $row['created_at']
    ];
}

send_json([
    'message' => 'Nutrition profiles retrieved',
    'count' => count($profiles),
    'profiles' => $profiles
]);
?>
