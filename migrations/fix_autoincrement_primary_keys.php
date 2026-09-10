<?php
require_once __DIR__ . '/../config.php';

echo "=== MIGRATION: ADD PRIMARY KEY AND AUTO_INCREMENT TO ALL TABLES ===\n\n";

try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $fixedCount = 0;

    foreach ($tables as $t) {
        if (in_array($t, ['users', 'website_visitors', 'approval_requests'], true)) continue;

        $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
        $hasId = false;
        $isPrimary = false;
        $isAutoInc = false;

        foreach ($cols as $c) {
            if ($c['Field'] === 'id') {
                $hasId = true;
                if ($c['Key'] === 'PRI') $isPrimary = true;
                if (strpos($c['Extra'], 'auto_increment') !== false) $isAutoInc = true;
            }
        }

        if ($hasId && !$isAutoInc) {
            try {
                if (!$isPrimary) {
                    // Renumber rows with id = 0 or NULL to sequential unique positive integers
                    $rowsZero = $pdo->query("SELECT id FROM `$t` WHERE id = 0 OR id IS NULL")->fetchAll();
                    if (!empty($rowsZero)) {
                        $maxId = (int)$pdo->query("SELECT MAX(id) FROM `$t` WHERE id > 0")->fetchColumn();
                        $nextId = $maxId + 1;
                        // Use PDO to update each row with id = 0
                        $zeroCount = count($rowsZero);
                        for ($i = 0; $i < $zeroCount; $i++) {
                            $pdo->exec("UPDATE `$t` SET id = " . ($nextId + $i) . " WHERE id = 0 OR id IS NULL LIMIT 1");
                        }
                    }
                    $pdo->exec("ALTER TABLE `$t` ADD PRIMARY KEY (`id`)");
                }
                $pdo->exec("ALTER TABLE `$t` MODIFY COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT");
                echo "[FIXED] Table '$t' -> id is now PRIMARY KEY AUTO_INCREMENT.\n";
                $fixedCount++;
            } catch (Exception $ex) {
                echo "[WARNING] Table '$t' alter failed: " . $ex->getMessage() . "\n";
            }
        }
    }

    echo "\n=== MIGRATION COMPLETED! Fixed $fixedCount tables. ===\n";

} catch (Exception $e) {
    echo "\n[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
