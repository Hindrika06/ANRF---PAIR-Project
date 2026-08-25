<?php
// Multi-Tab Server-Side Session Isolation Logic
$requestedTabToken = $_REQUEST['tab_token'] ?? $_SERVER['HTTP_X_TAB_TOKEN'] ?? null;
if (!empty($requestedTabToken) && preg_match('/^[a-f0-9]{64}$/i', $requestedTabToken)) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_id($requestedTabToken);
    session_start();
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';
require_once 'role_access.php';
global $pdo;
require_once 'vendor/fpdf/fpdf.php';

if (!function_exists('safeExportExit')) {
    function safeExportExit($msg, $code = 400) {
        http_response_code($code);
        echo $msg;
        if (PHP_SAPI === 'cli') {
            return;
        } else {
            exit();
        }
    }
}

// 1. RESOLVE & SANITIZE INPUTS
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$requestedPrefix = $_GET['prefix'] ?? null;

if (!$reportId || $reportId <= 0) {
    safeExportExit('Invalid Progress Report ID.', 400);
    if (PHP_SAPI === 'cli') return;
}

// 2. UNIVERSITY CONTEXT RESOLUTION & SECURITY GUARD
$prefix = null;
$allowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

if (!empty($requestedPrefix) && in_array($requestedPrefix, $allowedPrefixes, true)) {
    $prefix = $requestedPrefix;
} elseif (function_exists('resolveAdminPrefix') && !empty($_SESSION['username'])) {
    $prefix = resolveAdminPrefix($requestedPrefix);
}

if (!$prefix || !in_array($prefix, $allowedPrefixes, true)) {
    // Attempt auto-detecting the institute prefix across all databases for this report ID
    foreach ($allowedPrefixes as $p) {
        $chkStmt = $pdo->prepare("SELECT id FROM `{$p}_progress_reports` WHERE id = :id");
        $chkStmt->execute([':id' => $reportId]);
        if ($chkStmt->fetch()) {
            $prefix = $p;
            break;
        }
    }
}

if (!$prefix) {
    safeExportExit('Progress Report or University context not found.', 404);
    if (PHP_SAPI === 'cli') return;
}

$table      = "{$prefix}_progress_reports";
$pubsTable   = "{$prefix}_progress_report_publications";
$eventsTable = "{$prefix}_progress_report_capacity_events";

