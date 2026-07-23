<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timeout duration in seconds (2 minutes for testing)
$timeout_duration = 120;

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix'])) {
    header("Location: ../login.php");
    exit();
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
