<?php
require_once 'auth_check.php';
require_once 'role_access.php';
require_once 'config/db.php';
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
// resolveAdminPrefix guarantees Spoke Admins are strictly bound to their session institute_prefix.
$prefix = resolveAdminPrefix($requestedPrefix);

if (!isValidPrefix($prefix) || $prefix === 'all') {
    // If Hub Admin didn't specify a valid single institute prefix, attempt auto-detecting the institute for this report ID
    if (isSuperAdmin()) {
        $foundPrefix = null;
        $allowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
        foreach ($allowedPrefixes as $p) {
            $chkStmt = $pdo->prepare("SELECT id FROM `{$p}_progress_reports` WHERE id = :id");
            $chkStmt->execute([':id' => $reportId]);
            if ($chkStmt->fetch()) {
                $foundPrefix = $p;
                break;
            }
        }
        if ($foundPrefix) {
            $prefix = $foundPrefix;
        } else {
            safeExportExit('Progress Report not found in any institute registry.', 404);
            if (PHP_SAPI === 'cli') return;
        }
    } else {
        safeExportExit('Unauthorized institute context.', 403);
        if (PHP_SAPI === 'cli') return;
    }
}

// Enforce institute boundary permission
if (!canEditInstitute($prefix) && !isSuperAdmin()) {
    safeExportExit('Access Denied: You do not have permission to view progress reports for this institute.', 403);
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
        $this->SetTextColor(51, 65, 85);
        $this->SetFillColor(248, 250, 252);
        $this->Cell(50, 6, $key . ':', 0, 0, 'L', $fill);

        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(15, 23, 42);
        $displayVal = (!empty($value) || $value === '0' || $value === 0) ? strip_tags((string)$value) : 'N/A';
        $this->Cell(140, 6, $displayVal, 0, 1, 'L', $fill);
    }

    function MultiLineBlock($key, $value)
    {
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetTextColor(2, 66, 131);
        $this->Cell(0, 6, $key . ':', 0, 1, 'L');

        $this->SetFont('Helvetica', '', 8.5);
        $this->SetTextColor(30, 41, 59);
        $this->SetFillColor(248, 250, 252);
        $cleanVal = strip_tags(trim((string)$value));
        $displayVal = !empty($cleanVal) ? $cleanVal : 'Not Provided';
        $this->MultiCell(190, 5, $displayVal, 1, 'L', true);
        $this->Ln(3);
    }

    function EmptySectionNotice($message = 'No records available for this section.')
    {
        $this->SetFont('Helvetica', 'I', 9);
        $this->SetTextColor(100, 116, 139);
        $this->SetFillColor(248, 250, 252);
        $this->Cell(190, 8, $message, 1, 1, 'C', true);
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

// 7. CORE PROGRESS REPORT METADATA & MAIN INFO
$pdf->SectionHeader('1. PROGRESS REPORT DETAILS');

$pdf->KeyValueRow('University / Institute', $universityFullName, true);
$pdf->KeyValueRow('Task Number', $report['task_no'] ?: 'TASK-UNASSIGNED', false);
$pdf->KeyValueRow('Project Title', $report['project_title'] ?: 'Untitled Project', true);
$pdf->KeyValueRow('Principal Investigator (PI)', $report['pi_name'] ?: 'Not Specified', false);
$pdf->KeyValueRow('Co-Principal Investigator', $report['co_pi_name'] ?: 'N/A', true);
$pdf->KeyValueRow('Work Package Number', $report['work_package_no'] ?: 'N/A', false);
$pdf->KeyValueRow('Approval Status', $report['approval_status'] ?: 'Approved', true);
$pdf->KeyValueRow('Report Submission Date', !empty($report['created_at']) ? date('d F Y, h:i A', strtotime($report['created_at'])) : 'N/A', false);
$pdf->Ln(4);

// Text Blocks: Objectives, Methodology, Summary Progress
$pdf->MultiLineBlock('Approved Objectives / Targets', $report['approved_objects'] ?? '');
$pdf->MultiLineBlock('Methodology / Approach Used', $report['methodology'] ?? '');
$pdf->MultiLineBlock('Summary of Progress Made', $report['summary_progress'] ?? '');

// 8. SECTION: PUBLICATION DETAILS
$pdf->SectionHeader('2. PUBLICATION DETAILS');

if (empty($pubs)) {
    $pdf->EmptySectionNotice('No publication records available.');
} else {
    // Table Headers
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetFillColor(2, 66, 131);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
    $pdf->Cell(55, 6, 'Publication Title', 1, 0, 'L', true);
    $pdf->Cell(35, 6, 'Authors', 1, 0, 'L', true);
    $pdf->Cell(35, 6, 'Journal', 1, 0, 'L', true);
    $pdf->Cell(20, 6, 'Date', 1, 0, 'C', true);
    $pdf->Cell(20, 6, 'DOI', 1, 0, 'L', true);
    $pdf->Cell(15, 6, 'IF', 1, 1, 'C', true);

    $pdf->SetFont('Helvetica', '', 7.5);
    $pdf->SetTextColor(15, 23, 42);

    $sno = 1;
    foreach ($pubs as $p) {
        // Calculate max height for multiline cells
        $title   = strip_tags($p['publication_title'] ?? 'N/A');
        $authors = strip_tags($p['author_name'] ?? 'N/A');
        $journal = strip_tags($p['publication_journal'] ?? 'N/A');
        $pubDate = !empty($p['publication_date']) ? date('d-m-Y', strtotime($p['publication_date'])) : 'N/A';
        $doi     = strip_tags($p['doi_number'] ?? 'N/A');
        $if      = ($p['impact_factor'] !== null && $p['impact_factor'] !== '') ? (string)$p['impact_factor'] : 'N/A';

        // Check if page break is needed
        if ($pdf->GetY() > 260) {
            $pdf->AddPage();
            // Repeat Header
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->SetFillColor(2, 66, 131);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
            $pdf->Cell(55, 6, 'Publication Title', 1, 0, 'L', true);
            $pdf->Cell(35, 6, 'Authors', 1, 0, 'L', true);
            $pdf->Cell(35, 6, 'Journal', 1, 0, 'L', true);
            $pdf->Cell(20, 6, 'Date', 1, 0, 'C', true);
            $pdf->Cell(20, 6, 'DOI', 1, 0, 'L', true);
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
        $pdf->MultiCell(55, 5, $title, 1, 'L');
        $h2 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 65, $startY);
        $pdf->MultiCell(35, 5, $authors, 1, 'L');
        $h3 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 100, $startY);
        $pdf->MultiCell(35, 5, $journal, 1, 'L');
        $h4 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 135, $startY);
        $pdf->MultiCell(20, 5, $pubDate, 1, 'C');
        $h5 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 155, $startY);
        $pdf->MultiCell(20, 5, $doi, 1, 'L');
        $h6 = $pdf->GetY() - $startY;

        $pdf->SetXY($startX + 175, $startY);
        $pdf->MultiCell(15, 5, $if, 1, 'C');
        $h7 = $pdf->GetY() - $startY;

        $maxH = max($h1, $h2, $h3, $h4, $h5, $h6, $h7);
        $pdf->SetXY($startX, $startY + $maxH);
    }
    $pdf->Ln(4);
}

