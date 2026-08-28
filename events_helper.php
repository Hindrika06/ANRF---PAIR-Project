<?php
require_once __DIR__ . '/config.php';

date_default_timezone_set('Asia/Kolkata');

/**
 * Sync all event statuses based on current date/time (Asia/Kolkata IST)
 */
function syncAllEventStatuses($pdo = null) {
    if (!$pdo) {
        global $pdo;
    }
    if (!$pdo) return;

    date_default_timezone_set('Asia/Kolkata');
    $nowStr = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->query("SELECT id, event_date, end_date, start_time, end_time, status FROM `events`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $upd = $pdo->prepare("UPDATE `events` SET `status` = ? WHERE id = ?");

        foreach ($rows as $r) {
            $startDate = !empty($r['event_date']) ? $r['event_date'] : date('Y-m-d');
            $endDate   = (!empty($r['end_date']) && $r['end_date'] !== '0000-00-00') ? $r['end_date'] : $startDate;
            $startTime = !empty($r['start_time']) ? $r['start_time'] : '00:00:00';
            $endTime   = !empty($r['end_time']) ? $r['end_time'] : '23:59:59';

            $startDT = $startDate . ' ' . $startTime;
            $endDT   = $endDate . ' ' . $endTime;

            if ($nowStr < $startDT) {
                $newStatus = 'upcoming';
            } elseif ($nowStr > $endDT) {
                $newStatus = 'completed';
            } else {
                $newStatus = 'ongoing';
            }

            if ($r['status'] !== $newStatus) {
                $upd->execute([$newStatus, $r['id']]);
            }
        }
    } catch (Exception $e) {
        // Silently handle if database error
    }
}

/**
 * Compute real-time status string for a given event array
 */
function getEventStatus($event) {
    date_default_timezone_set('Asia/Kolkata');
    $nowStr = date('Y-m-d H:i:s');

    $startDate = !empty($event['event_date']) ? $event['event_date'] : date('Y-m-d');
    $endDate   = !empty($event['end_date']) ? $event['end_date'] : $startDate;
    $startTime = !empty($event['start_time']) ? $event['start_time'] : '00:00:00';
    $endTime   = !empty($event['end_time']) ? $event['end_time'] : '23:59:59';

    $startDateTime = $startDate . ' ' . $startTime;
    $endDateTime   = $endDate . ' ' . $endTime;

    if ($nowStr < $startDateTime) {
        return 'upcoming';
    } elseif ($nowStr > $endDateTime) {
        return 'completed';
    } else {
        return 'ongoing';
    }
}

/**
 * Fetch all published events ordered by event_date ASC
 */
function getAllPublishedEvents($limit = null) {
    global $pdo;
    syncAllEventStatuses($pdo);
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
