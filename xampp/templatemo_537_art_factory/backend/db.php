<?php
require_once __DIR__ . '/config.php';

function get_db_connection(): mysqli {
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(['error' => 'Database connection failed']));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function send_json($payload, int $status = 200): void {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function start_session_if_needed(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function require_auth(): int {
    start_session_if_needed();
    if (!isset($_SESSION['user_id'])) {
        send_json(['error' => 'Unauthorized'], 401);
    }
    return intval($_SESSION['user_id']);
}
?>



