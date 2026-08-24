<?php
require_once __DIR__ . '/../config.php';

echo "--- BEFORE UPDATE ---\n";
$stmt = $pdo->query("SELECT id, full_name, phone, designation FROM team");
$before = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($before);

echo "\n--- EXECUTING UPDATE ---\n";
$updateStmt = $pdo->prepare("UPDATE team SET phone = '040-23132316' WHERE full_name LIKE '%Bramanandam Manavathi%' OR id = 1");
$updateStmt->execute();
echo "Rows affected: " . $updateStmt->rowCount() . "\n";

echo "\n--- AFTER UPDATE ---\n";
$stmt = $pdo->query("SELECT id, full_name, phone, designation FROM team");
$after = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($after);
