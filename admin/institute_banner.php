<?php
if (!isset($GLOBALS['__role_access_loaded'])) { require_once 'role_access.php'; $GLOBALS['__role_access_loaded'] = true; }
$__bannerPrefix = $prefix ?? resolveAdminPrefix();
?>
<div class="institute-banner">
    <img src="<?= htmlspecialchars(getInstituteLogo($__bannerPrefix)) ?>" alt="<?= htmlspecialchars(getInstituteFullName($__bannerPrefix)) ?> Logo" class="institute-banner-logo">
    <div class="institute-banner-text">
        <span class="institute-banner-label">Managing Institute</span>
        <span class="institute-banner-name"><?= htmlspecialchars(getInstituteFullName($__bannerPrefix)) ?></span>
    </div>
</div>
<style>
.institute-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 18px;
    margin-bottom: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, .03);
}
.institute-banner-logo {
    height: 48px;
    width: auto;
    object-fit: contain;
    flex-shrink: 0;
}
.institute-banner-text {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
}
.institute-banner-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748B;
}
.institute-banner-name {
    font-size: 16px;
    font-weight: 800;
    color: #024283;
}
</style>
