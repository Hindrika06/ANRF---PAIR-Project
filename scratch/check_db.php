<?php
require_once __DIR__ . '/../config.php';

try {
    $stmt = $pdo->query("SELECT id, full_name, email, phone, designation FROM team");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "COUNT: " . count($rows) . "\n";
    foreach ($rows as $row) {
        echo "ID: {$row['id']} | Name: {$row['full_name']} | Phone: {$row['phone']} | Designation: {$row['designation']}\n";
    }
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
