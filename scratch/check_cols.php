<?php
require_once 'config.php';
print_r($pdo->query('DESCRIBE cuk_publications')->fetchAll(PDO::FETCH_COLUMN));
