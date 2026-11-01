<?php

function schedule_time_minutes(string $timeToken): ?int
{
    if (!preg_match('/^\s*(\d{1,2})(?::(\d{2}))?\s*(AM|PM)?\s*$/i', $timeToken, $match)) {
        return null;
    }

    $hour = (int) $match[1];
    $minute = isset($match[2]) && $match[2] !== '' ? (int) $match[2] : 0;
    $meridiem = strtoupper($match[3] ?? '');
    if ($minute > 59) return null;
    if ($meridiem !== '') {
        if ($hour < 1 || $hour > 12) return null;
        $hour = ($hour % 12) + ($meridiem === 'PM' ? 12 : 0);
    } elseif ($hour > 23) {
        return null;
    }

    return $hour * 60 + $minute;
}

function schedule_window_contains(string $scheduleText, int $currentSeconds): bool
{
    preg_match_all('/(?<!\d)(\d{1,2}(?::\d{2})?\s*(?:AM|PM)?)(?![A-Za-z])/i', $scheduleText, $matches);
    if (count($matches[1] ?? []) < 2) return false;

    $start = schedule_time_minutes($matches[1][0]);
    $end = schedule_time_minutes($matches[1][1]);
    if ($start === null || $end === null) return false;
    $startSeconds = $start * 60;
    $endSeconds = $end * 60;
    if ($startSeconds <= $endSeconds) return $currentSeconds >= $startSeconds && $currentSeconds <= $endSeconds;

    return $currentSeconds >= $startSeconds || $currentSeconds <= $endSeconds;
}

function collector_duty(PDO $pdo, string $username, ?DateTimeImmutable $now = null): ?array
{
    $now = $now ?? new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
    $assignmentQuery = $pdo->prepare(
        "SELECT d.id AS driver_id, d.route_id, r.name AS route_name, r.barangay,
                v.vehicle_number, s.day_name, s.collection_time
         FROM users u
         JOIN drivers d ON d.user_id = u.id AND d.status = 'active'
         JOIN routes r ON r.id = d.route_id AND r.status = 'active'
         JOIN vehicles v ON v.id = d.vehicle_id AND v.status = 'active'
         JOIN schedule s ON s.route_id = d.route_id AND s.status = 'active'
         WHERE u.username = :username AND u.role = 'collector' AND u.status = 'active'
           AND s.day_name = :day_name"
    );
    $assignmentQuery->execute([
        ':username' => $username,
        ':day_name' => $now->format('l')
    ]);
    $currentSeconds = ((int) $now->format('G')) * 3600 + ((int) $now->format('i')) * 60 + (int) $now->format('s');
    foreach ($assignmentQuery->fetchAll() as $assignment) {
        if (schedule_window_contains((string) $assignment['collection_time'], $currentSeconds)) {
            return $assignment;
        }
    }

    return null;
}

