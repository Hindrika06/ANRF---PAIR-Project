<?php
require_once __DIR__ . '/backend/db.php';

echo "<h2>Testing ACTIVAURA Authentication Endpoints</h2>";

// Test database connection
try {
    $db = get_db_connection();
    echo "<p>✓ Database connection successful!</p>";
    
    // Check if users table exists and has data
    $result = $db->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✓ Users table exists with {$row['count']} users</p>";
    } else {
        echo "<p>✗ Users table not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p>✗ Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Test backend files exist
$backendFiles = [
    'auth_login.php',
    'auth_signup.php', 
    'auth_logout.php',
    'me.php',
    'config.php',
    'db.php'
];

echo "<h3>Backend Files Check</h3>";
foreach ($backendFiles as $file) {
    $path = __DIR__ . '/backend/' . $file;
    if (file_exists($path)) {
        echo "<p>✓ $file exists</p>";
    } else {
        echo "<p>✗ $file missing</p>";
    }
}

// Test configuration
echo "<h3>Configuration Test</h3>";
try {
    require_once __DIR__ . '/backend/config.php';
    echo "<p>✓ Config loaded successfully</p>";
    echo "<p>Database: " . DB_NAME . "</p>";
    echo "<p>Host: " . DB_HOST . "</p>";
} catch (Exception $e) {
    echo "<p>✗ Config error: " . $e->getMessage() . "</p>";
}

// Test authentication functions
echo "<h3>Authentication Functions Test</h3>";
try {
    // Test json_input function
    if (function_exists('json_input')) {
        echo "<p>✓ json_input function exists</p>";
    } else {
        echo "<p>✗ json_input function missing</p>";
    }
    
    // Test send_json function
    if (function_exists('send_json')) {
        echo "<p>✓ send_json function exists</p>";
    } else {
        echo "<p>✗ send_json function missing</p>";
    }
    
    // Test require_auth function
    if (function_exists('require_auth')) {
        echo "<p>✓ require_auth function exists</p>";
    } else {
        echo "<p>✗ require_auth function missing</p>";
    }
    
} catch (Exception $e) {
    echo "<p>✗ Function test error: " . $e->getMessage() . "</p>";
}

echo "<h3>Manual Testing Instructions</h3>";
echo "<p>1. Make sure XAMPP Apache and MySQL are running</p>";
echo "<p>2. Visit <a href='index.html' target='_blank'>index.html</a> to test the forms</p>";
echo "<p>3. Try creating a new account with the signup form</p>";
echo "<p>4. Try logging in with existing credentials</p>";
echo "<p>5. Check browser console for any JavaScript errors</p>";

echo "<h3>Common Issues & Solutions</h3>";
echo "<ul>";
echo "<li><strong>Network Error:</strong> Check if Apache is running on port 80</li>";
echo "<li><strong>Database Error:</strong> Check if MySQL is running and database exists</li>";
echo "<li><strong>JavaScript Error:</strong> Check browser console for syntax errors</li>";
echo "<li><strong>Modal Not Opening:</strong> Check if Bootstrap and jQuery are loaded</li>";
echo "</ul>";

echo "<h3>Quick Fixes</h3>";
echo "<p>If you're still having issues:</p>";
echo "<ol>";
echo "<li>Clear browser cache and cookies</li>";
echo "<li>Restart XAMPP Apache and MySQL services</li>";
echo "<li>Check that all files are in the correct location</li>";
echo "<li>Verify database setup by running setup_database.php</li>";
echo "</ol>";
?>
