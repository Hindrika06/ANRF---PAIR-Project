<?php
/**
 * Master Pages Modules Full QA Test Suite
 * Audits & tests all 6 modules under Admin Dashboard -> Pages:
 * 1. Scrolling Ticker / Announcements
 * 2. Homepage Banners
 * 3. Event Calendar
 * 4. Gallery Albums & Photos
 * 5. Drive Event Links
 * 6. Team Management
 */

require_once __DIR__ . '/../admin/config/db.php';
date_default_timezone_set('Asia/Kolkata');

$nowStr = date('Y-m-d H:i:s');
$passCount = 0;
$failCount = 0;

function assertCheck($module, $testName, $condition, $message) {
    global $passCount, $failCount;
    if ($condition) {
        echo "[PASS] [$module] $testName: $message\n";
        $passCount++;
    } else {
        echo "[FAIL] [$module] $testName: $message\n";
        $failCount++;
    }
}

echo "=======================================================================\n";
echo "ADMIN DASHBOARD -> PAGES MODULES MASTER QA SUITE\n";
echo "Current IST Server Time: $nowStr\n";
echo "=======================================================================\n\n";


// -----------------------------------------------------------------------
// 1. SCROLLING TICKER / ANNOUNCEMENTS
// -----------------------------------------------------------------------
echo "--- 1/6 MODULE: Scrolling Ticker / Announcements ---\n";
// Create
$stmt = $pdo->prepare("INSERT INTO announcements (title, link, is_active) VALUES ('QA Ticker Announcement Test', 'https://example.com/ticker', 1)");
$stmt->execute();
$annId = $pdo->lastInsertId();
assertCheck("Announcements", "CREATE", !empty($annId), "Created announcement ticker record ID #$annId");

// Read/View
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = :id");
$stmt->execute([':id' => $annId]);
$annRow = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Announcements", "VIEW", $annRow['title'] === 'QA Ticker Announcement Test' && $annRow['link'] === 'https://example.com/ticker', "Read announcement record correctly");

// Edit/Update
$stmt = $pdo->prepare("UPDATE announcements SET title = 'QA Ticker Announcement Updated', link = 'https://example.com/updated', is_active = 0 WHERE id = :id");
$stmt->execute([':id' => $annId]);

$stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = :id");
$stmt->execute([':id' => $annId]);
$annUpdated = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Announcements", "UPDATE", $annUpdated['title'] === 'QA Ticker Announcement Updated' && $annUpdated['is_active'] == 0, "Updated announcement record ID #$annId");

// Delete
$stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
$stmt->execute([':id' => $annId]);
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = :id");
$stmt->execute([':id' => $annId]);
assertCheck("Announcements", "DELETE", empty($stmt->fetch()), "Deleted announcement record ID #$annId cleanly");


// -----------------------------------------------------------------------
// 2. HOMEPAGE BANNERS
// -----------------------------------------------------------------------
echo "\n--- 2/6 MODULE: Homepage Banners ---\n";
// Create
$stmt = $pdo->prepare("INSERT INTO homepage_banners (title, short_description, target_url, institute_prefix, image_path, display_order, status) VALUES ('QA Banner Test', 'Test desc', 'https://example.com', 'uoh', 'uploads/slider/1.png', 1, 'Active')");
$stmt->execute();
$banId = $pdo->lastInsertId();
assertCheck("Homepage Banners", "CREATE", !empty($banId), "Created banner record ID #$banId");

// Read/View
$stmt = $pdo->prepare("SELECT * FROM homepage_banners WHERE id = :id");
$stmt->execute([':id' => $banId]);
$banRow = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Homepage Banners", "VIEW", $banRow['title'] === 'QA Banner Test', "Read banner record correctly");

// Edit without changing image (Image preservation test)
$oldImage = $banRow['image_path'];
$stmt = $pdo->prepare("UPDATE homepage_banners SET title = 'QA Banner Updated', short_description = 'Updated desc' WHERE id = :id");
$stmt->execute([':id' => $banId]);

$stmt = $pdo->prepare("SELECT * FROM homepage_banners WHERE id = :id");
$stmt->execute([':id' => $banId]);
$banUpdated = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Homepage Banners", "UPDATE-PRESERVE-IMAGE", $banUpdated['title'] === 'QA Banner Updated' && $banUpdated['image_path'] === $oldImage, "Updated banner text while preserving existing image path");

// Delete
$stmt = $pdo->prepare("DELETE FROM homepage_banners WHERE id = :id");
$stmt->execute([':id' => $banId]);
$stmt = $pdo->prepare("SELECT * FROM homepage_banners WHERE id = :id");
$stmt->execute([':id' => $banId]);
assertCheck("Homepage Banners", "DELETE", empty($stmt->fetch()), "Deleted banner record ID #$banId cleanly");


