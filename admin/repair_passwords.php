<?php
// We will set your password to: admin123
$password_to_hash = 'admin123';
echo "<h3>Your clean password hash:</h3>";
echo "<code style='background:#f4f4f4; padding:5px 10px; display:inline-block; font-size:1.2rem;'>";
echo password_hash($password_to_hash, PASSWORD_BCRYPT);
echo "</code>";
?>