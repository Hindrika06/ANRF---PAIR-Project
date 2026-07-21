<?php
// Start session to detect logged-in admins for visibility permission checks
session_start();

// Include database configuration
require_once 'admin/config/db.php';
require_once 'admin/role_access.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $where_clauses = ["publish_status = 1"];
    $params = [];

    // Filter by visibility/permissions
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
        // Super Admin sees all published events (both public and university_only)
    } elseif (isset($_SESSION['institute_prefix'])) {
        // Logged-in regular admin sees public events, plus university_only events for their own university
        $where_clauses[] = "(visibility = 'public' OR (visibility = 'university_only' AND university_id = :user_prefix))";
        $params[':user_prefix'] = $_SESSION['institute_prefix'];
    } else {
        // Unauthenticated visitor sees ONLY public visibility events
        $where_clauses[] = "visibility = 'public'";
    }

    $where_sql = implode(' AND ', $where_clauses);
    $sql = "SELECT * FROM `events` WHERE $where_sql ORDER BY event_date ASC, start_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group events by date format YYYY-MM-DD (supporting multi-day events)
    $calendarEvents = [];
    foreach ($rows as $row) {
        $start = date("g:i A", strtotime($row['start_time']));
        $end = date("g:i A", strtotime($row['end_time']));
        $timeStr = "{$start} - {$end}";

        $coordinator = !empty($row['coordinator']) ? $row['coordinator'] : $row['created_by'];

        $eventObj = [
            'id'          => (int)$row['id'],
            'title'       => $row['title'],
            'time'        => $timeStr,
            'venue'       => $row['venue'],
            'coordinator' => $coordinator
        ];

        $startDate = $row['event_date'];
        $endDate = !empty($row['end_date']) ? $row['end_date'] : $startDate;

        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);

        for ($ts = $startTs; $ts <= $endTs; $ts += 86400) {
            $dateKey = date('Y-m-d', $ts);
            if (!isset($calendarEvents[$dateKey])) {
                $calendarEvents[$dateKey] = [];
            }
            $calendarEvents[$dateKey][] = $eventObj;
        }
    }

    echo json_encode($calendarEvents, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
