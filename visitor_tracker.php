<?php
/**
 * visitor_tracker.php — ANRF–PAIR Project
 * Centralized visitor tracking logic based on unique IP address.
 */
require_once __DIR__ . '/config.php';

/**
 * Detect the visitor's IP address safely.
 *
 * @return string
 */
function getVisitorIP(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // Check proxy headers if present and valid
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $candidate = trim($ipList[0]);
        if (filter_var($candidate, FILTER_VALIDATE_IP)) {
            $ip = $candidate;
        }
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $candidate = trim($_SERVER['HTTP_CLIENT_IP']);
        if (filter_var($candidate, FILTER_VALIDATE_IP)) {
            $ip = $candidate;
        }
    }

    // Normalize IPv6 localhost
    if ($ip === '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}

/**
 * Tracks unique visitors based on IP address and returns total unique visitor count.
 *
 * @param PDO $pdo
 * @param string|null $overrideIp Optional IP override for testing
 * @return int Total unique visitors count
 */
function trackAndGetUniqueVisitors(PDO $pdo, ?string $overrideIp = null): int {
    static $trackedThisRequest = false;

    $ip = $overrideIp ?? getVisitorIP();
    $now = date('Y-m-d H:i:s');

    // Ensure database table exists
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `website_visitors` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL UNIQUE,
            `first_visit_at` DATETIME NOT NULL,
            `last_visit_at` DATETIME NOT NULL,
            `visit_count` INT NOT NULL DEFAULT 1,
            INDEX `idx_ip` (`ip_address`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (PDOException $e) {
        // Table creation fallback/ignore if table exists or permissions restricted
    }

    // Skip tracking for admin panel pages (/admin/)
    $currentScript = $_SERVER['PHP_SELF'] ?? '';
    $isAdminPage = (strpos($currentScript, '/admin/') !== false);

    if (!$isAdminPage && (!$trackedThisRequest || $overrideIp !== null)) {
        try {
            // Check if IP already exists
            $stmt = $pdo->prepare("SELECT `id`, `visit_count` FROM `website_visitors` WHERE `ip_address` = ? LIMIT 1");
            $stmt->execute([$ip]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Existing visitor: Update last visit time & increment total page hits for this IP (unique count remains unchanged)
                $updateStmt = $pdo->prepare("UPDATE `website_visitors` SET `last_visit_at` = ?, `visit_count` = `visit_count` + 1 WHERE `id` = ?");
                $updateStmt->execute([$now, $row['id']]);
            } else {
                // New unique visitor: Insert record
                $insertStmt = $pdo->prepare("INSERT INTO `website_visitors` (`ip_address`, `first_visit_at`, `last_visit_at`, `visit_count`) 
                    VALUES (?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE `last_visit_at` = VALUES(`last_visit_at`), `visit_count` = `visit_count` + 1");
                $insertStmt->execute([$ip, $now, $now]);
            }
        } catch (PDOException $e) {
            // Log/ignore DB tracking errors silently to prevent breaking page display
        }

        if ($overrideIp === null) {
            $trackedThisRequest = true;
        }
    }

    // Fetch and return total unique visitors (number of distinct IP rows in website_visitors)
    try {
        $countStmt = $pdo->query("SELECT COUNT(*) AS total FROM `website_visitors`");
        $res = $countStmt->fetch(PDO::FETCH_ASSOC);
        return (int)($res['total'] ?? 0);
    } catch (PDOException $e) {
        return 0;
    }
}
