<?php
require_once __DIR__ . '/../admin/config/db.php';
date_default_timezone_set('Asia/Kolkata');

// Check if poster already exists
$stmt = $pdo->prepare("SELECT * FROM homepage_banners WHERE image_path = 'uploads/webinars/cuk_radiomics_webinar_poster.jpg'");
$stmt->execute();
$existing = $stmt->fetch();

if (!$existing) {
    $stmt = $pdo->prepare("
        INSERT INTO homepage_banners 
        (title, short_description, target_url, start_datetime, end_datetime, institute_prefix, image_path, caption, display_order, status)
        VALUES
        (:title, :short_description, :target_url, :start_datetime, :end_datetime, :institute_prefix, :image_path, :caption, :display_order, :status)
    ");
    $stmt->execute([
        ':title' => 'ANRF-PAIR Webinar: Advanced Radiomics and Predictive Modeling (CUK)',
        ':short_description' => 'Webinar on 20th August 2026 by Prof. H. P. Rani',
        ':target_url' => 'webinars.php?prefix=cuk',
        ':start_datetime' => '2026-08-20 09:00:00',
        ':end_datetime' => '2026-08-20 18:00:00',
        ':institute_prefix' => 'cuk',
        ':image_path' => 'uploads/webinars/cuk_radiomics_webinar_poster.jpg',
        ':caption' => 'CUK Webinar Poster 20 Aug 2026',
        ':display_order' => 1,
        ':status' => 'Active'
    ]);
    $id = $pdo->lastInsertId();
    echo "SUCCESS: Inserted CUK Expired Webinar Poster Record ID #$id\n";
} else {
    // Ensure dates are set to 20 August 2026
    $stmt = $pdo->prepare("
        UPDATE homepage_banners 
        SET start_datetime = '2026-08-20 09:00:00',
            end_datetime = '2026-08-20 18:00:00',
            status = 'Active',
            institute_prefix = 'cuk'
        WHERE id = :id
    ");
    $stmt->execute([':id' => $existing['id']]);
    echo "SUCCESS: Updated CUK Webinar Poster Record ID #" . $existing['id'] . " to 20 August 2026 (EXPIRED)\n";
}
