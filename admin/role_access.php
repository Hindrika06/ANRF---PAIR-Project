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
    'uoh' => 'logo/3.png',
    'yvu' => '../logos/yu.jpg',
];

function isValidPrefix($prefix)
{
    global $adminAllowedPrefixes;
    return $prefix === 'all' || in_array($prefix, $adminAllowedPrefixes, true);
}

function resolveAdminPrefix()
{
    global $adminAllowedPrefixes;

    // Super admin: allow ?prefix= from URL for viewing any institute or 'all' overview
    if (isSuperAdmin()) {
        $req = $_GET['prefix'] ?? null;
        if ($req === 'all' || ($req && in_array($req, $adminAllowedPrefixes, true))) {
            $_SESSION['active_prefix'] = $req;
            return $req;
        }
        if (!empty($_SESSION['active_prefix']) && ($_SESSION['active_prefix'] === 'all' || in_array($_SESSION['active_prefix'], $adminAllowedPrefixes, true))) {
            return $_SESSION['active_prefix'];
        }
        return 'all'; // Super admin default: All Institutes Hub Overview
    }

    // Regular admin: ALWAYS use the institute_prefix stored in session at login.
    // The ?prefix= URL parameter is ignored — institute is locked to the account.
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
    if ($prefix === 'all') return 'ALL';
    return $adminPrefixLabels[$prefix] ?? strtoupper($prefix);
}

function getInstituteFullName($prefix)
{
    global $adminPrefixFullNames;
    if ($prefix === 'all') return 'All Institutes (Hub Overview)';
    return $adminPrefixFullNames[$prefix] ?? getInstituteLabel($prefix);
}

function getInstituteLogo($prefix)
{
    global $adminPrefixLogos;
    if ($prefix === 'all') return 'logo/logo.png';
    return $adminPrefixLogos[$prefix] ?? 'logo/3.png';
}