// 3. FETCH PROGRESS REPORT DATA (100% READ-ONLY)
try {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = :id");
    $stmt->execute([':id' => $reportId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        safeExportExit('Progress Report record not found.', 404);
        if (PHP_SAPI === 'cli') return;
    }

    // Fetch Publications
    $pubs = [];
    try {
        $stmtPubs = $pdo->prepare("SELECT * FROM `$pubsTable` WHERE progress_report_id = :pr_id ORDER BY id ASC");
        $stmtPubs->execute([':pr_id' => $reportId]);
        $pubs = $stmtPubs->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $ex) {
        $pubs = [];
    }

    // Fetch Capacity Events
    $workshops = [];
    $trainings = [];
    try {
        $stmtEv = $pdo->prepare("SELECT * FROM `$eventsTable` WHERE progress_report_id = :pr_id ORDER BY id ASC");
        $stmtEv->execute([':pr_id' => $reportId]);
        $events = $stmtEv->fetchAll(PDO::FETCH_ASSOC);

        foreach ($events as $ev) {
            if (($ev['category'] ?? '') === 'Workshop_Conference') {
                $workshops[] = $ev;
            } else {
                $trainings[] = $ev;
            }
        }
    } catch (Exception $ex) {
        $workshops = [];
        $trainings = [];
    }

} catch (Exception $e) {
    safeExportExit('Database query error: ' . htmlspecialchars($e->getMessage()), 500);
    if (PHP_SAPI === 'cli') return;
}

// 4. PREPARE DISPLAY METADATA
$universityFullName = getInstituteFullName($prefix);
$universityShortLabel = getInstituteLabel($prefix);

// 5. EXTENDED FPDF CLASS FOR INSTITUTIONAL REPORT DESIGN
if (!class_exists('ProgressReportPDF')) {
class ProgressReportPDF extends FPDF
{
    public $univName = '';
    public $reportTaskNo = '';

    function Header()
    {
        // Institutional Header Top Bar
        $this->SetFont('Helvetica', 'B', 15);
        $this->SetTextColor(2, 66, 131); // Sapphire Blue #024283
        $this->Cell(0, 7, 'ANRF-PAIR PROJECT', 0, 1, 'C');

        $this->SetFont('Helvetica', 'B', 12);
        $this->SetTextColor(51, 65, 85); // Slate Dark
        $this->Cell(0, 6, mb_strtoupper($this->univName, 'UTF-8'), 0, 1, 'C');

        $this->SetLineWidth(0.6);
        $this->SetDrawColor(2, 66, 131);
        $this->Line(10, $this->GetY() + 2, 200, $this->GetY() + 2);
        $this->Ln(5);

        // Document Title Banner
        $this->SetFont('Helvetica', 'B', 13);
        $this->SetFillColor(241, 245, 249);
        $this->SetTextColor(2, 66, 131);
        $this->Cell(0, 8, 'PROGRESS REPORT', 1, 1, 'C', true);
        $this->Ln(4);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(100, 116, 139);
        $this->SetDrawColor(226, 232, 240);
        $this->SetLineWidth(0.4);
        $this->Line(10, $this->GetY(), 200, $this->GetY());

        $this->SetY(-12);
        $footerLeft  = 'ANRF-PAIR Project | ' . $this->univName;
        $footerRight = 'Generated on: ' . date('d M Y, h:i A') . ' | Page ' . $this->PageNo() . ' of {nb}';

        $this->Cell(120, 6, $footerLeft, 0, 0, 'L');
        $this->Cell(70, 6, $footerRight, 0, 0, 'R');
    }

    function SectionHeader($title)
    {
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetFillColor(2, 66, 131);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 7, '  ' . mb_strtoupper($title, 'UTF-8'), 0, 1, 'L', true);
        $this->Ln(3);
    }

    function KeyValueRow($key, $value, $fill = false)
    {
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetTextColor(107, 33, 168); // Purple accent #6b21a8
        $this->SetFillColor(248, 250, 252);
        $this->Cell(50, 6, mb_strtoupper($key, 'UTF-8') . ':', 0, 0, 'L', $fill);

        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(30, 41, 59); // Normal dark text
        $displayVal = (!empty($value) || $value === '0' || $value === 0) ? strip_tags((string)$value) : 'N/A';
        $this->Cell(140, 6, $displayVal, 0, 1, 'L', $fill);
    }

    function MultiLineBlock($key, $value)
    {
        $this->SetFont('Helvetica', 'B', 9.5);
        $this->SetTextColor(107, 33, 168); // Purple accent #6b21a8
        $this->Cell(0, 6, mb_strtoupper($key, 'UTF-8') . ':', 0, 1, 'L');

        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(30, 41, 59); // Normal dark readable text
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(203, 213, 225);
        $cleanVal = strip_tags(trim((string)$value));
        $displayVal = !empty($cleanVal) ? $cleanVal : 'Not Provided';
        $this->MultiCell(190, 5.5, $displayVal, 1, 'L', true);
        $this->Ln(3);
    }

    function EmptySectionNotice($message = 'No records available for this section.')
    {
        $this->SetFont('Helvetica', 'I', 9.5);
        $this->SetTextColor(51, 65, 85);
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(203, 213, 225);
        $this->Cell(190, 8, '  ' . $message, 1, 1, 'L', true);
        $this->Ln(4);
    }
}
}

// 6. INITIALIZE PDF DOCUMENT
$pdf = new ProgressReportPDF('P', 'mm', 'A4');
if (PHP_SAPI === 'cli') {
    $pdf->SetCompression(false);
}
$pdf->univName = $universityFullName;
$pdf->reportTaskNo = $report['task_no'] ?? '';
$pdf->AliasNbPages('{nb}');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

