<?php
require 'admin/config/db.php';
$stmt = $pdo->query("SELECT id, username, role, password FROM users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Username: {$row['username']} | Role: {$row['role']} | PassHash: " . substr($row['password'], 0, 15) . "...\n";
}
