<?php
require_once __DIR__ . '/../config.php';
$stmt = $pdo->query("SELECT * FROM homepage_banners WHERE status='Active' AND (institute_prefix='all' OR institute_prefix='' OR institute_prefix IS NULL)");
$rows = $stmt->fetchAll();
echo "Active Global Banners Count: " . count($rows) . "\n";
