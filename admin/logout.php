<?php
if (session_status() === PHP_SESSION_NONE) {
    $requestedTabToken = $_REQUEST['tab_token'] ?? $_SERVER['HTTP_X_TAB_TOKEN'] ?? null;
    if (!empty($requestedTabToken) && preg_match('/^[a-f0-9]{64}$/i', $requestedTabToken)) {
        session_id($requestedTabToken);
    }
    session_start();
}
$_SESSION = [];
session_unset();
session_destroy();

if (isset($_GET['timeout']) && $_GET['timeout'] === '1') {
    header('Location: ../login.php?timeout=1');
} else {
    header('Location: index.php');
}
exit();
