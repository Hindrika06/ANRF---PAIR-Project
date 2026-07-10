<?php
require_once __DIR__ . '/backend/config.php';

echo "<h2>Setting up ACTIVAURA Database</h2>";

try {
    // Create connection without database first
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✓ Connected to MySQL server</p>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<p>✓ Database '" . DB_NAME . "' created or already exists</p>";
    } else {
        echo "<p>✗ Error creating database: " . $conn->error . "</p>";
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/backend/schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
            if ($conn->query($statement) === TRUE) {
                echo "<p>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
            } else {
                echo "<p>✗ Error executing statement: " . $conn->error . "</p>";
                echo "<p>Statement: " . $statement . "</p>";
            }
        }
    }
    
    echo "<h3>Database setup completed!</h3>";
    echo "<p>You can now use the login and signup forms.</p>";
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?> 