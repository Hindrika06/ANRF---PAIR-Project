<?php
$ch = curl_init('http://127.0.0.1:8080/index.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$h = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Port 8080 Code: $code | Len: " . strlen($h) . "\n";
