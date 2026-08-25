<?php
require_once 'config.php';
echo "=== HOMEPAGE BANNERS SCHEMA ===\n";
$cols = $pdo->query("DESCRIBE `homepage_banners`")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "- {$c['Field']} ({$c['Type']})\n";
}

echo "\n=== HOMEPAGE BANNERS CONTENT ===\n";
$rows = $pdo->query("SELECT * FROM `homepage_banners`")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
