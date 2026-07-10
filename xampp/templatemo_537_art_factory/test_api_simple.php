<?php
// Simple API test without output buffering issues
echo "<h2>Simple API Test</h2>";

// Test 1: Check if backend files exist and are readable
echo "<h3>1. File Check</h3>";
$files = ['backend/auth_signup.php', 'backend/auth_login.php', 'backend/db.php', 'backend/config.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p>✓ $file exists</p>";
    } else {
        echo "<p>✗ $file missing</p>";
    }
}

// Test 2: Check database connection
echo "<h3>2. Database Connection</h3>";
try {
    require_once 'backend/db.php';
    $db = get_db_connection();
    echo "<p>✓ Database connection successful</p>";
    
    // Check users table
    $result = $db->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✓ Users table has {$row['count']} users</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Database error: " . $e->getMessage() . "</p>";
}

// Test 3: Check configuration
echo "<h3>3. Configuration</h3>";
try {
    require_once 'backend/config.php';
    echo "<p>✓ Database name: " . DB_NAME . "</p>";
    echo "<p>✓ Database host: " . DB_HOST . "</p>";
} catch (Exception $e) {
    echo "<p>✗ Config error: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Manual Testing Instructions</h3>";
echo "<p>Since the API endpoints require proper HTTP requests, please test manually:</p>";
echo "<ol>";
echo "<li>Make sure XAMPP Apache and MySQL are running</li>";
echo "<li>Open your browser and go to: <a href='http://localhost/templatemo_537_art_factory/index.html' target='_blank'>http://localhost/templatemo_537_art_factory/index.html</a></li>";
echo "<li>Click 'Join us' to open the login modal</li>";
echo "<li>Click 'Create account' to switch to signup form</li>";
echo "<li>Fill in the form and try to create an account</li>";
echo "<li>Check browser console (F12) for any JavaScript errors</li>";
echo "</ol>";

echo "<h3>5. Common Issues & Solutions</h3>";
echo "<ul>";
echo "<li><strong>Network Error:</strong> Check if Apache is running on port 80</li>";
echo "<li><strong>Modal Not Opening:</strong> Check if Bootstrap and jQuery are loaded</li>";
echo "<li><strong>Form Not Submitting:</strong> Check browser console for JavaScript errors</li>";
echo "<li><strong>Database Error:</strong> Check if MySQL is running</li>";
echo "</ul>";

echo "<h3>6. Debug Steps</h3>";
echo "<p>If you're still having issues:</p>";
echo "<ol>";
echo "<li>Open browser developer tools (F12)</li>";
echo "<li>Go to Network tab</li>";
echo "<li>Try to submit the form</li>";
echo "<li>Look for failed requests to backend/auth_signup.php or backend/auth_login.php</li>";
echo "<li>Check the response for error messages</li>";
echo "</ol>";
?>