// 7. SECTION 1: GENERAL INFORMATION
$pdf->SectionHeader('1. GENERAL INFORMATION');
$pdf->KeyValueRow('Work Package (WP) No', $report['work_package_no'] ?? $report['wp_no'] ?? '', true);
$pdf->KeyValueRow('Task Number', $report['task_no'], false);
$pdf->KeyValueRow('PI Name', $report['pi_name'], true);
$pdf->KeyValueRow('Co-PI Name', $report['co_pi_name'], false);
$pdf->Ln(4);

// 8. SECTION 2: CORE REPORT CONTENT
$pdf->SectionHeader('2. CORE REPORT CONTENT');
$pdf->MultiLineBlock('Approved Objectives', $report['approved_objects']);
$pdf->MultiLineBlock('Methodology', $report['methodology']);
$pdf->MultiLineBlock('Summary Progress', $report['summary_progress']);

// 9. SECTION 3: CAPACITY BUILDING & PUBLICATIONS
$pdf->SectionHeader('3. CAPACITY BUILDING & PUBLICATIONS');

// 3.1 Publications Submitted / Published
$pdf->SetFont('Helvetica', 'B', 9.5);
$pdf->SetTextColor(107, 33, 168);
$pdf->Cell(0, 6, '3.1 Publications Submitted / Published', 0, 1, 'L');
$pdf->Ln(1);

if (empty($pubs)) {
    $pdf->EmptySectionNotice('No publication details added yet.');
} else {
    // Table Headers (Maroon/Red)
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetFillColor(188, 33, 33);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
    $pdf->Cell(20, 6, 'Task No', 1, 0, 'C', true);
    $pdf->Cell(55, 6, 'Publication Title', 1, 0, 'L', true);
    $pdf->Cell(35, 6, 'Author(s)', 1, 0, 'L', true);
    $pdf->Cell(35, 6, 'Journal Name', 1, 0, 'L', true);
    $pdf->Cell(20, 6, 'DOI / Date', 1, 0, 'L', true);
    $pdf->Cell(15, 6, 'IF', 1, 1, 'C', true);

    $pdf->SetFont('Helvetica', '', 7.5);
    $pdf->SetTextColor(15, 23, 42);

    $sno = 1;
    foreach ($pubs as $p) {
        $pTask    = $p['task_no'] ?? 'N/A';
        $pTitle   = $p['publication_title'] ?? 'N/A';
        $pAuthor  = $p['author_name'] ?? 'N/A';
        $pJournal = $p['publication_journal'] ?? 'N/A';
        $pDoi     = (!empty($p['doi_number']) ? 'DOI: ' . $p['doi_number'] : '') . ($p['publication_date'] ? ' (' . $p['publication_date'] . ')' : '');
        if (empty(trim($pDoi))) $pDoi = 'N/A';
        $pIf      = (string)($p['impact_factor'] ?? 'N/A');

        // Check if page break is needed
        if ($pdf->GetY() > 260) {
            $pdf->AddPage();
            // Repeat Header
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->SetFillColor(188, 33, 33);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
            $pdf->Cell(20, 6, 'Task No', 1, 0, 'C', true);
            $pdf->Cell(55, 6, 'Publication Title', 1, 0, 'L', true);
            $pdf->Cell(35, 6, 'Author(s)', 1, 0, 'L', true);
            $pdf->Cell(35, 6, 'Journal Name', 1, 0, 'L', true);
            $pdf->Cell(20, 6, 'DOI / Date', 1, 0, 'L', true);
            $pdf->Cell(15, 6, 'IF', 1, 1, 'C', true);
            $pdf->SetFont('Helvetica', '', 7.5);
            $pdf->SetTextColor(15, 23, 42);
        }

        // Print row
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        $pdf->MultiCell(10, 5, (string)$sno++, 1, 'C');
        $h1 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 10, $startY);
        $pdf->MultiCell(20, 5, $pTask, 1, 'C');
        $h2 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 30, $startY);
        $pdf->MultiCell(55, 5, $pTitle, 1, 'L');
        $h3 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 85, $startY);
        $pdf->MultiCell(35, 5, $pAuthor, 1, 'L');
        $h4 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 120, $startY);
        $pdf->MultiCell(35, 5, $pJournal, 1, 'L');
        $h5 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 155, $startY);
        $pdf->MultiCell(20, 5, $pDoi, 1, 'L');
        $h6 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 175, $startY);
        $pdf->MultiCell(15, 5, $pIf, 1, 'C');
        $h7 = $pdf->GetY() - $startY;

        $maxH = max($h1, $h2, $h3, $h4, $h5, $h6, $h7);
        $pdf->SetXY($startX, $startY + $maxH);
    }
    $pdf->Ln(4);
}

