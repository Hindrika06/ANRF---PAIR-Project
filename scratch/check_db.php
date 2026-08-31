<?php
$passwords = [
    '', 'root', 'admin', 'password', '123456', '1234', 'mysql', 'anrf',
    'Root123!', 'Admin123!', 'root123', 'admin123', 'anrf123', 'anrf_pass',
    'Xampp123!', 'MySQL80', 'mysql80'
];

foreach ($passwords as $pass) {
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "FOUND WORKING CREDENTIALS! Port: 3306, User: root, Pass: '$pass'\n";
        $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Databases: " . implode(', ', $dbs) . "\n";
        exit(0);
    } catch (PDOException $e) {
        echo "Pass '$pass': " . $e->getMessage() . "\n";
    }
}
echo "FINISHED TESTING PASSWORDS.\n";
