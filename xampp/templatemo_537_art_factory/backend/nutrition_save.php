<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['error' => 'Method not allowed'], 405);
}

$userId = require_auth();
$data = json_input();

$profile = [
    'age' => intval($data['age'] ?? 0),
    'gender' => substr(trim($data['gender'] ?? ''), 0, 20),
    'region' => substr(trim($data['region'] ?? ''), 0, 30),
    'goal' => substr(trim($data['goal'] ?? ''), 0, 30),
    'diet' => substr(trim($data['diet'] ?? ''), 0, 20),
    'concerns' => substr(trim($data['concerns'] ?? ''), 0, 255),
    'height_cm' => floatval($data['height'] ?? 0),
    'weight_kg' => floatval($data['weight'] ?? 0),
    'bmi' => isset($data['bmi']) ? floatval($data['bmi']) : null,
    'calories' => isset($data['calories']) ? intval($data['calories']) : null,
    'carbs_g' => isset($data['carbs_g']) ? intval($data['carbs_g']) : null,
    'protein_g' => isset($data['protein_g']) ? intval($data['protein_g']) : null,
    'fat_g' => isset($data['fat_g']) ? intval($data['fat_g']) : null,
    'plan_json' => json_encode($data['plan'] ?? [], JSON_UNESCAPED_UNICODE)
];

$db = get_db_connection();
$stmt = $db->prepare('INSERT INTO nutrition_profiles
    (user_id, age, gender, region, goal, diet, concerns, height_cm, weight_kg, bmi, calories, carbs_g, protein_g, fat_g, plan_json)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

$stmt->bind_param(
    'iisssssdddiiiis',
    $userId,
    $profile['age'],
    $profile['gender'],
    $profile['region'],
    $profile['goal'],
    $profile['diet'],
    $profile['concerns'],
    $profile['height_cm'],
    $profile['weight_kg'],
    $profile['bmi'],
    $profile['calories'],
    $profile['carbs_g'],
    $profile['protein_g'],
    $profile['fat_g'],
    $profile['plan_json']
);

if (!$stmt->execute()) {
    send_json(['error' => 'Failed to save nutrition profile'], 500);
}

send_json(['message' => 'Nutrition profile saved', 'id' => $stmt->insert_id]);
?>


