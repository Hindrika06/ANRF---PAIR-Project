<?php
require_once __DIR__ . '/../config.php';
$cols = $pdo->query("DESCRIBE `events`")->fetchAll();
print_r($cols);
