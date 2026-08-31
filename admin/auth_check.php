<?php
// Multi-Tab Server-Side Session Isolation Logic
$requestedTabToken = $_REQUEST['tab_token'] ?? $_SERVER['HTTP_X_TAB_TOKEN'] ?? null;

$isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
if (session_status() === PHP_SESSION_NONE) {
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        session_set_cookie_params(0, '/; samesite=Lax', '', $isSecure, true);
    }
}

if (!empty($requestedTabToken) && preg_match('/^[a-f0-9]{64}$/i', $requestedTabToken)) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_id($requestedTabToken);
    session_start();
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timeout duration in seconds (30 minutes)
$timeout_duration = 1800;

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Session validation: reload user role and institute prefix from DB if missing
if (empty($_SESSION['role']) || empty($_SESSION['institute_prefix']) || empty($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/config/db.php';
        $stmt = $pdo->prepare("SELECT id, institute_prefix, role FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['username']]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userRow && !empty($userRow['role']) && !empty($userRow['institute_prefix'])) {
            $_SESSION['user_id']          = $userRow['id'];
            $_SESSION['institute_prefix'] = $userRow['institute_prefix'];
            $_SESSION['role']             = $userRow['role'];
        } else {
            session_unset();
            session_destroy();
            header("Location: ../login.php");
            exit();
        }
    } catch (Exception $e) {
        // If DB lookup fails while session role is missing, treat session as invalid and redirect
        session_unset();
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
}

// Check for inactivity
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $elapsed_time = time() - $_SESSION['LAST_ACTIVITY'];
    if ($elapsed_time > $timeout_duration) {
        // Clear all session variables
        session_unset();
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page with timeout parameter
        header("Location: ../login.php?timeout=1");
        exit();
    }
}

// Update the last activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();

// Start output buffering to inject client-side inactivity timer script before </body>
if (PHP_SAPI !== 'cli') {
    ob_start(function($buffer) use ($timeout_duration) {
        $js_timeout = $timeout_duration * 1000; // in milliseconds

        $js_script = <<<JS
<script>
(function() {
    var timeoutDuration = {$js_timeout};
    var idleTimer;

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(logoutUser, timeoutDuration);
    }

    function logoutUser() {
        window.location.href = 'logout.php?timeout=1';
    }

    // User activity events to listen to
    var events = ['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart', 'click'];
    events.forEach(function(eventName) {
        document.addEventListener(eventName, resetIdleTimer, true);
    });

    // Start initial timer
    resetIdleTimer();
})();
</script>
JS;

        // Inject before </body> if present
        if (stripos($buffer, '</body>') !== false) {
            return str_replace('</body>', $js_script . '</body>', $buffer);
        }
        return $buffer;
    });
}

// CSRF PROTECTION MIDDLEWARE HELPERS
if (!function_exists('getCsrfToken')) {
    function getCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('getCsrfInputField')) {
    function getCsrfInputField() {
        $token = getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                if (PHP_SAPI !== 'cli') {
                    http_response_code(403);
                    die('CSRF Verification Failed: Invalid or missing security token.');
                }
            }
        }
    }
}