// 3.2 Workshops / Conferences Conducted
$pdf->SetFont('Helvetica', 'B', 9.5);
$pdf->SetTextColor(107, 33, 168);
$pdf->Cell(0, 6, '3.2 Workshops / Conferences Conducted', 0, 1, 'L');
$pdf->Ln(1);

if (empty($workshops)) {
    $pdf->EmptySectionNotice('No workshops or conferences recorded yet.');
} else {
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetFillColor(188, 33, 33);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
    $pdf->Cell(45, 6, 'Event Title', 1, 0, 'L', true);
    $pdf->Cell(22, 6, 'Date', 1, 0, 'C', true);
    $pdf->Cell(30, 6, 'Venue / Mode', 1, 0, 'L', true);
    $pdf->Cell(38, 6, 'Organizing Inst.', 1, 0, 'L', true);
    $pdf->Cell(15, 6, 'Parts', 1, 0, 'C', true);
    $pdf->Cell(30, 6, 'Description', 1, 1, 'L', true);

    $pdf->SetFont('Helvetica', '', 7.5);
    $pdf->SetTextColor(15, 23, 42);

    $sno = 1;
    foreach ($workshops as $w) {
        $eTitle = $w['title'] ?? 'N/A';
        $eDate  = !empty($w['event_date']) ? date('d-m-Y', strtotime($w['event_date'])) : 'N/A';
        $eVenue = $w['venue_mode'] ?? 'N/A';
        $eOrg   = $w['organizing_institution'] ?? 'N/A';
        $eParts = (string)($w['participant_count'] ?? 0);
        $eDesc  = $w['description'] ?? 'N/A';

        if ($pdf->GetY() > 260) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->SetFillColor(188, 33, 33);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
            $pdf->Cell(45, 6, 'Event Title', 1, 0, 'L', true);
            $pdf->Cell(22, 6, 'Date', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Venue / Mode', 1, 0, 'L', true);
            $pdf->Cell(38, 6, 'Organizing Inst.', 1, 0, 'L', true);
            $pdf->Cell(15, 6, 'Parts', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Description', 1, 1, 'L', true);
            $pdf->SetFont('Helvetica', '', 7.5);
            $pdf->SetTextColor(15, 23, 42);
        }

        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        $pdf->MultiCell(10, 5, (string)$sno++, 1, 'C');
        $h1 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 10, $startY);
        $pdf->MultiCell(45, 5, $eTitle, 1, 'L');
        $h2 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 55, $startY);
        $pdf->MultiCell(22, 5, $eDate, 1, 'C');
        $h3 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 77, $startY);
        $pdf->MultiCell(30, 5, $eVenue, 1, 'L');
        $h4 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 107, $startY);
        $pdf->MultiCell(38, 5, $eOrg, 1, 'L');
        $h5 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 145, $startY);
        $pdf->MultiCell(15, 5, $eParts, 1, 'C');
        $h6 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 160, $startY);
        $pdf->MultiCell(30, 5, $eDesc, 1, 'L');
        $h7 = $pdf->GetY() - $startY;

        $maxH = max($h1, $h2, $h3, $h4, $h5, $h6, $h7);
        $pdf->SetXY($startX, $startY + $maxH);
    }
    $pdf->Ln(4);
}

// 3.3 Training Programs Conducted
$pdf->SetFont('Helvetica', 'B', 9.5);
$pdf->SetTextColor(107, 33, 168);
$pdf->Cell(0, 6, '3.3 Training Programs Conducted', 0, 1, 'L');
$pdf->Ln(1);

