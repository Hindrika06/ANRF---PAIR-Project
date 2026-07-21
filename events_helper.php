<?php
require_once __DIR__ . '/config.php';

/**
 * Fetch all published events ordered by event_date ASC
 */
function getAllPublishedEvents($limit = null) {
    global $pdo;
    $sql = "SELECT * FROM `events` WHERE `publish_status` = 1 ORDER BY `event_date` ASC";
    if ($limit !== null && (int)$limit > 0) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch a single event by ID
 */
function getEventById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM `events` WHERE `id` = ? AND `publish_status` = 1 LIMIT 1");
    $stmt->execute([(int)$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Fetch latest published Workshop
 */
function getFeaturedWorkshop() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM `events` WHERE `publish_status` = 1 AND `event_type` = 'Workshop' ORDER BY `event_date` DESC, `id` DESC LIMIT 1");
    $stmt->execute();
    $workshop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$workshop) {
        // Fallback to latest published event
        $stmt = $pdo->prepare("SELECT * FROM `events` WHERE `publish_status` = 1 ORDER BY `event_date` DESC, `id` DESC LIMIT 1");
        $stmt->execute();
        $workshop = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return $workshop;
}

/**
 * Fetch calendar events map (keyed by YYYY-MM-DD date strings)
 */
function getCalendarEventsMap() {
    global $pdo;
    $rows = getAllPublishedEvents();
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

    return $calendarEvents;
}
