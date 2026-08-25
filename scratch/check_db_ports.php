<?php
echo "=== TESTING MYSQL PORT 3307 ===\n";
try {
    $pdo3307 = new PDO("mysql:host=127.0.0.1;port=3307;dbname=anrf;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $rows3307 = $pdo3307->query("SELECT id, username, password FROM users WHERE username = 'Idsathyan@cuk.ac.in'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Port 3307 User count: " . count($rows3307) . "\n";
    if (!empty($rows3307)) {
        echo "Port 3307 CUK Password Hash: " . substr($rows3307[0]['password'], 0, 15) . "...\n";
        echo "Port 3307 Verify cuk@admin123: " . (password_verify('cuk@admin123', $rows3307[0]['password']) ? "YES" : "NO") . "\n";
    }
} catch (Exception $e) {
    echo "Port 3307 Error: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING MYSQL PORT 3306 ===\n";
try {
    $pdo3306 = new PDO("mysql:host=127.0.0.1;port=3306;dbname=anrf;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $rows3306 = $pdo3306->query("SELECT id, username, password FROM users WHERE username = 'Idsathyan@cuk.ac.in'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Port 3306 User count: " . count($rows3306) . "\n";
    if (!empty($rows3306)) {
        echo "Port 3306 CUK Password Hash: " . substr($rows3306[0]['password'], 0, 15) . "...\n";
        echo "Port 3306 Verify cuk@admin123: " . (password_verify('cuk@admin123', $rows3306[0]['password']) ? "YES" : "NO") . "\n";
    }
} catch (Exception $e) {
    echo "Port 3306 Error: " . $e->getMessage() . "\n";
}
