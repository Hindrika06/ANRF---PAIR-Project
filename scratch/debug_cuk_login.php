<?php
$ch = curl_init('http://127.0.0.1:8080/login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'Idsathyan@cuk.ac.in',
    'password' => 'cuk@admin123'
]));
$html = curl_exec($ch);
curl_close($ch);

echo "HTML Response for Idsathyan@cuk.ac.in:\n";
preg_match('/<div class="alert-custom">(.*?)<\/div>/s', $html, $matches);
if (!empty($matches[1])) {
    echo "ALERT: " . trim($matches[1]) . "\n";
} else {
    echo "NO ALERT FOUND IN HTML!\n";
}
