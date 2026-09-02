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

// STRICT SERVER-SIDE AUTHORIZATION FOR PDF EXPORT:
// Only Super Admin can export/generate the final Progress Report PDF.
if (!isSuperAdmin()) {
    safeExportExit('Unauthorized access: Progress Report PDF Export is strictly restricted to Super Admin after approval.', 403);
}

if ($requestedPrefix && strtolower($requestedPrefix) !== strtolower($prefix)) {
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

// 4. FETCH SELECTED KPI RECORDS
$pubIds   = json_decode($report['selected_publication_ids'] ?? '[]', true) ?: [];
$confIds  = json_decode($report['selected_conference_ids'] ?? '[]', true) ?: [];
$webIds   = json_decode($report['selected_webinar_ids'] ?? '[]', true) ?: [];
$intIds   = json_decode($report['selected_internship_ids'] ?? '[]', true) ?: [];
$patIds   = json_decode($report['selected_patent_ids'] ?? '[]', true) ?: [];
$wshopIds = json_decode($report['selected_workshop_ids'] ?? '[]', true) ?: [];
$trainIds = json_decode($report['selected_training_ids'] ?? '[]', true) ?: [];

$publications = getKpiCategoryRecordsByIds($pdo, $prefix, 'publications', $pubIds);
$conferences  = getKpiCategoryRecordsByIds($pdo, $prefix, 'conferences', $confIds);
$webinars     = getKpiCategoryRecordsByIds($pdo, $prefix, 'webinars', $webIds);
$internships  = getKpiCategoryRecordsByIds($pdo, $prefix, 'internships', $intIds);
$patents      = getKpiCategoryRecordsByIds($pdo, $prefix, 'patents', $patIds);
$workshops    = getKpiCategoryRecordsByIds($pdo, $prefix, 'workshops', $wshopIds);
$trainings    = getKpiCategoryRecordsByIds($pdo, $prefix, 'trainings', $trainIds);

$incWorkshops = !empty($report['include_workshops']);
$incTraining  = !empty($report['include_training']);
$incInterns   = !empty($report['include_interns']);
$internsCount = (int)($report['interns_trained_count'] ?? 0);

$instituteFullName = getInstituteFullName($prefix);

// 5. EXTEND FPDF FOR STYLED HEADER/FOOTER
class ProgressReportPDF extends FPDF {
    public $instituteName = '';
    public $taskNo = '';

    function Header() {
        $this->SetFillColor(15, 76, 129); // Premium Navy Blue
        $this->Rect(0, 0, 210, 24, 'F');
        
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 4);
        $this->Cell(0, 8, pdfTxt('ANRF-PAIR PROGRAMME — PROGRESS REPORT'), 0, 1, 'L');
        
        $this->SetFont('Helvetica', 'I', 10);
        $this->SetXY(10, 13);
        $this->Cell(0, 6, pdfTxt($this->instituteName . ($this->taskNo ? '  |  Task ID: ' . $this->taskNo : '')), 0, 1, 'L');
        
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
$pdf->taskNo = $report['task_no'] ?? '';
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

$meta = [
    'Institute'                 => $instituteFullName,
    'Project Title'             => $report['project_title'] ?? 'N/A',
    'Principal Investigator'    => $report['pi_name'] ?? 'N/A',
    'Co-Principal Investigator' => $report['co_pi_name'] ?: 'N/A',
    'Work Package'              => $report['work_package_no'] ?: 'N/A',
    'Task ID'                   => $report['task_no'] ?: 'N/A',
    'Approval Status'           => $report['approval_status'] ?? 'Pending',
    'Report Date'               => !empty($report['created_at']) ? date('d M Y', strtotime($report['created_at'])) : date('d M Y'),
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

// SECTION 1: PROGRESS DETAILS (OBJECTIVES, METHODOLOGY, SUMMARY)
if (!empty($report['approved_objects']) || !empty($report['methodology']) || !empty($report['summary_progress'])) {
    $pdf->SetFillColor(240, 244, 248);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->Cell(0, 7, pdfTxt('  PROGRESS REPORT DETAILS'), 0, 1, 'L', true);
    $pdf->Ln(2);

    if (!empty($report['approved_objects'])) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 6, pdfTxt('Approved Objectives:'), 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->MultiCell(0, 5, pdfTxt($report['approved_objects']), 0, 'L');
        $pdf->Ln(3);
    }

    if (!empty($report['methodology'])) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 6, pdfTxt('Methodology:'), 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->MultiCell(0, 5, pdfTxt($report['methodology']), 0, 'L');
        $pdf->Ln(3);
    }

    if (!empty($report['summary_progress'])) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 6, pdfTxt('Summary of Progress:'), 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->MultiCell(0, 5, pdfTxt($report['summary_progress']), 0, 'L');
        $pdf->Ln(3);
    }
    $pdf->Ln(2);
}

