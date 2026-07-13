<?php
if (!isset($GLOBALS['__role_access_loaded'])) { require_once 'role_access.php'; $GLOBALS['__role_access_loaded'] = true; }
$__bannerPrefix = $prefix ?? resolveAdminPrefix();
$__isSuperA     = isSuperAdmin();
$__ownPrefix    = $_SESSION['institute_prefix'] ?? '';
?>

<?php if ($__isSuperA): ?>
<!-- ══════════════════════════════════════════════════════
     SUPER ADMIN — Full institute switcher (unchanged)
════════════════════════════════════════════════════════ -->
<div class="institute-banner">
    <!-- Logo -->
    <img
        src="<?= htmlspecialchars(getInstituteLogo($__bannerPrefix)) ?>"
        alt="<?= htmlspecialchars(getInstituteFullName($__bannerPrefix)) ?> Logo"
        class="institute-banner-logo"
    >

    <!-- Text + Dropdown -->
    <div class="institute-banner-text" style="flex: 1; display: flex; flex-direction: column;">
        <span class="institute-banner-label">🛡️ Super Admin — Switch Institute</span>
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-top: 5px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <select
                    id="institute-switcher"
                    class="form-select institute-select-dropdown"
                    onchange="window.location.href = window.location.pathname + '?prefix=' + this.value;"
                >
                    <?php
                    global $adminAllowedPrefixes;
                    foreach ($adminAllowedPrefixes as $pref): ?>
                        <option value="<?= $pref ?>" <?= ($__bannerPrefix === $pref) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(getInstituteFullName($pref)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="banner-right-badge" style="margin-left: auto;">
                <div class="badge-pill badge-super">
                    <i class="fas fa-shield-alt"></i> Full Access
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ══════════════════════════════════════════════════════
     REGULAR ADMIN — Static info banner (no switcher)
════════════════════════════════════════════════════════ -->
<div class="institute-banner">
    <!-- Logo -->
    <img
        src="<?= htmlspecialchars(getInstituteLogo($__bannerPrefix)) ?>"
        alt="<?= htmlspecialchars(getInstituteFullName($__bannerPrefix)) ?> Logo"
        class="institute-banner-logo"
    >

    <!-- Institute name (static — no dropdown) -->
    <div class="institute-banner-text">
        <span class="institute-banner-label">🏛️ Your Institute</span>
        <span class="institute-banner-name"><?= htmlspecialchars(getInstituteFullName($__bannerPrefix)) ?></span>
    </div>
</div>
<?php endif; ?>

<style>
/* ── Institute Banner ── */
.institute-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px 20px;
    margin-bottom: 6px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    flex-wrap: wrap;
}
.institute-banner-logo {
    height: 52px;
    width: auto;
    object-fit: contain;
    flex-shrink: 0;
    border-radius: 8px;
}
.institute-banner-text {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
    flex: 1;
    min-width: 220px;
}
.institute-banner-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748b;
    margin-bottom: 3px;
}
.institute-banner-name {
    font-size: 15px;
    font-weight: 800;
    color: #1e3a8a;
}

/* ── Super Admin Dropdown (unchanged) ── */
.institute-select-dropdown {
    font-size: 15px !important;
    font-weight: 800 !important;
    border: 1.5px solid #93c5fd !important;
    border-radius: 8px !important;
    padding: 6px 36px 6px 14px !important;
    background-color: #fff !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%231e3a8a' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px 12px !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    min-width: 260px;
    max-width: 100%;
    cursor: pointer;
    outline: none;
    font-family: inherit;
    color: #1e3a8a;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.institute-select-dropdown:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.18) !important;
}

/* ── Right badges ── */
.banner-right-badge { margin-left: auto; }
.badge-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.03em;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}
.badge-super { background: linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; }
</style>
