<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Google Client Configuration
$client = new Google\Client();
$client->setApplicationName('ANRF Sheets');
$client->setAuthConfig(__DIR__ . '/../credentials.json');
$client->setScopes([
    Google\Service\Sheets::SPREADSHEETS_READONLY
]);

$service = new Google\Service\Sheets($client);

// ==========================================
// CHANGE THESE TWO VALUES
// ==========================================

// Paste your Google Spreadsheet ID here
$spreadsheetId = "100D1bWoJLJSsakrURyncYvVV-di3F9tAv92QseMSEPY";

// Replace Sheet1 if your sheet has another name
$range = "Sheet1!A1:Z1000";

// ==========================================

try {

    $response = $service->spreadsheets_values->get($spreadsheetId, $range);

    $values = $response->getValues();

    header('Content-Type: application/json');

    echo json_encode($values);

} catch (Exception $e) {
    header('Content-Type: text/plain');

    echo "ERROR\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}