// SECTION 2: SELECTED PUBLICATIONS (ONLY IF SELECTED ITEMS EXIST)
if (!empty($publications)) {
    $pdf->SetFillColor(240, 244, 248);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->Cell(0, 7, pdfTxt('  PUBLICATIONS'), 0, 1, 'L', true);
    $pdf->Ln(2);

    $count = 1;
    foreach ($publications as $pub) {
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(0, 0, 0);
        $titleStr = $count . '. ' . ($pub['publication_title'] ?? 'Untitled Paper');
        $pdf->MultiCell(0, 5, pdfTxt($titleStr), 0, 'L');
        
        $details = [];
        if (!empty($pub['author_name'])) $details[] = 'Authors: ' . $pub['author_name'];
        if (!empty($pub['publication_journal'])) $details[] = 'Journal: ' . $pub['publication_journal'];
        if (!empty($pub['publication_date'])) $details[] = 'Date: ' . date('d M Y', strtotime($pub['publication_date']));
        if (!empty($pub['doi_number'])) $details[] = 'DOI: ' . $pub['doi_number'];

        if ($details) {
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetX(16);
            $pdf->MultiCell(0, 4.5, pdfTxt(implode(' | ', $details)), 0, 'L');
        }
        $pdf->Ln(2);
        $count++;
    }
    $pdf->Ln(3);
}

// SECTION 3: SELECTED CONFERENCES (ONLY IF SELECTED ITEMS EXIST)
if (!empty($conferences)) {
    $pdf->SetFillColor(240, 244, 248);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->Cell(0, 7, pdfTxt('  CONFERENCES'), 0, 1, 'L', true);
    $pdf->Ln(2);

    $count = 1;
    foreach ($conferences as $conf) {
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(0, 0, 0);
        $cTitle = $conf['title'] ?? 'Untitled Conference';
        $pdf->Cell(0, 5, pdfTxt("  {$count}. {$cTitle}"), 0, 1, 'L');

        $cOrg  = $conf['organisers'] ?? ($conf['organizer'] ?? '');
        $cDate = !empty($conf['conf_date']) ? date('d M Y', strtotime($conf['conf_date'])) : '';
        $cLoc  = $conf['institute'] ?? '';

        $cDetails = [];
        if ($cOrg) $cDetails[] = 'Organizer: ' . $cOrg;
        if ($cDate) $cDetails[] = 'Date: ' . $cDate;
        if ($cLoc) $cDetails[] = 'Venue: ' . $cLoc;

        if ($cDetails) {
            $pdf->SetFont('Helvetica', '', 8.5);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetX(16);
            $pdf->Cell(0, 4.5, pdfTxt(implode(' | ', $cDetails)), 0, 1, 'L');
        }
        $pdf->Ln(2);
        $count++;
    }
    $pdf->Ln(3);
}

// SECTION 4: SELECTED WEBINARS (ONLY IF SELECTED ITEMS EXIST)
if (!empty($webinars)) {
    $pdf->SetFillColor(240, 244, 248);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->Cell(0, 7, pdfTxt('  WEBINARS'), 0, 1, 'L', true);
    $pdf->Ln(2);

    $count = 1;
    foreach ($webinars as $web) {
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(0, 0, 0);
        $wTitle = $web['title'] ?? 'Untitled Webinar';
        $pdf->Cell(0, 5, pdfTxt("  {$count}. {$wTitle}"), 0, 1, 'L');

        $wSpeaker = $web['speaker_name'] ?? '';
        $wDate    = !empty($web['webinar_date']) ? date('d M Y', strtotime($web['webinar_date'])) : '';

        $wDetails = [];
        if ($wSpeaker) $wDetails[] = 'Speaker: ' . $wSpeaker;
        if ($wDate) $wDetails[] = 'Date: ' . $wDate;

        if ($wDetails) {
            $pdf->SetFont('Helvetica', '', 8.5);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetX(16);
            $pdf->Cell(0, 4.5, pdfTxt(implode(' | ', $wDetails)), 0, 1, 'L');
        }
        $pdf->Ln(2);
        $count++;
    }
    $pdf->Ln(3);
}

// SECTION 5: SELECTED INTERNSHIPS (ONLY IF SELECTED ITEMS EXIST)
if (!empty($internships)) {
    $pdf->SetFillColor(240, 244, 248);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->Cell(0, 7, pdfTxt('  INTERNSHIPS'), 0, 1, 'L', true);
    $pdf->Ln(2);

    $count = 1;
    foreach ($internships as $intern) {
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(0, 0, 0);
        $iTitle = $intern['title'] ?? 'Untitled Internship';
        $pdf->Cell(0, 5, pdfTxt("  {$count}. {$iTitle}"), 0, 1, 'L');

        $iPi    = $intern['project_investigator'] ?? '';
        $iCount = $intern['no_students_trained'] ?? 0;

        $iDetails = [];
        if ($iPi) $iDetails[] = 'PI: ' . $iPi;
        if ($iCount) $iDetails[] = 'Students Trained: ' . $iCount;

        if ($iDetails) {
            $pdf->SetFont('Helvetica', '', 8.5);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetX(16);
            $pdf->Cell(0, 4.5, pdfTxt(implode(' | ', $iDetails)), 0, 1, 'L');
        }
        $pdf->Ln(2);
        $count++;
    }
    $pdf->Ln(3);
}

