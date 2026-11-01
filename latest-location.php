<?php
require 'config.php';
require_once 'tracking.php';
header('Content-Type: application/json');
header('Cache-Control: no-store, max-age=0');

$candidates = $pdo->query(
    "SELECT g.collector_username, g.latitude, g.longitude, g.accuracy,
            DATE_FORMAT(g.recorded_at, '%Y-%m-%dT%H:%i:%sZ') AS recorded_at,
            g.recorded_at AS recorded_at_utc
     FROM gps_locations g
     JOIN (
         SELECT collector_username, MAX(id) AS latest_id
         FROM gps_locations
         WHERE recorded_at >= UTC_TIMESTAMP() - INTERVAL 60 SECOND
         GROUP BY collector_username
     ) latest ON latest.latest_id = g.id
     ORDER BY g.recorded_at DESC
     LIMIT 100"
)->fetchAll();

foreach ($candidates as $location) {
    $duty = collector_duty($pdo, (string) $location['collector_username']);
    if ($duty === null) continue;

    $pointQuery = $pdo->prepare(
        'SELECT point_name, latitude, longitude FROM route_points WHERE route_id = ? ORDER BY point_order'
    );
    $pointQuery->execute([$duty['route_id']]);
    $routePoints = array_map(static function (array $point): array {
        return [
            'name' => $point['point_name'],
            'latitude' => (float) $point['latitude'],
            'longitude' => (float) $point['longitude']
        ];
    }, $pointQuery->fetchAll());

    echo json_encode([
        'latitude' => (float) $location['latitude'],
        'longitude' => (float) $location['longitude'],
        'accuracy' => $location['accuracy'] === null ? null : (float) $location['accuracy'],
        'recorded_at' => $location['recorded_at'],
        'route_name' => $duty['route_name'],
        'barangay' => $duty['barangay'],
        'vehicle_number' => $duty['vehicle_number'],
        'route_points' => $routePoints
    ]);
    exit;
}

echo json_encode([
    'latitude' => null,
    'longitude' => null,
    'message' => 'No collector is currently sharing a recent location during an assigned schedule.'
]);
