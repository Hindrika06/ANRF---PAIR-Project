<?php
$adminAllowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
$adminPrefixLabels = [
    'cuk' => 'CUK',
    'kannur' => 'Kannur',
    'mgu' => 'MGU',
    'ou' => 'OU',
    'svu' => 'SVU',
    'uoh' => 'UoH',
    'yvu' => 'YVU',
];
$adminPrefixFullNames = [
    'cuk' => 'Central University of Karnataka',
    'kannur' => 'Kannur University',
    'mgu' => 'Mahatma Gandhi University',
    'ou' => 'Osmania University',
    'svu' => 'Sri Venkateswara University',
    'uoh' => 'University of Hyderabad',
    'yvu' => 'Yogi Vemana University',
];
$adminPrefixLogos = [
    'cuk' => '../logos/cuk1.jpg',
    'kannur' => '../logos/ku1.jpg',
    'mgu' => '../logos/mg1.jpg',
    'ou' => '../logos/ou1.jpg',
    'svu' => '../logos/gan1.jpg',
    'uoh' => '../3.png',
    'yvu' => '../logos/yu.jpg',
];
$adminPrefixFavicons = [
    'cuk' => 'uploads/institutes/cuk_logo.png',
    'kannur' => 'uploads/institutes/kannur_logo.png',
    'mgu' => 'uploads/institutes/mgu_logo.png',
    'ou' => 'uploads/institutes/ou_logo.png',
    'svu' => 'uploads/institutes/svu_logo.png',
    'uoh' => 'uploads/institutes/uoh_logo.png',
    'yvu' => 'uploads/institutes/yvu_logo.png',
];

function isValidPrefix($prefix)
{
    global $adminAllowedPrefixes;
    return in_array($prefix, $adminAllowedPrefixes, true);
}

/**
 * Architecture & Session Security Note:
 * Standard PHP session cookies (PHPSESSID) are scoped to the browser profile/domain.
 * When logging into a different account in Tab 2 of the same browser, PHP updates
 * the server session tied to PHPSESSID.
 *
 * To enforce strict role and institute boundary security:
 * 1. isSuperAdmin() checks the authenticated user's session role ('super_admin').
 * 2. Regular Institute Admins ('admin') are locked strictly to $_SESSION['institute_prefix'].
 *    Any URL parameter like ?prefix=uoh is explicitly ignored for Institute Admins.
 * 3. Super Admins ('super_admin') can switch active institute view using ?prefix= parameter.
 * 4. Super-admin-only modules enforce server-side auth guards (if (!isSuperAdmin()) header("Location: dashboard.php"); exit();).
 */
function resolveAdminPrefix($requestedPrefix = null)
{
    global $adminAllowedPrefixes;

    // Super admin: allow ?prefix= from URL or argument for viewing any institute
    if (isSuperAdmin()) {
        $req = $requestedPrefix ?? $_GET['prefix'] ?? null;
        if ($req && in_array($req, $adminAllowedPrefixes, true)) {
            $_SESSION['active_prefix'] = $req;
            return $req;
        }
        if (!empty($_SESSION['active_prefix']) && in_array($_SESSION['active_prefix'], $adminAllowedPrefixes, true)) {
            return $_SESSION['active_prefix'];
        }
        return 'uoh'; // Super admin default
    }

    // Regular admin: ALWAYS use the institute_prefix stored in session at login.
    // The ?prefix= URL parameter or requested argument is strictly ignored — institute is locked to the account.
    if (!empty($_SESSION['institute_prefix']) && in_array($_SESSION['institute_prefix'], $adminAllowedPrefixes, true)) {
        $_SESSION['active_prefix'] = $_SESSION['institute_prefix'];
        return $_SESSION['institute_prefix'];
    }

    return $adminAllowedPrefixes[0];
}

function isSuperAdmin()
{
    $role = $_SESSION['role'] ?? 'admin';
    return $role === 'super_admin';
}

function canEditInstitute($prefix)
{
    return isSuperAdmin() || (!empty($_SESSION['institute_prefix']) && $_SESSION['institute_prefix'] === $prefix);
}

function getInstituteLabel($prefix)
{
    global $adminPrefixLabels;
    return $adminPrefixLabels[$prefix] ?? strtoupper($prefix);
}

function getInstituteFullName($prefix)
{
    global $adminPrefixFullNames;
    return $adminPrefixFullNames[$prefix] ?? getInstituteLabel($prefix);
}

function getInstituteLogo($prefix)
{
    global $adminPrefixLogos;
    return $adminPrefixLogos[$prefix] ?? '../3.png';
}

function getInstituteFavicon($prefix)
{
    global $adminPrefixFavicons;
    return $adminPrefixFavicons[$prefix] ?? 'uploads/institutes/uoh_logo.png';
}

function getActiveInstituteContext()
{
    $prefix = resolveAdminPrefix();
    $name = getInstituteFullName($prefix);
    $favicon = getInstituteFavicon($prefix);
    return [
        'prefix' => $prefix,
        'name' => $name,
        'favicon' => $favicon,
    ];
}

