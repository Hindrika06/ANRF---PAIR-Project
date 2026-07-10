<?php
require_once __DIR__ . '/db.php';
start_session_if_needed();
session_unset();
session_destroy();
send_json(['message' => 'Logged out']);
?>



