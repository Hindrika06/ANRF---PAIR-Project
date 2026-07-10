<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

try {
    $user = require_auth();
    
    // Get user details
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT id, name, email, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $user);
    $stmt->execute();
    $stmt->bind_result($id, $name, $email, $created_at);
    
    if (!$stmt->fetch()) {
        send_json(['error' => 'User not found'], 404);
    }
    $stmt->close();
    
    send_json([
        'authenticated' => true,
        'user' => [
            'id' => intval($id),
            'name' => $name,
            'email' => $email,
            'created_at' => $created_at
        ]
    ]);
    
} catch (Exception $e) {
    send_json(['error' => 'Authentication required'], 401);
}
?>
