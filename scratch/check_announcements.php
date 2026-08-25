<?php
require_once __DIR__ . '/../config.php';

$stmt = $pdo->query("SELECT * FROM `announcements` ORDER BY id DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total announcements in DB: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Title: {$r['title']} | Link: {$r['link']} | Active: {$r['is_active']} | Created: {$r['created_at']}\n";
}
