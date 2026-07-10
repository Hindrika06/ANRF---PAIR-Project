<?php
// Test the signup and login endpoints directly
echo "<h2>Direct API Endpoint Testing</h2>";

// Test signup endpoint
echo "<h3>Testing Signup Endpoint</h3>";
$signupData = [
    'name' => 'Test User ' . time(),
    'email' => 'test' . time() . '@example.com',
    'password' => 'testpass123'
];

$signupJson = json_encode($signupData);

echo "<p>Testing signup with data: " . htmlspecialchars($signupJson) . "</p>";

// Simulate POST request to signup endpoint
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capture output
ob_start();
include 'backend/auth_signup.php';
$signupResponse = ob_get_clean();

echo "<p><strong>Signup Response:</strong></p>";
echo "<pre>" . htmlspecialchars($signupResponse) . "</pre>";

// Test login endpoint
echo "<h3>Testing Login Endpoint</h3>";
$loginData = [
    'email' => $signupData['email'],
    'password' => $signupData['password']
];

$loginJson = json_encode($loginData);

echo "<p>Testing login with data: " . htmlspecialchars($loginJson) . "</p>";

// Simulate POST request to login endpoint
ob_start();
include 'backend/auth_login.php';
$loginResponse = ob_get_clean();

echo "<p><strong>Login Response:</strong></p>";
echo "<pre>" . htmlspecialchars($loginResponse) . "</pre>";

echo "<h3>Test Complete</h3>";
echo "<p>If you see JSON responses above, the API endpoints are working correctly.</p>";
echo "<p>If you see errors, there may be an issue with the backend code.</p>";
?>
