<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $tabToken = $_SESSION['tab_token'] ?? $_REQUEST['tab_token'] ?? null;
    $target = "dashboard.php";
    if (!empty($tabToken)) {
        $target .= "?tab_token=" . urlencode($tabToken);
    }
    header("Location: " . $target);
    exit();
}

header("Location: ../login.php");
exit();
?>
