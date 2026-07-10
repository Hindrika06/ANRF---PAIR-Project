<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['error' => 'Method not allowed'], 405);
}

$data = json_input();
$name = trim($data['name'] ?? '');
$email = strtolower(trim($data['email'] ?? ''));
$password = $data['password'] ?? '';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    send_json(['error' => 'Invalid input'], 400);
}

$db = get_db_connection();

// Check if email exists
$stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    send_json(['error' => 'Email already registered'], 409);
}
$stmt->close();

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $db->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $name, $email, $hash);
if (!$stmt->execute()) {
    send_json(['error' => 'Signup failed'], 500);
}
$userId = $stmt->insert_id;
$stmt->close();

start_session_if_needed();
$_SESSION['user_id'] = $userId;

send_json(['message' => 'Signup successful', 'user' => ['id' => $userId, 'name' => $name, 'email' => $email]]);
?>



