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
    return in_array($prefix, $adminAllowedPrefixes, true);
}

function resolveAdminPrefix($requestedPrefix = null)
{
    global $adminAllowedPrefixes;

    // Allow switching to any valid prefix (both super admin AND regular admin can view any institute)
    if ($requestedPrefix && in_array($requestedPrefix, $adminAllowedPrefixes, true)) {
        $_SESSION['active_prefix'] = $requestedPrefix;
        return $requestedPrefix;
    }

    // Use session-remembered active prefix
    if (!empty($_SESSION['active_prefix']) && in_array($_SESSION['active_prefix'], $adminAllowedPrefixes, true)) {
        return $_SESSION['active_prefix'];
    }

    // Default to the admin's own institute on first load
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

function getVisiblePrefixes()
{
    global $adminAllowedPrefixes;
    // All users (super admin AND regular admin) can SEE all institutes.
    // Write access is separately controlled by canEditInstitute().
    return $adminAllowedPrefixes;
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
    return $adminPrefixLogos[$prefix] ?? 'logo/3.png';
}
