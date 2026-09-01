<?php
// Progress Reports are strictly ADMIN-ONLY in ANRF-PAIR system.
// Redirect public visitors to home page to prevent narrative data leakage.
header("Location: index.php");
exit();
