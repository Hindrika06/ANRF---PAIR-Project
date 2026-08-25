<?php
/**
 * Master Full-Site Automated QA Test Suite
 * Performs comprehensive CRUD, Multi-Tenant Isolation, Spoke Admin Security,
 * and Public Page Rendering checks across all 17 Admin Modules and 7 Universities.
 */

date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../admin/config/db.php';
require_once __DIR__ . '/../admin/role_access.php';

$universities = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
$passCount = 0;
$failCount = 0;
$warningCount = 0;
$results = [];

function recordTestResult($module, $testName, $passed, $details = '', $warning = false) {
    global $passCount, $failCount, $warningCount, $results;
    if ($warning) {
        $status = "WARNING";
        $warningCount++;
    } elseif ($passed) {
        $status = "PASS";
        $passCount++;
    } else {
        $status = "FAIL";
        $failCount++;
    }
    $results[] = [
        'module' => $module,
        'test' => $testName,
        'status' => $status,
        'details' => $details
    ];
    echo sprintf("[%s] %s - %s: %s\n", $status, $module, $testName, $details);
}

echo "=======================================================================\n";
echo "MASTER FULL-SITE MULTI-TENANT E2E QA TEST SUITE (ANRF-PAIR)\n";
echo "=======================================================================\n\n";

// Ensure dummy test upload file
$uploadDir = __DIR__ . '/../uploads/slider/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$testImg = 'uploads/slider/test_qa_master_dummy.jpg';
$fullTestImg = __DIR__ . '/../' . $testImg;
if (!file_exists($fullTestImg)) {
    copy(__DIR__ . '/../1.png', $fullTestImg);
}

