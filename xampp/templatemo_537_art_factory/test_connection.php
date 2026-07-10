<?php
require_once __DIR__ . '/backend/db.php';

echo "<h2>Testing ACTIVAURA Database Connection</h2>";

try {
    $db = get_db_connection();
    echo "<p>✓ Database connection successful!</p>";
    
    // Test if users table exists
    $result = $db->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "<p>✓ Users table exists</p>";
        
        // Count users
        $result = $db->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        echo "<p>✓ Users in database: " . $row['count'] . "</p>";
    } else {
        echo "<p>✗ Users table not found - please run setup_database.php first</p>";
    }
    
    // Test other tables
    $tables = ['nutrition_profiles', 'workout_plans', 'task_logs'];
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p>✓ $table table exists</p>";
        } else {
            echo "<p>✗ $table table not found</p>";
        }
    }
    
    echo "<h3>Backend API Endpoints Test</h3>";
    echo "<p>Available endpoints:</p>";
    echo "<ul>";
    echo "<li><a href='backend/auth_login.php' target='_blank'>Login API</a></li>";
    echo "<li><a href='backend/auth_signup.php' target='_blank'>Signup API</a></li>";
    echo "<li><a href='backend/me.php' target='_blank'>User Info API</a></li>";
    echo "<li><a href='backend/auth_logout.php' target='_blank'>Logout API</a></li>";
    echo "</ul>";
    
    echo "<h3>Next Steps</h3>";
    echo "<p>1. Make sure XAMPP Apache and MySQL are running</p>";
    echo "<p>2. Visit <a href='index.html'>index.html</a> to test the login/signup forms</p>";
    echo "<p>3. Try creating a new account and logging in</p>";
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your XAMPP MySQL service is running and the database configuration in backend/config.php</p>";
}
?>
