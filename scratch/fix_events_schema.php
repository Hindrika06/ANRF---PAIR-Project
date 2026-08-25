<?php
require_once __DIR__ . '/../config.php';
try {
    $pdo->exec("ALTER TABLE `events` MODIFY `created_by` VARCHAR(255) NOT NULL DEFAULT 'admin'");
    echo "Successfully updated events.created_by column default!\n";
} catch (Exception $e) {
    echo "Schema update error: " . $e->getMessage() . "\n";
}
