<?php
// Database configuration
// Update these constants to match your MySQL setup
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'activaura_db');

// Session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('activaura_sess');
?>



