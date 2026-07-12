<?php
if (!isset($GLOBALS['__role_access_loaded'])) { require_once 'role_access.php'; $GLOBALS['__role_access_loaded'] = true; }
$__bannerPrefix  = $prefix ?? resolveAdminPrefix();
$__isSuperA      = isSuperAdmin();
$__ownPrefix     = $_SESSION['institute_prefix'] ?? '';
$__canEdit       = canEditInstitute($__bannerPrefix);
$__isViewingOther = !$__isSuperA && ($__bannerPrefix !== $__ownPrefix);
?>

<div class="institute-banner <?= $__isViewingOther ? 'banner-view-only' : '' ?>">
    <!-- Logo -->
    <img
        src="<?= htmlspecialchars(getInstituteLogo($__bannerPrefix)) ?>"
        alt="<?= htmlspecialchars(getInstituteFullName($__bannerPrefix)) ?> Logo"
        class="institute-banner-logo"
    >

    <!-- Text + Dropdown -->
    <div class="institute-banner-text">
        <span class="institute-banner-label">
            <?php if ($__isSuperA): ?>
                🛡️ Super Admin — Select Institute
            <?php elseif ($__isViewingOther): ?>
                👁️ Viewing (Read-Only) — Switch Institute
            <?php else: ?>
                🏛️ Your Institute — Switch to View Others
            <?php endif; ?>
        </span>

        <div style="margin-top:5px;">
            <select
                id="institute-switcher"
                class="form-select institute-select-dropdown"
                onchange="window.location.href = window.location.pathname + '?prefix=' + this.value;"
            >
                <?php foreach (getVisiblePrefixes() as $pref): ?>
                    <option value="<?= $pref ?>" <?= ($__bannerPrefix === $pref) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(getInstituteFullName($pref)) ?>
                        <?php if (!$__isSuperA && $pref === $__ownPrefix): ?> ★<?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Right-side badge -->
    <div class="banner-right-badge">
        <?php if ($__isSuperA): ?>
            <div class="badge-pill badge-super">
                <i class="fas fa-shield-alt"></i> Full Access
            </div>
        <?php elseif ($__isViewingOther): ?>
            <div class="badge-pill badge-view-only">
                <i class="fas fa-eye"></i> View Only
            </div>
        <?php else: ?>
            <div class="badge-pill badge-edit">
                <i class="fas fa-edit"></i> Can Edit
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View-only notice bar -->
<?php if ($__isViewingOther): ?>
<div class="view-only-notice">
    <i class="fas fa-info-circle me-2"></i>
    You are viewing <strong><?= htmlspecialchars(getInstituteFullName($__bannerPrefix)) ?></strong> in
    <strong>read-only mode</strong>. You can only add, edit, or delete records for
    <strong><?= htmlspecialchars(getInstituteFullName($__ownPrefix)) ?></strong>
    (your assigned institute).
    <a href="?prefix=<?= htmlspecialchars($__ownPrefix) ?>" class="ms-2 view-only-return">
        ← Back to my institute
    </a>
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
    transition: border-color 0.3s;
    flex-wrap: wrap;
}
.institute-banner.banner-view-only {
    border-color: #fbbf24;
    background: linear-gradient(90deg, #fffbeb, #ffffff);
    box-shadow: 0 2px 12px rgba(251,191,36,0.15);
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

/* ── Dropdown ── */
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
.banner-view-only .institute-select-dropdown {
    border-color: #fbbf24 !important;
    color: #92400e;
    background-color: #fffbeb !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2392400e' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
}
.banner-view-only .institute-select-dropdown:focus {
    border-color: #f59e0b !important;
    box-shadow: 0 0 0 3px rgba(245,158,11,0.18) !important;
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
.badge-super  { background: linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; }
.badge-view-only { background: linear-gradient(135deg,#d97706,#f59e0b); color:#fff; }
.badge-edit   { background: linear-gradient(135deg,#065f46,#059669); color:#fff; }

/* ── View-only notice bar ── */
.view-only-notice {
    background: linear-gradient(90deg, #fffbeb, #fff3cd);
    border: 1px solid #fbbf24;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 13px;
    color: #78350f;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
}
.view-only-return {
    color: #1e40af;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
}
.view-only-return:hover { text-decoration: underline; }
</style>