// SECTION 6: SELECTED PATENTS (ONLY IF SELECTED ITEMS EXIST)
if (!empty($patents)) {
    $pdf->SetFillColor(240, 244, 248);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->Cell(0, 7, pdfTxt('  PATENTS'), 0, 1, 'L', true);
    $pdf->Ln(2);

    $count = 1;
    foreach ($patents as $pat) {
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(0, 0, 0);
        $pTitle = $pat['patent_title'] ?? 'Untitled Patent';
        $pdf->Cell(0, 5, pdfTxt("  {$count}. {$pTitle}"), 0, 1, 'L');

        $pInv  = $pat['inventor_name'] ?? '';
        $pNo   = $pat['patent_number'] ?? ($pat['application_number'] ?? '');
        $pStat = $pat['patent_status'] ?? '';

        $pDetails = [];
        if ($pInv) $pDetails[] = 'Inventor: ' . $pInv;
        if ($pNo) $pDetails[] = 'Patent/App No: ' . $pNo;
        if ($pStat) $pDetails[] = 'Status: ' . $pStat;

        if ($pDetails) {
            $pdf->SetFont('Helvetica', '', 8.5);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetX(16);
            $pdf->Cell(0, 4.5, pdfTxt(implode(' | ', $pDetails)), 0, 1, 'L');
        }
        $pdf->Ln(2);
        $count++;
    }
    $pdf->Ln(3);
}

// SECTION 7: CAPACITY BUILDING (ONLY IF ENABLED & HAS CONTENT)
$showCapacity = ($incWorkshops && !empty($workshops)) || ($incTraining && !empty($trainings)) || ($incInterns && $internsCount > 0);
if ($showCapacity) {
    $pdf->SetFillColor(240, 244, 248);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->Cell(0, 7, pdfTxt('  CAPACITY BUILDING'), 0, 1, 'L', true);
    $pdf->Ln(2);

    if ($incWorkshops && !empty($workshops)) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 6, pdfTxt('Workshops / Conferences Conducted:'), 0, 1, 'L');

        $wIdx = 1;
        foreach ($workshops as $w) {
            $wTitle = $w['title'] ?? 'Untitled Workshop';
            $wOrg   = $w['organisers'] ?? ($w['organizer'] ?? '');
            $wDate  = !empty($w['conf_date']) ? date('d M Y', strtotime($w['conf_date'])) : '';

            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 5, pdfTxt("    {$wIdx}. {$wTitle}"), 0, 1, 'L');

            $wDetails = [];
            if ($wOrg) $wDetails[] = 'Organizer: ' . $wOrg;
            if ($wDate) $wDetails[] = 'Date: ' . $wDate;

            if ($wDetails) {
                $pdf->SetFont('Helvetica', '', 8.5);
                $pdf->SetTextColor(80, 80, 80);
                $pdf->SetX(20);
                $pdf->Cell(0, 4.5, pdfTxt(implode(' | ', $wDetails)), 0, 1, 'L');
            }
            $wIdx++;
        }
        $pdf->Ln(3);
    }

    if ($incTraining && !empty($trainings)) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 6, pdfTxt('Training Programs Conducted:'), 0, 1, 'L');

        $tIdx = 1;
        foreach ($trainings as $t) {
            $tTitle   = $t['title'] ?? 'Untitled Training Program';
            $tSpeaker = $t['speaker_name'] ?? '';
            $tDate    = !empty($t['webinar_date']) ? date('d M Y', strtotime($t['webinar_date'])) : '';

            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 5, pdfTxt("    {$tIdx}. {$tTitle}"), 0, 1, 'L');

            $tDetails = [];
            if ($tSpeaker) $tDetails[] = 'Speaker: ' . $tSpeaker;
            if ($tDate) $tDetails[] = 'Date: ' . $tDate;

            if ($tDetails) {
                $pdf->SetFont('Helvetica', '', 8.5);
                $pdf->SetTextColor(80, 80, 80);
                $pdf->SetX(20);
                $pdf->Cell(0, 4.5, pdfTxt(implode(' | ', $tDetails)), 0, 1, 'L');
            }
            $tIdx++;
        }
        $pdf->Ln(3);
    }

    if ($incInterns && $internsCount > 0) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 6, pdfTxt('Number of Interns Trained:'), 0, 1, 'L');

        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(15, 76, 129);
        $pdf->Cell(0, 6, pdfTxt("    Total Interns Trained: {$internsCount}"), 0, 1, 'L');
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
$filename = 'Progress_Report_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $prefix . '_Report_' . $reportId) . '.pdf';

if (PHP_SAPI !== 'cli') {
    if (ob_get_length()) {
        ob_end_clean();
    }
    $pdf->Output('I', $filename);
    exit();
} else {
    echo $pdf->Output('S');
}
