<?php
require_once __DIR__ . '/../config.php';
$stmt = $pdo->prepare("UPDATE approval_requests SET status = 'Pending', approved_by = NULL, approved_at = NULL WHERE id = 13");
$stmt->execute();
echo "Updated request #13 status to Pending.\n";