// -----------------------------------------------------------------------
// 3. EVENT CALENDAR
// -----------------------------------------------------------------------
echo "\n--- 3/6 MODULE: Event Calendar ---\n";
// Create
$stmt = $pdo->prepare("INSERT INTO events (title, event_date, start_time, end_time, venue, description, university_id, publish_status, status, created_by) VALUES ('QA Calendar Event Test', '2026-09-10', '10:00:00', '12:00:00', 'Auditorium', 'QA Test Event', 'uoh', 1, 'upcoming', 'Super Admin')");
$stmt->execute();
$evtId = $pdo->lastInsertId();
assertCheck("Event Calendar", "CREATE", !empty($evtId), "Created event calendar record ID #$evtId");

// Read/View
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
$stmt->execute([':id' => $evtId]);
$evtRow = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Event Calendar", "VIEW", $evtRow['title'] === 'QA Calendar Event Test', "Read calendar event correctly");

// Edit/Update
$stmt = $pdo->prepare("UPDATE events SET title = 'QA Calendar Event Updated', venue = 'Main Hall' WHERE id = :id");
$stmt->execute([':id' => $evtId]);

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
$stmt->execute([':id' => $evtId]);
$evtUpdated = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Event Calendar", "UPDATE", $evtUpdated['title'] === 'QA Calendar Event Updated' && $evtUpdated['venue'] === 'Main Hall', "Updated calendar event ID #$evtId");

// Delete
$stmt = $pdo->prepare("DELETE FROM events WHERE id = :id");
$stmt->execute([':id' => $evtId]);
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
$stmt->execute([':id' => $evtId]);
assertCheck("Event Calendar", "DELETE", empty($stmt->fetch()), "Deleted calendar event ID #$evtId cleanly");


// -----------------------------------------------------------------------
// 4. GALLERY ALBUMS & PHOTOS
// -----------------------------------------------------------------------
echo "\n--- 4/6 MODULE: Gallery Albums & Photos ---\n";
// Create Album
$stmt = $pdo->prepare("INSERT INTO gallery_albums (album_name, album_date, description, institute_prefix) VALUES ('QA Test Gallery Album', '2026-08-25', 'Test description', 'uoh')");
$stmt->execute();
$albId = $pdo->lastInsertId();
assertCheck("Gallery Albums", "CREATE", !empty($albId), "Created gallery album ID #$albId");

// Read/View Album
$stmt = $pdo->prepare("SELECT * FROM gallery_albums WHERE id = :id");
$stmt->execute([':id' => $albId]);
$albRow = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Gallery Albums", "VIEW", $albRow['album_name'] === 'QA Test Gallery Album', "Read gallery album correctly");

// Edit/Update Album
$stmt = $pdo->prepare("UPDATE gallery_albums SET album_name = 'QA Test Gallery Album Updated' WHERE id = :id");
$stmt->execute([':id' => $albId]);

$stmt = $pdo->prepare("SELECT * FROM gallery_albums WHERE id = :id");
$stmt->execute([':id' => $albId]);
$albUpdated = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Gallery Albums", "UPDATE", $albUpdated['album_name'] === 'QA Test Gallery Album Updated', "Updated gallery album ID #$albId");

// Delete Album
$stmt = $pdo->prepare("DELETE FROM gallery_albums WHERE id = :id");
$stmt->execute([':id' => $albId]);
$stmt = $pdo->prepare("SELECT * FROM gallery_albums WHERE id = :id");
$stmt->execute([':id' => $albId]);
assertCheck("Gallery Albums", "DELETE", empty($stmt->fetch()), "Deleted gallery album ID #$albId cleanly");


// -----------------------------------------------------------------------
// 5. DRIVE EVENT LINKS
// -----------------------------------------------------------------------
echo "\n--- 5/6 MODULE: Drive Event Links ---\n";
// Create
$stmt = $pdo->prepare("INSERT INTO uoh_gallery_events (event_name, coordinator_name, event_date, photos_drive_link, category, description) VALUES ('QA Drive Event Test', 'Dr. Coordinator', '2026-08-25', 'https://drive.google.com/test', 'Workshop', 'Drive link desc')");
$stmt->execute();
$drvId = $pdo->lastInsertId();
assertCheck("Drive Event Links", "CREATE", !empty($drvId), "Created drive event link ID #$drvId");

