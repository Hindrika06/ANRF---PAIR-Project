<?php
/**
 * ANRF Publications Import Script
 * Imports publication records from extracted DOCX data into institute publication tables.
 */

// 1. Database Connection
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'anrf');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// 2. Load Extracted JSON Data
$json_path = 'C:/Users/HP/.gemini/antigravity-ide/brain/398698fd-7ef6-4b1c-88f8-855404f85bb5/scratch/extracted_publications.json';

if (!file_exists($json_path)) {
    die("Extracted publications JSON file not found at: $json_path\n");
}

$raw_publications = json_decode(file_get_contents($json_path), true);
if (!is_array($raw_publications)) {
    die("Failed to parse JSON content.\n");
}

// 3. Helper Functions
function cleanField(?string $s): string {
    if ($s === null) return '';
    $s = trim($s);
    // Remove trailing 'Digital Object Identifier (DOI)' artifact
    $s = preg_replace('/Digital Object Identifier \(DOI\)$/i', '', $s);
    return trim($s);
}

function normalizeDoi(?string $doi): string {
    $doi = cleanField($doi);
    if ($doi === '') return '';
    if (in_array(strtolower($doi), ['not found', 'under review', 'none', 'n/a', '-'], true)) {
        return '';
    }
    // Strip URL prefix
    $doi = preg_replace('/^https?:\/\/(?:dx\.)?doi\.org\//i', '', $doi);
    return strtolower(trim($doi));
}

function normalizeText(?string $t): string {
    $t = strtolower(cleanField($t));
    $t = preg_replace('/[^\w\s]/', '', $t);
    return implode(' ', preg_split('/\s+/', $t, -1, PREG_SPLIT_NO_EMPTY));
}

function parsePublicationDate(?string $dateStr): ?string {
    $clean = cleanField($dateStr);
    if ($clean === '') return null;
    
    // Check non-standard date strings
    if (in_array(strtolower($clean), ['submitted', 'to be communicated'], true)) {
        return null;
    }

    // Try parsing standard date strings (e.g., '15 July 2026', '5 Jan 2026', '2026 Apr 28')
    $ts = strtotime($clean);
    if ($ts !== false && $ts > 0) {
        return date('Y-m-d', $ts);
    }

    return null;
}

// 4. University Mapping Definition
$university_table_map = [
    'central university of karnataka' => 'cuk_publications',
    'mahatma gandhi university'       => 'mgu_publications',
    'kannur university'               => 'kannur_publications',
    'osmania university'              => 'ou_publications',
    'yogi vemana university'          => 'yvu_publications',
    'hub: university of hyderabad'    => 'uoh_publications',
    'university of hyderabad'        => 'uoh_publications'
];

// Tracking metrics
$total_extracted           = count($raw_publications);
$records_inserted          = 0;
$records_skipped_existing  = 0;
$records_skipped_source_dup= 0;
$incomplete_review_records = [];

$uni_stats = [
    'cuk_publications'    => ['found' => 0, 'inserted' => 0, 'skipped_existing' => 0, 'skipped_source_dup' => 0],
    'mgu_publications'    => ['found' => 0, 'inserted' => 0, 'skipped_existing' => 0, 'skipped_source_dup' => 0],
    'kannur_publications' => ['found' => 0, 'inserted' => 0, 'skipped_existing' => 0, 'skipped_source_dup' => 0],
    'ou_publications'     => ['found' => 0, 'inserted' => 0, 'skipped_existing' => 0, 'skipped_source_dup' => 0],
    'yvu_publications'    => ['found' => 0, 'inserted' => 0, 'skipped_existing' => 0, 'skipped_source_dup' => 0],
    'uoh_publications'    => ['found' => 0, 'inserted' => 0, 'skipped_existing' => 0, 'skipped_source_dup' => 0],
];

// Source internal deduplication trackers
$seen_source_dois = [];
$seen_source_titles = [];

echo "=======================================================\n";
echo "       STARTING ANRF PUBLICATIONS IMPORT PROCESS       \n";
echo "=======================================================\n";
echo "Total Records Extracted from DOCX: $total_extracted\n\n";

