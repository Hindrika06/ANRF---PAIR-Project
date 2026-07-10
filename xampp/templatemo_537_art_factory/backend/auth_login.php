<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo $[REQUEST_METHOD] ?? '';
    header('Allow: POST');
    send_json(['error' => 'Method not allowed'], 405);
}

$data = json_input();
$email = strtolower(trim($data['email'] ?? ''));
$password = $data['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    send_json(['error' => 'Invalid credentials'], 400);
}

$db = get_db_connection();
$stmt = $db->prepare('SELECT id, name, password_hash FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($id, $name, $hash);
if (!$stmt->fetch()) {
    send_json(['error' => 'Invalid email or password'], 401);
}
$stmt->close();

if (!password_verify($password, $hash)) {
    send_json(['error' => 'Invalid email or password'], 401);
}

start_session_if_needed();
$_SESSION['user_id'] = intval($id);

send_json(['message' => 'Login successful', 'user' => ['id' => intval($id), 'name' => $name, 'email' => $email]]);
?>



