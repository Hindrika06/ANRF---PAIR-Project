<?php
/**
 * Full System CRUD & Multi-Tenant Audit Script
 * Inspects database tables and checks CRUD support across all admin modules.
 */
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../admin/config/db.php';
require_once __DIR__ . '/../admin/role_access.php';

$universities = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

echo "=======================================================\n";
echo "FULL ANRF-PAIR SYSTEM CODEBASE & DATABASE CRUD AUDIT\n";
echo "=======================================================\n\n";

// 1. Audit DB Tables for Modules
$modules = [
    'Homepage Banners' => ['table' => 'homepage_banners', 'file' => 'admin/banner_management.php', 'public' => 'index.php'],
    'Announcements/Ticker' => ['table' => 'announcements', 'file' => 'admin/announcements_management.php', 'public' => 'whatsnew.php'],
    'Event Calendar' => ['table' => 'events', 'file' => 'admin/event_calendar.php', 'public' => 'events_activities.php'],
    'Conferences & Drive Links' => ['table' => 'conferences', 'file' => 'admin/conferences.php', 'public' => 'conferences.php'],
    'Gallery Albums' => ['table' => 'gallery_albums', 'file' => 'admin/gallery_albums_management.php', 'public' => 'gallery.php'],
    'Gallery Photos' => ['table' => 'gallery', 'file' => 'admin/gallery.php', 'public' => 'gallery.php'],
    'Team Management' => ['table' => 'team_members', 'file' => 'admin/team_management.php', 'public' => 'team.php'],
    'Manage Spoke Admins' => ['table' => 'users', 'file' => 'admin/manage_admins.php', 'public' => 'N/A'],
    'Research Infrastructure' => ['table' => 'research_infrastructure', 'file' => 'admin/research_infrastructure.php', 'public' => 'infrastructure-facilities.php'],
    'Collaborations' => ['table' => 'collaborations', 'file' => 'admin/collaborations_management.php', 'public' => 'collobrations.php'],
    'Internships' => ['table' => 'internships', 'file' => 'admin/internships.php', 'public' => 'internships.php'],
    'Publications' => ['table' => 'uoh_publications', 'file' => 'admin/publications.php', 'public' => 'publications-reports.php'],
    'Patents' => ['table' => 'uoh_patents', 'file' => 'admin/patents.php', 'public' => 'patents-innovations.php'],
    'Webinars' => ['table' => 'webinars', 'file' => 'admin/webinars.php', 'public' => 'webinars.php'],
    'Progress Reports' => ['table' => 'progress_reports', 'file' => 'admin/progress_reports.php', 'public' => 'progress_reports.php'],
];

foreach ($modules as $name => $info) {
    echo "--- AUDITING MODULE: $name ---\n";
    $table = $info['table'];
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $cnt = $countStmt->fetchColumn();
            echo "  [OK] Table `$table` exists (Row count: $cnt)\n";
        } else {
            echo "  [WARNING] Table `$table` does NOT exist\n";
        }
    } catch (Exception $e) {
        echo "  [ERROR] Table check failed for `$table`: " . $e->getMessage() . "\n";
    }

    $filePath = __DIR__ . '/../' . $info['file'];
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $hasInsert = stripos($content, 'INSERT INTO') !== false;
        $hasSelect = stripos($content, 'SELECT') !== false;
        $hasUpdate = stripos($content, 'UPDATE') !== false;
        $hasDelete = stripos($content, 'DELETE') !== false || stripos($content, 'action=delete') !== false;
        $hasCsrf   = stripos($content, 'csrf_token') !== false;
        $hasInst   = stripos($content, 'institute_prefix') !== false || stripos($content, 'canEditInstitute') !== false;

        echo "  [FILE] {$info['file']}:\n";
        echo "    - CREATE (INSERT): " . ($hasInsert ? "YES" : "NO") . "\n";
        echo "    - READ (SELECT): " . ($hasSelect ? "YES" : "NO") . "\n";
        echo "    - UPDATE (UPDATE): " . ($hasUpdate ? "YES" : "NO") . "\n";
        echo "    - DELETE (DELETE): " . ($hasDelete ? "YES" : "NO") . "\n";
        echo "    - CSRF Protection: " . ($hasCsrf ? "YES" : "NO") . "\n";
        echo "    - Multi-Tenant Filtering: " . ($hasInst ? "YES" : "NO") . "\n";
    } else {
        echo "  [ERROR] File {$info['file']} not found!\n";
    }
    echo "\n";
}

echo "=======================================================\n";
echo "AUDIT COMPLETE\n";
echo "=======================================================\n";