foreach ($raw_publications as $idx => $item) {
    $rec_num = $idx + 1;
    $heading = trim($item['heading_university'] ?? '');
    $data    = $item['data'] ?? [];

    // Determine target table
    $target_table = null;
    foreach ($university_table_map as $key => $table) {
        if (stripos($heading, $key) !== false) {
            $target_table = $table;
            break;
        }
    }

    if (!$target_table || !isset($uni_stats[$target_table])) {
        echo "[WARNING] Record #$rec_num: Could not map university heading '$heading'. Skipping.\n";
        continue;
    }

    $uni_stats[$target_table]['found']++;

    // Extract fields
    $task_no            = cleanField($data['Task no'] ?? '');
    $publication_title  = cleanField($data['Publication title'] ?? '');
    $author_name        = cleanField($data['Author name'] ?? '');
    $raw_doi            = cleanField($data['DOI Number'] ?? '');
    $raw_date           = cleanField($data['Publication date'] ?? '');
    $publication_journal= cleanField($data['Publication journal'] ?? '');
    $raw_impact         = cleanField($data['Impact factor'] ?? '');

    $norm_doi   = normalizeDoi($raw_doi);
    $norm_title = normalizeText($publication_title);
    $norm_auth  = normalizeText($author_name);

    $parsed_date   = parsePublicationDate($raw_date);
    $impact_factor = ($raw_impact !== '' && is_numeric($raw_impact)) ? (float)$raw_impact : null;

    // Check incomplete / manual review conditions
    $review_reasons = [];
    if ($publication_title === '') $review_reasons[] = "Missing Title";
    if ($author_name === '') $review_reasons[] = "Missing Author Name";
    if ($publication_journal === '') $review_reasons[] = "Missing Journal";
    if ($raw_date === '') $review_reasons[] = "Blank Date";
    elseif (in_array(strtolower($raw_date), ['submitted', 'to be communicated'], true)) {
        $review_reasons[] = "Non-standard Date '$raw_date'";
    }
    if ($raw_doi === '' || in_array(strtolower($raw_doi), ['not found', 'under review'], true)) {
        $review_reasons[] = "Special/Missing DOI '$raw_doi'";
    }

    if (!empty($review_reasons)) {
        $incomplete_review_records[] = [
            'rec_num' => $rec_num,
            'table'   => $target_table,
            'title'   => $publication_title,
            'reasons' => implode(', ', $review_reasons)
        ];
    }

    // 1. DEDUPLICATION WITHIN SOURCE DOCX
    $is_source_dup = false;
    if ($norm_doi !== '' && isset($seen_source_dois[$norm_doi])) {
        $is_source_dup = true;
    } else {
        $title_key = $target_table . '::' . $norm_title . '::' . $norm_auth;
        if ($norm_title !== '' && isset($seen_source_titles[$title_key])) {
            $is_source_dup = true;
        }
    }

    if ($is_source_dup) {
        $records_skipped_source_dup++;
        $uni_stats[$target_table]['skipped_source_dup']++;
        echo "[SKIP SOURCE DUP] Record #$rec_num -> $target_table: '$publication_title'\n";
        continue;
    }

    // Mark as seen in source
    if ($norm_doi !== '') $seen_source_dois[$norm_doi] = $rec_num;
    if ($norm_title !== '') $seen_source_titles[$target_table . '::' . $norm_title . '::' . $norm_auth] = $rec_num;

    // 2. DEDUPLICATION AGAINST EXISTING DATABASE
    $existing_rows = $pdo->query("SELECT id, doi_number, publication_title, author_name FROM `$target_table`")->fetchAll();
    $is_existing_db = false;

    foreach ($existing_rows as $ex_row) {
        $ex_doi   = normalizeDoi($ex_row['doi_number'] ?? '');
        $ex_title = normalizeText($ex_row['publication_title'] ?? '');
        $ex_auth  = normalizeText($ex_row['author_name'] ?? '');

        if ($norm_doi !== '' && $ex_doi !== '' && $norm_doi === $ex_doi) {
            $is_existing_db = true;
            break;
        }

        if ($norm_title !== '' && $ex_title !== '' && $norm_title === $ex_title && $norm_auth === $ex_auth) {
            $is_existing_db = true;
            break;
        }
    }

    if ($is_existing_db) {
        $records_skipped_existing++;
        $uni_stats[$target_table]['skipped_existing']++;
        echo "[SKIP DB EXISTING] Record #$rec_num -> $target_table: '$publication_title'\n";
        continue;
    }

    // 3. INSERT RECORD INTO DATABASE
    $insert_stmt = $pdo->prepare("
        INSERT INTO `$target_table`
            (task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor, approval_status, created_at)
        VALUES
            (:task_no, :publication_title, :author_name, :doi_number, :publication_date, :publication_journal, :impact_factor, 'Approved', NOW())
    ");

    $insert_stmt->execute([
        ':task_no'             => $task_no ?: null,
        ':publication_title'   => $publication_title,
        ':author_name'         => $author_name,
        ':doi_number'          => $raw_doi ?: null,
        ':publication_date'    => $parsed_date,
        ':publication_journal' => $publication_journal,
        ':impact_factor'       => $impact_factor
    ]);

    $records_inserted++;
    $uni_stats[$target_table]['inserted']++;
    echo "[INSERTED] Record #$rec_num -> $target_table (ID: " . $pdo->lastInsertId() . ")\n";
}

echo "\n=======================================================\n";
echo "                  IMPORT SUMMARY REPORT                \n";
echo "=======================================================\n";
echo sprintf("Total Records Extracted:           %d\n", $total_extracted);
echo sprintf("Total Records Inserted:            %d\n", $records_inserted);
echo sprintf("Skipped (Already Existing in DB): %d\n", $records_skipped_existing);
echo sprintf("Skipped (Duplicate within DOCX):  %d\n", $records_skipped_source_dup);
echo sprintf("Incomplete / Special Review:       %d\n\n", count($incomplete_review_records));

echo "--- UNIVERSITY STATS BREAKDOWN ---\n";
foreach ($uni_stats as $tbl => $st) {
    echo sprintf(
        "Table %-20s | Found: %2d | Inserted: %2d | Skipped DB: %2d | Skipped Source: %2d\n",
        "`$tbl`",
        $st['found'],
        $st['inserted'],
        $st['skipped_existing'],
        $st['skipped_source_dup']
    );
}

if (!empty($incomplete_review_records)) {
    echo "\n--- INCOMPLETE / MANUAL REVIEW RECORDS ---\n";
    foreach ($incomplete_review_records as $inc) {
        echo sprintf("Rec #%02d (`%s`): %s | Reasons: %s\n", $inc['rec_num'], $inc['table'], $inc['title'] ?: '(No Title)', $inc['reasons']);
    }
}
echo "=======================================================\n";
