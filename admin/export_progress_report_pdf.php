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

require_once 'auth_check.php';
require_once 'config/db.php';
require_once 'role_access.php';
require_once 'includes/progress_reports_helper.php';
require_once 'vendor/fpdf/fpdf.php';

global $pdo;

if (!function_exists('safeExportExit')) {
    function safeExportExit($msg, $code = 400) {
        http_response_code($code);
        echo "<div style='font-family:sans-serif; padding:30px; max-width:600px; margin:50px auto; border:1px solid #ebccd1; background:#f2dede; color:#a94442; border-radius:4px;'>
                <h3 style='margin-top:0;'>Export Error</h3>
                <p>" . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . "</p>
                <p><a href='javascript:history.back()' style='color:#843534; font-weight:bold;'>&laquo; Go Back</a></p>
              </div>";
        if (PHP_SAPI === 'cli') {
            return;
        } else {
            exit();
        }
    }
}

// Helper to convert UTF-8 strings to ISO-8859-1 safely for FPDF
function pdfTxt($str) {
    if ($str === null || $str === '') return '';
    $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$str);
    return $converted !== false ? $converted : utf8_decode((string)$str);
}

// 1. RESOLVE & SANITIZE INPUTS
$reportId        = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$requestedPrefix = $_GET['prefix'] ?? null;

if (!$reportId || $reportId <= 0) {
    safeExportExit('Invalid Progress Report ID.', 400);
}

// 2. UNIVERSITY CONTEXT RESOLUTION & SECURITY GUARD
$prefix = resolveAdminPrefix($requestedPrefix);
$allowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

if ($requestedPrefix && !isSuperAdmin() && strtolower($requestedPrefix) !== strtolower($prefix)) {
    safeExportExit('Unauthorized access: Cross-institute export is forbidden.', 403);
}

if (!$prefix || !in_array($prefix, $allowedPrefixes, true) || !canEditInstitute($prefix)) {
    safeExportExit('Unauthorized access: You are not permitted to export progress reports for this institute.', 403);
}

ensureProgressReportsSchema($pdo);

$table = "{$prefix}_progress_reports";