if (empty($trainings)) {
    $pdf->EmptySectionNotice('No training programs recorded yet.');
} else {
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetFillColor(188, 33, 33);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
    $pdf->Cell(50, 6, 'Program Title', 1, 0, 'L', true);
    $pdf->Cell(30, 6, 'Date / Duration', 1, 0, 'L', true);
    $pdf->Cell(35, 6, 'Venue / Mode', 1, 0, 'L', true);
    $pdf->Cell(20, 6, 'Participants', 1, 0, 'C', true);
    $pdf->Cell(45, 6, 'Description', 1, 1, 'L', true);

    $pdf->SetFont('Helvetica', '', 7.5);
    $pdf->SetTextColor(15, 23, 42);

    $sno = 1;
    foreach ($trainings as $t) {
        $tTitle = $t['title'] ?? 'N/A';
        $tDate  = (!empty($t['event_date']) ? date('d-m-Y', strtotime($t['event_date'])) : '') . ($t['duration'] ? ' (' . $t['duration'] . ')' : '');
        if (empty(trim($tDate))) $tDate = 'N/A';
        $tVenue = $t['venue_mode'] ?? 'N/A';
        $tParts = (string)($t['participant_count'] ?? 0);
        $tDesc  = $t['description'] ?? 'N/A';

        if ($pdf->GetY() > 260) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->SetFillColor(188, 33, 33);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
            $pdf->Cell(50, 6, 'Program Title', 1, 0, 'L', true);
            $pdf->Cell(30, 6, 'Date / Duration', 1, 0, 'L', true);
            $pdf->Cell(35, 6, 'Venue / Mode', 1, 0, 'L', true);
            $pdf->Cell(20, 6, 'Participants', 1, 0, 'C', true);
            $pdf->Cell(45, 6, 'Description', 1, 1, 'L', true);
            $pdf->SetFont('Helvetica', '', 7.5);
            $pdf->SetTextColor(15, 23, 42);
        }

        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        $pdf->MultiCell(10, 5, (string)$sno++, 1, 'C');
        $h1 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 10, $startY);
        $pdf->MultiCell(50, 5, $tTitle, 1, 'L');
        $h2 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 60, $startY);
        $pdf->MultiCell(30, 5, $tDate, 1, 'L');
        $h3 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 90, $startY);
        $pdf->MultiCell(35, 5, $tVenue, 1, 'L');
        $h4 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 125, $startY);
        $pdf->MultiCell(20, 5, $tParts, 1, 'C');
        $h5 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 145, $startY);
        $pdf->MultiCell(45, 5, $tDesc, 1, 'L');
        $h6 = $pdf->GetY() - $startY;

        $maxH = max($h1, $h2, $h3, $h4, $h5, $h6);
        $pdf->SetXY($startX, $startY + $maxH);
    }
    $pdf->Ln(4);
}

// 10. SECTION: INTERNS TRAINED COUNT
$pdf->SectionHeader('4. NUMBER OF INTERNS TRAINED');

$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetFillColor(243, 232, 255); // Soft Purple
$pdf->SetDrawColor(107, 33, 168);   // Purple Border
$pdf->SetTextColor(107, 33, 168);

$internsVal = isset($report['interns_trained_count']) && $report['interns_trained_count'] !== '' ? (string)$report['interns_trained_count'] : 'N/A';
$pdf->Cell(190, 10, ' Total Interns / Students Trained:  ' . $internsVal . ' ', 1, 1, 'L', true);
$pdf->Ln(6);

// 12. DYNAMIC SANITIZED FILENAME & OUTPUT
$rawTask = $report['task_no'] ?: ('REPORT_' . $reportId);
$cleanTask = preg_replace('/[^A-Za-z0-9_\-]/', '_', $rawTask);
$cleanUniv = preg_replace('/[^A-Za-z0-9_\-]/', '_', $universityShortLabel);
$filename  = "ANRF-PAIR_Progress_Report_{$cleanUniv}_{$cleanTask}.pdf";

// Output attachment PDF
if (PHP_SAPI === 'cli') {
    echo $pdf->Output('S');
} else {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    $pdf->Output('I', $filename);
    exit();
}
