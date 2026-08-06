<?php
require_once __DIR__ . '/../config.php';
$stmt = $pdo->query("SELECT * FROM approval_requests ORDER BY requested_at DESC");
$reqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total requests: " . count($reqs) . "\n";
print_r($reqs);