// 3. FETCH PROGRESS REPORT DATA
try {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = :id");
    $stmt->execute([':id' => $reportId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        safeExportExit('Progress Report not found.', 404);
    }
} catch (Exception $e) {
    safeExportExit('Database query failed: ' . $e->getMessage(), 500);
}

// 4. FETCH KPI DATA (SINGLE SOURCE OF TRUTH) FOR REPORT'S TASK ID
$taskId       = $report['task_no'] ?? '';
$publications = getKpiPublicationsForTask($pdo, $prefix, $taskId);
$conferences  = getKpiConferencesForTask($pdo, $prefix, $taskId);
$webinars     = getKpiWebinarsForTask($pdo, $prefix, $taskId);
$internsCount = getKpiInternCountForTask($pdo, $prefix, $taskId);

// Section Toggles
$incPubs     = !empty($report['include_publications']);
$incCapacity = !empty($report['include_capacity_building']);
$incWorkshops= $incCapacity && !empty($report['include_workshops']);
$incTraining = $incCapacity && !empty($report['include_training']);
$incInterns  = $incCapacity && !empty($report['include_interns']);

$instituteFullName = getInstituteFullName($prefix);

// 5. EXTEND FPDF FOR STYLED HEADER/FOOTER
class ProgressReportPDF extends FPDF {
    public $instituteName = '';
    public $taskNo = '';

    function Header() {
        // Banner
        $this->SetFillColor(15, 76, 129); // Premium Navy Blue
        $this->Rect(0, 0, 210, 24, 'F');
        
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 4);
        $this->Cell(0, 8, pdfTxt('ANRF-PAIR PROGRAMME — PROGRESS REPORT'), 0, 1, 'L');
        
        $this->SetFont('Helvetica', 'I', 10);
        $this->SetXY(10, 13);
        $this->Cell(0, 6, pdfTxt($this->instituteName . '  |  Task ID: ' . $this->taskNo), 0, 1, 'L');
        
        $this->Ln(8);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, pdfTxt('ANRF-PAIR Official Report  |  Generated on ' . date('d M Y, h:i A') . '  |  Page ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
    }
}

// 6. GENERATE PDF DOCUMENT
$pdf = new ProgressReportPDF('P', 'mm', 'A4');
$pdf->instituteName = $instituteFullName;
$pdf->taskNo = $taskId;
$pdf->AliasNbPages();
$pdf->SetMargins(12, 12, 12);
$pdf->AddPage();

// Document Title Box
$pdf->SetFont('Helvetica', 'B', 16);
$pdf->SetTextColor(15, 76, 129);
$pdf->Cell(0, 10, pdfTxt('PROJECT PROGRESS REPORT'), 0, 1, 'C');
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
$pdf->Ln(4);

// SECTION: PROJECT INFORMATION
$pdf->SetFillColor(240, 244, 248);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->SetTextColor(15, 76, 129);
$pdf->Cell(0, 7, pdfTxt('  PROJECT INFORMATION'), 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(40, 40, 40);

// Two-Column Meta Table
$meta = [
    'Institute'                => $instituteFullName,
    'Task ID'                  => $taskId,
    'Project Title'            => $report['project_title'] ?? 'N/A',
    'Principal Investigator'   => $report['pi_name'] ?? 'N/A',
    'Co-Principal Investigator' => $report['co_pi_name'] ?: 'N/A',
    'Work Package'             => $report['work_package_no'] ?: 'N/A',
    'Approval Status'          => $report['approval_status'] ?? 'Pending',
    'Report Date'              => !empty($report['created_at']) ? date('d M Y', strtotime($report['created_at'])) : date('d M Y'),
];

foreach ($meta as $label => $val) {
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->Cell(50, 6, pdfTxt($label . ':'), 0, 0, 'L');
    $pdf->SetFont('Helvetica', '', 9.5);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 6, pdfTxt($val), 0, 'L');
}
$pdf->Ln(4);

// SECTION 1: PROGRESS REPORT NARRATIVE
$pdf->SetFillColor(240, 244, 248);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->SetTextColor(15, 76, 129);
$pdf->Cell(0, 7, pdfTxt('  1. PROGRESS REPORT DETAILS'), 0, 1, 'L', true);
$pdf->Ln(2);

// Approved Objectives
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(0, 6, pdfTxt('Approved Objectives:'), 0, 1, 'L');
$pdf->SetFont('Helvetica', '', 9.5);
$pdf->SetTextColor(60, 60, 60);
$objText = trim($report['approved_objects'] ?? '') ?: 'None specified.';
$pdf->MultiCell(0, 5, pdfTxt($objText), 0, 'L');
$pdf->Ln(3);

// Methodology
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(0, 6, pdfTxt('Methodology:'), 0, 1, 'L');
$pdf->SetFont('Helvetica', '', 9.5);
$pdf->SetTextColor(60, 60, 60);
$methText = trim($report['methodology'] ?? '') ?: 'None specified.';
$pdf->MultiCell(0, 5, pdfTxt($methText), 0, 'L');
$pdf->Ln(3);

// Summary of Progress
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(0, 6, pdfTxt('Summary of Progress:'), 0, 1, 'L');
$pdf->SetFont('Helvetica', '', 9.5);
$pdf->SetTextColor(60, 60, 60);
$sumText = trim($report['summary_progress'] ?? '') ?: 'None specified.';
$pdf->MultiCell(0, 5, pdfTxt($sumText), 0, 'L');
$pdf->Ln(5);

// SECTION 2: PUBLICATIONS (ONLY IF ENABLED)
if ($incPubs) {
    $pdf->SetFillColor(240, 244, 248);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->Cell(0, 7, pdfTxt('  2. PUBLICATIONS (Derived from KPI Module)'), 0, 1, 'L', true);
    $pdf->Ln(2);

    if (empty($publications)) {
        $pdf->SetFont('Helvetica', 'I', 9.5);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 6, pdfTxt('No publication KPI records found for Task ID: ' . $taskId), 0, 1, 'L');
    } else {
        $count = 1;
        foreach ($publications as $pub) {
            $pdf->SetFont('Helvetica', 'B', 9.5);
            $pdf->SetTextColor(0, 0, 0);
            $titleStr = $count . '. ' . ($pub['publication_title'] ?? 'Untitled Paper');
            $pdf->MultiCell(0, 5, pdfTxt($titleStr), 0, 'L');
            
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(80, 80, 80);
            $details = [];
            if (!empty($pub['author_name'])) $details[] = 'Authors: ' . $pub['author_name'];
            if (!empty($pub['publication_journal'])) $details[] = 'Journal: ' . $pub['publication_journal'];
            if (!empty($pub['publication_date'])) $details[] = 'Date: ' . date('d M Y', strtotime($pub['publication_date']));
            if (!empty($pub['doi_number'])) $details[] = 'DOI: ' . $pub['doi_number'];
            if (!empty($pub['impact_factor'])) $details[] = 'IF: ' . $pub['impact_factor'];

            if ($details) {
                $pdf->SetX(16);
                $pdf->MultiCell(0, 4.5, pdfTxt(implode(' | ', $details)), 0, 'L');
            }
            $pdf->Ln(2);
            $count++;
        }
    }
    $pdf->Ln(3);
}