// 9. SECTION: CAPACITY BUILDING
$pdf->SectionHeader('3. CAPACITY BUILDING');

// 3.1 Workshops / Conferences Conducted
$pdf->SetFont('Helvetica', 'B', 9.5);
$pdf->SetTextColor(2, 66, 131);
$pdf->Cell(0, 6, '3.1 Workshops / Conferences Conducted', 0, 1, 'L');
$pdf->Ln(1);

if (empty($workshops)) {
    $pdf->EmptySectionNotice('No workshop/conference records available.');
} else {
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetFillColor(2, 66, 131);
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
            $pdf->SetFillColor(2, 66, 131);
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

// 3.2 Training Programs Conducted
$pdf->SetFont('Helvetica', 'B', 9.5);
$pdf->SetTextColor(2, 66, 131);
$pdf->Cell(0, 6, '3.2 Training Programs Conducted', 0, 1, 'L');
$pdf->Ln(1);

if (empty($trainings)) {
    $pdf->EmptySectionNotice('No training program records available.');
} else {
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetFillColor(2, 66, 131);
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
            $pdf->SetFillColor(2, 66, 131);
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
$pdf->SetFillColor(240, 253, 244); // Soft Green
$pdf->SetDrawColor(34, 197, 94);   // Green Border
$pdf->SetTextColor(21, 128, 61);

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
    $pdf->Output('D', $filename);
    exit();
}
