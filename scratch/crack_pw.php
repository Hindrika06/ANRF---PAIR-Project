<?php
$hash = '$2y$10$S9JHjM62u1cOsBZPNXicSO3cfBBx8f9srFH/vpddbAByzA6UnUIsu';
$words = ['admin', 'admin123', 'superadmin', 'password', 'uoh123', 'admin@123', 'superadmin123', 'ANRF@2026', 'PAIR@2026', 'anrf2026', 'pair2026'];
foreach ($words as $w) {
    if (password_verify($w, $hash)) {
        echo "FOUND MATCH: $w\n";
    }
}
