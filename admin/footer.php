<?php
/**
 * admin/footer.php — ANRF–PAIR Admin Portal
 * Minimal footer include for admin pages.
 * Closes the HTML document opened by nav_header.php / header.php.
 *
 * Usage: <?php include 'footer.php'; ?>
 */
include_once __DIR__ . '/includes/confirm_modal.php';
?>
<div class="footer">
    <div class="copyright">
        <p>&copy; <?php echo date('Y'); ?> ANRF&ndash;PAIR Project, University of Hyderabad. All rights reserved. Developed by <a href="https://bhimavaramdigitals.com/" target="_blank" rel="noopener noreferrer" class="footer-dev-link">Bhimavaram Digitals ↗</a></p>
    </div>
</div>
<script src="assets/js/confirm-modal.js?v=<?= filemtime(__DIR__ . '/assets/js/confirm-modal.js') ?>"></script>
</body>
</html>