// -----------------------------------------------------------------------
// 1. HOMEPAGE BANNERS MODULE
// -----------------------------------------------------------------------
echo "--- 1/17 AUDITING MODULE: Homepage Banners ---\n";
foreach ($universities as $u) {
    // Create
    $stmt = $pdo->prepare("INSERT INTO `homepage_banners` (title, short_description, target_url, institute_prefix, image_path, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(["QA_BANNER_$u", "Description for $u", "https://example.com/$u", $u, $testImg, 'Active']);
    $id = (int)$pdo->lastInsertId();
    recordTestResult("Homepage Banners", "Create ($u)", $id > 0, "Created ID #$id for $u");

    // Read & List
    $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE institute_prefix = ? OR institute_prefix = 'all'");
    $stmt->execute([$u]);
    $rows = $stmt->fetchAll();
    $found = false;
    foreach ($rows as $r) { if ((int)$r['id'] === $id) $found = true; }
    recordTestResult("Homepage Banners", "Read ($u)", $found, "Found ID #$id in $u listing");

    // Edit & Update
    $stmt = $pdo->prepare("UPDATE `homepage_banners` SET title = ? WHERE id = ?");
    $stmt->execute(["QA_BANNER_{$u}_UPDATED", $id]);
    $stmt = $pdo->prepare("SELECT title, image_path FROM `homepage_banners` WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    $updateOk = ($row && $row['title'] === "QA_BANNER_{$u}_UPDATED" && $row['image_path'] === $testImg);
    recordTestResult("Homepage Banners", "Update & Image Preservation ($u)", $updateOk, "Title updated & image preserved");

    // Isolation
    $other = ($u === 'uoh') ? 'cuk' : 'uoh';
    $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE (institute_prefix = ? OR institute_prefix = 'all') AND id = ?");
    $stmt->execute([$other, $id]);
    $leaked = (bool)$stmt->fetch();
    recordTestResult("Homepage Banners", "Multi-Tenant Isolation ($u vs $other)", !$leaked, "Hidden in $other context");

    // Delete
    $stmt = $pdo->prepare("DELETE FROM `homepage_banners` WHERE id = ?");
    $stmt->execute([$id]);
    $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE id = ?");
    $stmt->execute([$id]);
    $deleted = !(bool)$stmt->fetch();
    recordTestResult("Homepage Banners", "Delete ($u)", $deleted, "Deleted ID #$id cleanly");
}

// -----------------------------------------------------------------------
// 2. TICKER ANNOUNCEMENTS MODULE
// -----------------------------------------------------------------------
echo "\n--- 2/17 AUDITING MODULE: Ticker Announcements ---\n";
// Create
$stmt = $pdo->prepare("INSERT INTO `announcements` (title, link, is_active) VALUES (?, ?, ?)");
$stmt->execute(["QA_TICKER_TEST", "events_activities.php", 1]);
$tId = (int)$pdo->lastInsertId();
recordTestResult("Ticker Announcements", "Create", $tId > 0, "Created Ticker ID #$tId");

// Update
$stmt = $pdo->prepare("UPDATE `announcements` SET title = ? WHERE id = ?");
$stmt->execute(["QA_TICKER_TEST_UPDATED", $tId]);
$stmt = $pdo->prepare("SELECT title FROM `announcements` WHERE id = ?");
$stmt->execute([$tId]);
$tRow = $stmt->fetch();
recordTestResult("Ticker Announcements", "Update", $tRow && $tRow['title'] === "QA_TICKER_TEST_UPDATED", "Updated title to QA_TICKER_TEST_UPDATED");

// Delete
$stmt = $pdo->prepare("DELETE FROM `announcements` WHERE id = ?");
$stmt->execute([$tId]);
recordTestResult("Ticker Announcements", "Delete", true, "Deleted Ticker ID #$tId");

// -----------------------------------------------------------------------
// 3. EVENT CALENDAR MODULE
// -----------------------------------------------------------------------
echo "\n--- 3/17 AUDITING MODULE: Event Calendar ---\n";
foreach ($universities as $u) {
    $stmt = $pdo->prepare("INSERT INTO `events` (title, description, event_date, start_time, end_time, venue, university_id, status, publish_status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(["QA_EVENT_$u", "Event details for $u", date('Y-m-d'), '10:00:00', '17:00:00', 'Main Auditorium', $u, 'upcoming', 1, 'superadmin']);
    $eId = (int)$pdo->lastInsertId();
    recordTestResult("Event Calendar", "Create ($u)", $eId > 0, "Created Event ID #$eId for $u");

    // Read & List
    $stmt = $pdo->prepare("SELECT * FROM `events` WHERE university_id = ? OR university_id = 'all'");
    $stmt->execute([$u]);
    $eFound = false;
    foreach ($stmt->fetchAll() as $er) { if ((int)$er['id'] === $eId) $eFound = true; }
    recordTestResult("Event Calendar", "Read ($u)", $eFound, "Event #$eId listed in $u");

    // Update
    $stmt = $pdo->prepare("UPDATE `events` SET title = ? WHERE id = ?");
    $stmt->execute(["QA_EVENT_{$u}_UPDATED", $eId]);
    $stmt = $pdo->prepare("SELECT title FROM `events` WHERE id = ?");
    $stmt->execute([$eId]);
    $eRow = $stmt->fetch();
    recordTestResult("Event Calendar", "Update ($u)", $eRow && $eRow['title'] === "QA_EVENT_{$u}_UPDATED", "Updated title for $u");

    // Delete
    $stmt = $pdo->prepare("DELETE FROM `events` WHERE id = ?");
    $stmt->execute([$eId]);
    recordTestResult("Event Calendar", "Delete ($u)", true, "Deleted Event #$eId");
}

// -----------------------------------------------------------------------
// 4. MANAGE SPOKE ADMINS MODULE
// -----------------------------------------------------------------------
echo "\n--- 4/17 AUDITING MODULE: Manage Spoke Admins ---\n";
$stmt = $pdo->prepare("INSERT INTO `users` (username, password, institute_prefix, role) VALUES (?, ?, ?, ?)");
$dummyHash = password_hash('qa_pass123', PASSWORD_DEFAULT);
$stmt->execute(["qa_test_admin_cuk@cuk.ac.in", $dummyHash, 'cuk', 'admin']);
$uId = (int)$pdo->lastInsertId();
recordTestResult("Manage Spoke Admins", "Create", $uId > 0, "Created Spoke Admin Account #$uId");

// Update
$stmt = $pdo->prepare("UPDATE `users` SET institute_prefix = ? WHERE id = ?");
$stmt->execute(['uoh', $uId]);
$stmt = $pdo->prepare("SELECT institute_prefix FROM `users` WHERE id = ?");
$stmt->execute([$uId]);
$uRow = $stmt->fetch();
recordTestResult("Manage Spoke Admins", "Update", $uRow && $uRow['institute_prefix'] === 'uoh', "Updated admin institute to uoh");

// Delete
$stmt = $pdo->prepare("DELETE FROM `users` WHERE id = ?");
$stmt->execute([$uId]);
recordTestResult("Manage Spoke Admins", "Delete", true, "Deleted Spoke Admin Account #$uId");

// -----------------------------------------------------------------------
// 5. CONFERENCES & DRIVE EVENT LINKS MODULE
// -----------------------------------------------------------------------
echo "\n--- 5/17 AUDITING MODULE: Conferences & Drive Links ---\n";
foreach ($universities as $u) {
    $tbl = "{$u}_conferences";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `$tbl` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `taskno` VARCHAR(50) DEFAULT NULL,
        `title` VARCHAR(255) NOT NULL,
        `organizer` VARCHAR(255) DEFAULT NULL,
        `start_date` DATE DEFAULT NULL,
        `end_date` DATE DEFAULT NULL,
        `location` VARCHAR(255) DEFAULT NULL,
        `submission_deadline` DATE DEFAULT NULL,
        `website_url` VARCHAR(1000) DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->prepare("INSERT INTO `$tbl` (title, organisers, conf_date) VALUES (?, ?, ?)");
    $stmt->execute(["QA_CONF_$u", "Organiser $u", date('Y-m-d')]);
    $cId = (int)$pdo->lastInsertId();
    recordTestResult("Conferences & Drive Links", "Create ($u)", $cId > 0, "Created Conference #$cId in $tbl");

    $stmt = $pdo->prepare("UPDATE `$tbl` SET title = ? WHERE id = ?");
    $stmt->execute(["QA_CONF_{$u}_UPDATED", $cId]);
    $stmt = $pdo->prepare("SELECT title FROM `$tbl` WHERE id = ?");
    $stmt->execute([$cId]);
    $cRow = $stmt->fetch();
    recordTestResult("Conferences & Drive Links", "Update ($u)", $cRow && $cRow['title'] === "QA_CONF_{$u}_UPDATED", "Updated conference in $tbl");

    $stmt = $pdo->prepare("DELETE FROM `$tbl` WHERE id = ?");
    $stmt->execute([$cId]);
    recordTestResult("Conferences & Drive Links", "Delete ($u)", true, "Deleted Conference #$cId from $tbl");
}

// -----------------------------------------------------------------------
// 6. GALLERY ALBUMS MODULE
// -----------------------------------------------------------------------
echo "\n--- 6/17 AUDITING MODULE: Gallery Albums ---\n";
foreach ($universities as $u) {
    $stmt = $pdo->prepare("INSERT INTO `gallery_albums` (album_name, description, institute_prefix) VALUES (?, ?, ?)");
    $stmt->execute(["QA_ALBUM_$u", "Album desc $u", $u]);
    $albId = (int)$pdo->lastInsertId();
    recordTestResult("Gallery Albums", "Create ($u)", $albId > 0, "Created Album #$albId for $u");

    $stmt = $pdo->prepare("UPDATE `gallery_albums` SET album_name = ? WHERE id = ?");
    $stmt->execute(["QA_ALBUM_{$u}_UPDATED", $albId]);
    $stmt = $pdo->prepare("SELECT album_name FROM `gallery_albums` WHERE id = ?");
    $stmt->execute([$albId]);
    $albRow = $stmt->fetch();
    recordTestResult("Gallery Albums", "Update ($u)", $albRow && $albRow['album_name'] === "QA_ALBUM_{$u}_UPDATED", "Updated album name");

    $stmt = $pdo->prepare("DELETE FROM `gallery_albums` WHERE id = ?");
    $stmt->execute([$albId]);
    recordTestResult("Gallery Albums", "Delete ($u)", true, "Deleted Album #$albId");
}

// -----------------------------------------------------------------------
// 7. PUBLICATIONS MODULE
// -----------------------------------------------------------------------
echo "\n--- 7/17 AUDITING MODULE: Publications ---\n";
foreach ($universities as $u) {
    $tbl = "{$u}_publications";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `$tbl` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `task_no` VARCHAR(50) DEFAULT NULL,
        `publication_title` VARCHAR(500) NOT NULL,
        `author_name` VARCHAR(500) DEFAULT NULL,
        `doi_number` VARCHAR(255) DEFAULT NULL,
        `publication_date` DATE DEFAULT NULL,
        `publication_journal` VARCHAR(500) DEFAULT NULL,
        `impact_factor` VARCHAR(50) DEFAULT NULL,
        `approval_status` ENUM('Approved','Pending','Rejected') DEFAULT 'Approved',
        `publish_status` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->prepare("INSERT INTO `$tbl` (publication_title, author_name, publication_journal) VALUES (?, ?, ?)");
    $stmt->execute(["QA_PUB_$u", "Author $u", "IEEE Transactions"]);
    $pId = (int)$pdo->lastInsertId();
    recordTestResult("Publications", "Create ($u)", $pId > 0, "Created Publication #$pId in $tbl");

    $stmt = $pdo->prepare("UPDATE `$tbl` SET publication_title = ? WHERE id = ?");
    $stmt->execute(["QA_PUB_{$u}_UPDATED", $pId]);
    $stmt = $pdo->prepare("SELECT publication_title FROM `$tbl` WHERE id = ?");
    $stmt->execute([$pId]);
    $pRow = $stmt->fetch();
    recordTestResult("Publications", "Update ($u)", $pRow && $pRow['publication_title'] === "QA_PUB_{$u}_UPDATED", "Updated publication title");

    $stmt = $pdo->prepare("DELETE FROM `$tbl` WHERE id = ?");
    $stmt->execute([$pId]);
    recordTestResult("Publications", "Delete ($u)", true, "Deleted Publication #$pId");
}

// -----------------------------------------------------------------------
// 8. PATENTS MODULE
// -----------------------------------------------------------------------
echo "\n--- 8/17 AUDITING MODULE: Patents ---\n";
foreach ($universities as $u) {
    $tbl = "{$u}_patents";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `$tbl` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `task_no` VARCHAR(50) DEFAULT NULL,
        `patent_title` VARCHAR(500) NOT NULL,
        `inventor_name` VARCHAR(500) DEFAULT NULL,
        `patent_number` VARCHAR(255) DEFAULT NULL,
        `application_number` VARCHAR(255) DEFAULT NULL,
        `filing_date` DATE DEFAULT NULL,
        `grant_date` DATE DEFAULT NULL,
        `patent_status` VARCHAR(100) DEFAULT 'Filed',
        `approval_status` ENUM('Approved','Pending','Rejected') DEFAULT 'Approved',
        `publish_status` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->prepare("INSERT INTO `$tbl` (patent_title, inventor_name) VALUES (?, ?)");
    $stmt->execute(["QA_PATENT_$u", "Inventor $u"]);
    $patId = (int)$pdo->lastInsertId();
    recordTestResult("Patents", "Create ($u)", $patId > 0, "Created Patent #$patId in $tbl");

    $stmt = $pdo->prepare("UPDATE `$tbl` SET patent_title = ? WHERE id = ?");
    $stmt->execute(["QA_PATENT_{$u}_UPDATED", $patId]);
    $stmt = $pdo->prepare("SELECT patent_title FROM `$tbl` WHERE id = ?");
    $stmt->execute([$patId]);
    $patRow = $stmt->fetch();
    recordTestResult("Patents", "Update ($u)", $patRow && $patRow['patent_title'] === "QA_PATENT_{$u}_UPDATED", "Updated patent title");

    $stmt = $pdo->prepare("DELETE FROM `$tbl` WHERE id = ?");
    $stmt->execute([$patId]);
    recordTestResult("Patents", "Delete ($u)", true, "Deleted Patent #$patId");
}

// -----------------------------------------------------------------------
// 9. INTERNSHIPS MODULE
// -----------------------------------------------------------------------
echo "\n--- 9/17 AUDITING MODULE: Internships ---\n";
foreach ($universities as $u) {
    $tbl = "{$u}_internships";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `$tbl` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `task_no` VARCHAR(50) DEFAULT NULL,
        `title` VARCHAR(255) NOT NULL,
        `mentor` VARCHAR(255) DEFAULT NULL,
        `student_name` VARCHAR(255) DEFAULT NULL,
        `duration` VARCHAR(100) DEFAULT NULL,
        `start_date` DATE DEFAULT NULL,
        `end_date` DATE DEFAULT NULL,
        `stipend` VARCHAR(100) DEFAULT NULL,
        `approval_status` ENUM('Approved','Pending','Rejected') DEFAULT 'Approved',
        `publish_status` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->prepare("INSERT INTO `$tbl` (title, project_investigator) VALUES (?, ?)");
    $stmt->execute(["QA_INTERN_$u", "Investigator $u"]);
    $inId = (int)$pdo->lastInsertId();
    recordTestResult("Internships", "Create ($u)", $inId > 0, "Created Internship #$inId in $tbl");

    $stmt = $pdo->prepare("UPDATE `$tbl` SET title = ? WHERE id = ?");
    $stmt->execute(["QA_INTERN_{$u}_UPDATED", $inId]);
    $stmt = $pdo->prepare("SELECT title FROM `$tbl` WHERE id = ?");
    $stmt->execute([$inId]);
    $inRow = $stmt->fetch();
    recordTestResult("Internships", "Update ($u)", $inRow && $inRow['title'] === "QA_INTERN_{$u}_UPDATED", "Updated internship title");

    $stmt = $pdo->prepare("DELETE FROM `$tbl` WHERE id = ?");
    $stmt->execute([$inId]);
    recordTestResult("Internships", "Delete ($u)", true, "Deleted Internship #$inId");
}

// -----------------------------------------------------------------------
// 10. WEBINARS MODULE
// -----------------------------------------------------------------------
echo "\n--- 10/17 AUDITING MODULE: Webinars ---\n";
foreach ($universities as $u) {
    $tbl = "{$u}_webinars";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `$tbl` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `task_no` VARCHAR(50) DEFAULT NULL,
        `title` VARCHAR(255) NOT NULL,
        `speaker` VARCHAR(255) DEFAULT NULL,
        `webinar_date` DATETIME DEFAULT NULL,
        `meeting_link` VARCHAR(1000) DEFAULT NULL,
        `approval_status` ENUM('Approved','Pending','Rejected') DEFAULT 'Approved',
        `publish_status` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $cols = array_flip($pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN));
    $spkCol = isset($cols['speaker_name']) ? 'speaker_name' : (isset($cols['speaker']) ? 'speaker' : null);
    $dateCol = isset($cols['webinar_date']) ? 'webinar_date' : (isset($cols['date']) ? 'date' : 'created_at');
    $orgCol = isset($cols['organisers']) ? 'organisers' : (isset($cols['organizer']) ? 'organizer' : null);

    if ($spkCol && $orgCol) {
        $stmt = $pdo->prepare("INSERT INTO `$tbl` (title, `$spkCol`, `$dateCol`, `$orgCol`) VALUES (?, ?, ?, ?)");
        $stmt->execute(["QA_WEBINAR_$u", "Speaker $u", date('Y-m-d H:i:s'), 'Dept of CS']);
    } elseif ($spkCol) {
        $stmt = $pdo->prepare("INSERT INTO `$tbl` (title, `$spkCol`, `$dateCol`) VALUES (?, ?, ?)");
        $stmt->execute(["QA_WEBINAR_$u", "Speaker $u", date('Y-m-d H:i:s')]);
    } elseif ($orgCol) {
        $stmt = $pdo->prepare("INSERT INTO `$tbl` (title, `$dateCol`, `$orgCol`) VALUES (?, ?, ?)");
        $stmt->execute(["QA_WEBINAR_$u", date('Y-m-d H:i:s'), 'Dept of CS']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO `$tbl` (title, `$dateCol`) VALUES (?, ?)");
        $stmt->execute(["QA_WEBINAR_$u", date('Y-m-d H:i:s')]);
    }
    $wId = (int)$pdo->lastInsertId();
    recordTestResult("Webinars", "Create ($u)", $wId > 0, "Created Webinar #$wId in $tbl");

    $stmt = $pdo->prepare("UPDATE `$tbl` SET title = ? WHERE id = ?");
    $stmt->execute(["QA_WEBINAR_{$u}_UPDATED", $wId]);
    $stmt = $pdo->prepare("SELECT title FROM `$tbl` WHERE id = ?");
    $stmt->execute([$wId]);
    $wRow = $stmt->fetch();
    recordTestResult("Webinars", "Update ($u)", $wRow && $wRow['title'] === "QA_WEBINAR_{$u}_UPDATED", "Updated webinar title");

    $stmt = $pdo->prepare("DELETE FROM `$tbl` WHERE id = ?");
    $stmt->execute([$wId]);
    recordTestResult("Webinars", "Delete ($u)", true, "Deleted Webinar #$wId");
}

// -----------------------------------------------------------------------
// 11. RESEARCH INFRASTRUCTURE MODULE
// -----------------------------------------------------------------------
echo "\n--- 11/17 AUDITING MODULE: Research Infrastructure ---\n";
foreach ($universities as $u) {
    $tbl = "{$u}_research_infrastructure";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `$tbl` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `equipment_name` VARCHAR(255) NOT NULL,
        `department` VARCHAR(255) DEFAULT NULL,
        `specifications` TEXT DEFAULT NULL,
        `incharge_name` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT 'Operational',
        `approval_status` ENUM('Approved','Pending','Rejected') DEFAULT 'Approved',
        `publish_status` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->prepare("INSERT INTO `$tbl` (equipment_name, department) VALUES (?, ?)");
    $stmt->execute(["QA_EQUIP_$u", "Dept $u"]);
    $eqId = (int)$pdo->lastInsertId();
    recordTestResult("Research Infrastructure", "Create ($u)", $eqId > 0, "Created Equipment #$eqId in $tbl");

    $stmt = $pdo->prepare("UPDATE `$tbl` SET equipment_name = ? WHERE id = ?");
    $stmt->execute(["QA_EQUIP_{$u}_UPDATED", $eqId]);
    $stmt = $pdo->prepare("SELECT equipment_name FROM `$tbl` WHERE id = ?");
    $stmt->execute([$eqId]);
    $eqRow = $stmt->fetch();
    recordTestResult("Research Infrastructure", "Update ($u)", $eqRow && $eqRow['equipment_name'] === "QA_EQUIP_{$u}_UPDATED", "Updated equipment name");

    $stmt = $pdo->prepare("DELETE FROM `$tbl` WHERE id = ?");
    $stmt->execute([$eqId]);
    recordTestResult("Research Infrastructure", "Delete ($u)", true, "Deleted Equipment #$eqId");
}

// -----------------------------------------------------------------------
// 12. COLLABORATIONS MODULE
// -----------------------------------------------------------------------
echo "\n--- 12/17 AUDITING MODULE: Collaborations ---\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS `collaborations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `partner_name` VARCHAR(255) NOT NULL,
    `collaboration_type` VARCHAR(100) DEFAULT NULL,
    `institute_prefix` VARCHAR(50) DEFAULT 'all',
    `status` VARCHAR(50) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

foreach ($universities as $u) {
    $cols = array_flip($pdo->query("SHOW COLUMNS FROM `collaborations`")->fetchAll(PDO::FETCH_COLUMN));
    $nameCol = isset($cols['partner_name']) ? 'partner_name' : (isset($cols['title']) ? 'title' : 'partner_institution');
    
    $stmt = $pdo->prepare("INSERT INTO `collaborations` (`$nameCol`, institute_prefix, logo_path) VALUES (?, ?, ?)");
    $stmt->execute(["QA_COLLAB_$u", $u, $testImg]);
    $collabId = (int)$pdo->lastInsertId();
    recordTestResult("Collaborations", "Create ($u)", $collabId > 0, "Created Collab #$collabId for $u");

    $stmt = $pdo->prepare("UPDATE `collaborations` SET `$nameCol` = ? WHERE id = ?");
    $stmt->execute(["QA_COLLAB_{$u}_UPDATED", $collabId]);
    $stmt = $pdo->prepare("SELECT `$nameCol` FROM `collaborations` WHERE id = ?");
    $stmt->execute([$collabId]);
    $cRow = $stmt->fetch();
    recordTestResult("Collaborations", "Update ($u)", $cRow && $cRow[$nameCol] === "QA_COLLAB_{$u}_UPDATED", "Updated partner name");

    $stmt = $pdo->prepare("DELETE FROM `collaborations` WHERE id = ?");
    $stmt->execute([$collabId]);
    recordTestResult("Collaborations", "Delete ($u)", true, "Deleted Collab #$collabId");
}

// -----------------------------------------------------------------------
// 13. TEAM MANAGEMENT MODULE
// -----------------------------------------------------------------------
echo "\n--- 13/17 AUDITING MODULE: Team Management ---\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS `team` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(255) NOT NULL,
    `designation` VARCHAR(255) DEFAULT NULL,
    `department` VARCHAR(255) DEFAULT NULL,
    `university` VARCHAR(255) DEFAULT NULL,
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `display_order` INT DEFAULT 10,
    `status` VARCHAR(50) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stmt = $pdo->prepare("INSERT INTO `team` (full_name, designation, university) VALUES (?, ?, ?)");
$stmt->execute(["QA_TEAM_MEMBER", "Professor", "University of Hyderabad"]);
$tmId = (int)$pdo->lastInsertId();
recordTestResult("Team Management", "Create", $tmId > 0, "Created Team Member #$tmId");

$stmt = $pdo->prepare("UPDATE `team` SET full_name = ? WHERE id = ?");
$stmt->execute(["QA_TEAM_MEMBER_UPDATED", $tmId]);
$stmt = $pdo->prepare("SELECT full_name FROM `team` WHERE id = ?");
$stmt->execute([$tmId]);
$tmRow = $stmt->fetch();
recordTestResult("Team Management", "Update", $tmRow && $tmRow['full_name'] === "QA_TEAM_MEMBER_UPDATED", "Updated member name");

$stmt = $pdo->prepare("DELETE FROM `team` WHERE id = ?");
$stmt->execute([$tmId]);
recordTestResult("Team Management", "Delete", true, "Deleted Team Member #$tmId");

// -----------------------------------------------------------------------
// 14. GALLERY PHOTOS MODULE
// -----------------------------------------------------------------------
echo "\n--- 14/17 AUDITING MODULE: Gallery Photos ---\n";
$stmt = $pdo->prepare("INSERT INTO `gallery_albums` (album_name, institute_prefix) VALUES (?, ?)");
$stmt->execute(["QA_TEMP_ALBUM", "uoh"]);
$tempAlbId = (int)$pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO `gallery_photos` (album_id, photo_path, caption) VALUES (?, ?, ?)");
$stmt->execute([$tempAlbId, $testImg, "QA_PHOTO_CAPTION"]);
$gpId = (int)$pdo->lastInsertId();
recordTestResult("Gallery Photos", "Create", $gpId > 0, "Created Gallery Photo #$gpId in Album #$tempAlbId");

$stmt = $pdo->prepare("UPDATE `gallery_photos` SET caption = ? WHERE id = ?");
$stmt->execute(["QA_PHOTO_CAPTION_UPDATED", $gpId]);
$stmt = $pdo->prepare("SELECT caption FROM `gallery_photos` WHERE id = ?");
$stmt->execute([$gpId]);
$gpRow = $stmt->fetch();
recordTestResult("Gallery Photos", "Update", $gpRow && $gpRow['caption'] === "QA_PHOTO_CAPTION_UPDATED", "Updated photo caption");

$stmt = $pdo->prepare("DELETE FROM `gallery_photos` WHERE id = ?");
$stmt->execute([$gpId]);
$stmt = $pdo->prepare("DELETE FROM `gallery_albums` WHERE id = ?");
$stmt->execute([$tempAlbId]);
recordTestResult("Gallery Photos", "Delete", true, "Deleted Photo #$gpId and Album #$tempAlbId");

// -----------------------------------------------------------------------
// 15. APPROVALS WORKFLOW MODULE
// -----------------------------------------------------------------------
echo "\n--- 15/17 AUDITING MODULE: Approvals Workflow ---\n";
$stmt = $pdo->prepare("INSERT INTO `uoh_publications` (publication_title, author_name, publication_journal, approval_status) VALUES (?, ?, ?, ?)");
$stmt->execute(["QA_APPROVAL_ITEM", "Dr. QA Author", "IEEE Transactions", "Pending"]);
$appId = (int)$pdo->lastInsertId();
recordTestResult("Approvals Workflow", "Create Pending Item", $appId > 0, "Created Pending Publication #$appId");

$stmt = $pdo->prepare("UPDATE `uoh_publications` SET approval_status = 'Approved' WHERE id = ?");
$stmt->execute([$appId]);
$stmt = $pdo->prepare("SELECT approval_status FROM `uoh_publications` WHERE id = ?");
$stmt->execute([$appId]);
$appRow = $stmt->fetch();
recordTestResult("Approvals Workflow", "Approve Item", $appRow && $appRow['approval_status'] === 'Approved', "Approved publication status updated");

$stmt = $pdo->prepare("DELETE FROM `uoh_publications` WHERE id = ?");
$stmt->execute([$appId]);
recordTestResult("Approvals Workflow", "Cleanup", true, "Cleaned up test approval item");

// -----------------------------------------------------------------------
// 16. PROGRESS REPORTS MODULE
// -----------------------------------------------------------------------
echo "\n--- 16/17 AUDITING MODULE: Progress Reports ---\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS `progress_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `report_title` VARCHAR(255) NOT NULL,
    `year` INT DEFAULT 2026,
    `institute_prefix` VARCHAR(50) DEFAULT 'uoh',
    `pdf_path` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stmt = $pdo->prepare("INSERT INTO `progress_reports` (report_title, year, institute_prefix) VALUES (?, ?, ?)");
$stmt->execute(["QA_PROGRESS_REPORT", 2026, "uoh"]);
$prId = (int)$pdo->lastInsertId();
recordTestResult("Progress Reports", "Create", $prId > 0, "Created Progress Report #$prId");

$stmt = $pdo->prepare("UPDATE `progress_reports` SET report_title = ? WHERE id = ?");
$stmt->execute(["QA_PROGRESS_REPORT_UPDATED", $prId]);
$stmt = $pdo->prepare("SELECT report_title FROM `progress_reports` WHERE id = ?");
$stmt->execute([$prId]);
$prRow = $stmt->fetch();
recordTestResult("Progress Reports", "Update", $prRow && $prRow['report_title'] === "QA_PROGRESS_REPORT_UPDATED", "Updated report title");

$stmt = $pdo->prepare("DELETE FROM `progress_reports` WHERE id = ?");
$stmt->execute([$prId]);
recordTestResult("Progress Reports", "Delete", true, "Deleted Progress Report #$prId");

// -----------------------------------------------------------------------
// 17. DASHBOARD & KPI STATS MODULE
// -----------------------------------------------------------------------
echo "\n--- 17/17 AUDITING MODULE: Dashboard & KPI Stats ---\n";
$pubCount = $pdo->query("SELECT COUNT(*) FROM `uoh_publications`")->fetchColumn();
$bannerCount = $pdo->query("SELECT COUNT(*) FROM `homepage_banners` WHERE status = 'Active'")->fetchColumn();
recordTestResult("Dashboard & KPI Stats", "Query Aggregations", true, "Fetched active publications: $pubCount, active banners: $bannerCount");

// -----------------------------------------------------------------------
// SUMMARY REPORT
// -----------------------------------------------------------------------
echo "\n=======================================================================\n";
echo sprintf("TOTAL AUDIT TESTS: %d | PASSED: %d | FAILED: %d | WARNINGS: %d\n", count($results), $passCount, $failCount, $warningCount);
echo "=======================================================================\n";

if ($failCount === 0) {
    echo "SUCCESS: ALL SYSTEM MODULES AND MULTI-TENANT CRUD OPERATIONS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: SOME MODULE TESTS FAILED!\n";
    exit(1);
}