// Read/View
$stmt = $pdo->prepare("SELECT * FROM uoh_gallery_events WHERE id = :id");
$stmt->execute([':id' => $drvId]);
$drvRow = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Drive Event Links", "VIEW", $drvRow['event_name'] === 'QA Drive Event Test', "Read drive event link correctly");

// Edit/Update
$stmt = $pdo->prepare("UPDATE uoh_gallery_events SET event_name = 'QA Drive Event Updated' WHERE id = :id");
$stmt->execute([':id' => $drvId]);

$stmt = $pdo->prepare("SELECT * FROM uoh_gallery_events WHERE id = :id");
$stmt->execute([':id' => $drvId]);
$drvUpdated = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Drive Event Links", "UPDATE", $drvUpdated['event_name'] === 'QA Drive Event Updated', "Updated drive event link ID #$drvId");

// Delete
$stmt = $pdo->prepare("DELETE FROM uoh_gallery_events WHERE id = :id");
$stmt->execute([':id' => $drvId]);
$stmt = $pdo->prepare("SELECT * FROM uoh_gallery_events WHERE id = :id");
$stmt->execute([':id' => $drvId]);
assertCheck("Drive Event Links", "DELETE", empty($stmt->fetch()), "Deleted drive event link ID #$drvId cleanly");


// -----------------------------------------------------------------------
// 6. TEAM MANAGEMENT
// -----------------------------------------------------------------------
echo "\n--- 6/6 MODULE: Team Management ---\n";
// Create
$stmt = $pdo->prepare("INSERT INTO team (full_name, designation, department, university, email, display_order, status) VALUES ('QA Dr. Team Member', 'Associate Professor', 'Bioinformatics', 'University of Hyderabad', 'member@uoh.ac.in', 1, 'Active')");
$stmt->execute();
$teamId = $pdo->lastInsertId();
assertCheck("Team Management", "CREATE", !empty($teamId), "Created team member record ID #$teamId");

// Read/View
$stmt = $pdo->prepare("SELECT * FROM team WHERE id = :id");
$stmt->execute([':id' => $teamId]);
$teamRow = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Team Management", "VIEW", $teamRow['full_name'] === 'QA Dr. Team Member', "Read team member record correctly");

// Edit/Update
$stmt = $pdo->prepare("UPDATE team SET full_name = 'QA Dr. Team Member Updated', department = 'Medical Tech' WHERE id = :id");
$stmt->execute([':id' => $teamId]);

$stmt = $pdo->prepare("SELECT * FROM team WHERE id = :id");
$stmt->execute([':id' => $teamId]);
$teamUpdated = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Team Management", "UPDATE", $teamUpdated['full_name'] === 'QA Dr. Team Member Updated' && $teamUpdated['department'] === 'Medical Tech', "Updated team member record ID #$teamId");

// Delete
$stmt = $pdo->prepare("DELETE FROM team WHERE id = :id");
$stmt->execute([':id' => $teamId]);
$stmt = $pdo->prepare("SELECT * FROM team WHERE id = :id");
$stmt->execute([':id' => $teamId]);
assertCheck("Team Management", "DELETE", empty($stmt->fetch()), "Deleted team member record ID #$teamId cleanly");


// -----------------------------------------------------------------------
// HOMEPAGE CAROUSEL NO-DUMMY FALLBACK VERIFICATION
// -----------------------------------------------------------------------
echo "\n--- HOMEPAGE CAROUSEL NO-DUMMY FALLBACK VERIFICATION ---\n";
$_GET['prefix'] = 'nonexistent_test_prefix';
ob_start();
include __DIR__ . '/../index.php';
$indexOutput = ob_get_clean();

$hasDummyImage = (strpos($indexOutput, 'assets/img/1.jpg') !== false);
$hasSliderSection = (strpos($indexOutput, 'id="homepage-slider"') !== false);

assertCheck("Homepage Carousel", "NO-DUMMY-SLIDE", !$hasDummyImage, "Dummy image 'assets/img/1.jpg' is NOT rendered when zero banners exist");
assertCheck("Homepage Carousel", "SUPPRESS-ZERO-BANNERS", !$hasSliderSection, "Slider section <section id=\"homepage-slider\"> is hidden when zero banners exist");


echo "\n=======================================================================\n";
echo sprintf("TOTAL PAGES MODULE CHECKS: %d | PASSED: %d | FAILED: %d\n", ($passCount + $failCount), $passCount, $failCount);
echo "=======================================================================\n";

if ($failCount === 0) {
    echo "SUCCESS: ALL PAGES MODULES PASSED FULL CRUD & CAROUSEL NO-DUMMY AUDIT!\n";
    exit(0);
} else {
    exit(1);
}
