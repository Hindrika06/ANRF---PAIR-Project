<?php
require_once __DIR__ . '/../admin/config/db.php';
echo "--- DESCRIBE homepage_banners ---\n";
$stmt = $pdo->query("DESCRIBE homepage_banners");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- ROWS in homepage_banners ---\n";
$stmt2 = $pdo->query("SELECT * FROM homepage_banners");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