// SECTION 3: CAPACITY BUILDING (ONLY IF ENABLED)
if ($incCapacity) {
    $pdf->SetFillColor(240, 244, 248);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->Cell(0, 7, pdfTxt('  3. CAPACITY BUILDING'), 0, 1, 'L', true);
    $pdf->Ln(2);

    // 3.1 Workshops / Conferences Conducted
    if ($incWorkshops) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 6, pdfTxt('3.1 Workshops / Conferences Conducted:'), 0, 1, 'L');

        if (!empty($report['workshops_narrative'])) {
            $pdf->SetFont('Helvetica', '', 9.5);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->MultiCell(0, 5, pdfTxt($report['workshops_narrative']), 0, 'L');
            $pdf->Ln(2);
        }

        if (empty($conferences)) {
            $pdf->SetFont('Helvetica', 'I', 9);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, pdfTxt('No conference KPI records found for Task ID: ' . $taskId), 0, 1, 'L');
        } else {
            $cIdx = 1;
            foreach ($conferences as $conf) {
                $cTitle = $conf['title'] ?? 'Untitled Conference';
                $cOrg   = $conf['organizer'] ?? ($conf['organisers'] ?? '');
                $cDate  = !empty($conf['start_date']) ? date('d M Y', strtotime($conf['start_date'])) : (!empty($conf['conf_date']) ? date('d M Y', strtotime($conf['conf_date'])) : '');
                $cLoc   = $conf['location'] ?? '';

                $pdf->SetFont('Helvetica', 'B', 9);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(0, 5, pdfTxt("    {$cIdx}. {$cTitle}"), 0, 1, 'L');

                $cDetails = [];
                if ($cOrg) $cDetails[] = 'Organizer: ' . $cOrg;
                if ($cDate) $cDetails[] = 'Date: ' . $cDate;
                if ($cLoc) $cDetails[] = 'Location: ' . $cLoc;

                if ($cDetails) {
                    $pdf->SetFont('Helvetica', '', 8.5);
                    $pdf->SetTextColor(80, 80, 80);
                    $pdf->SetX(20);
                    $pdf->Cell(0, 4.5, pdfTxt(implode(' | ', $cDetails)), 0, 1, 'L');
                }
                $cIdx++;
            }
        }
        $pdf->Ln(3);
    }

    // 3.2 Training Programs Conducted
    if ($incTraining) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 6, pdfTxt('3.2 Training Programs Conducted:'), 0, 1, 'L');

        if (!empty($report['training_narrative'])) {
            $pdf->SetFont('Helvetica', '', 9.5);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->MultiCell(0, 5, pdfTxt($report['training_narrative']), 0, 'L');
            $pdf->Ln(2);
        }

        if (empty($webinars)) {
            $pdf->SetFont('Helvetica', 'I', 9);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, pdfTxt('No training/webinar KPI records found for Task ID: ' . $taskId), 0, 1, 'L');
        } else {
            $wIdx = 1;
            foreach ($webinars as $web) {
                $wTitle   = $web['title'] ?? 'Untitled Training';
                $wSpeaker = $web['speaker_name'] ?? '';
                $wDate    = !empty($web['webinar_date']) ? date('d M Y', strtotime($web['webinar_date'])) : '';

                $pdf->SetFont('Helvetica', 'B', 9);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(0, 5, pdfTxt("    {$wIdx}. {$wTitle}"), 0, 1, 'L');

                $wDetails = [];
                if ($wSpeaker) $wDetails[] = 'Speaker: ' . $wSpeaker;
                if ($wDate) $wDetails[] = 'Date: ' . $wDate;

                if ($wDetails) {
                    $pdf->SetFont('Helvetica', '', 8.5);
                    $pdf->SetTextColor(80, 80, 80);
                    $pdf->SetX(20);
                    $pdf->Cell(0, 4.5, pdfTxt(implode(' | ', $wDetails)), 0, 1, 'L');
                }
                $wIdx++;
            }
        }
        $pdf->Ln(3);
    }

    // 3.3 Number of Interns Trained
    if ($incInterns) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 6, pdfTxt('3.3 Number of Interns Trained:'), 0, 1, 'L');

        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(15, 76, 129);
        $pdf->Cell(0, 6, pdfTxt("    Total Interns Trained: {$internsCount}  (Derived from Internship KPI records)"), 0, 1, 'L');
        $pdf->Ln(3);
    }
}

// SECTION: SIGNATURE & APPROVAL
$pdf->Ln(4);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
$pdf->Ln(4);

$pdf->SetFont('Helvetica', 'B', 9.5);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(93, 5, pdfTxt('Prepared By: Principal Investigator'), 0, 0, 'L');
$filename = 'Progress_Report_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $prefix . '_Task_' . $taskId) . '.pdf';

if (PHP_SAPI !== 'cli') {
    if (ob_get_length()) {
        ob_end_clean();
    }
    $pdf->Output('I', $filename);
    exit();
} else {
    echo $pdf->Output('S');
}
