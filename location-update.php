<?php
require_once 'auth.php';
require 'config.php';
require_role('collector');

header('Content-Type: application/json');
$collectorUsername = $_SESSION['username'] ?? null;
if (!is_string($collectorUsername) || $collectorUsername === '') {
    http_response_code(401);
    echo json_encode(['error' => 'Your session is missing an account identity. Please log in again.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$latitude = $payload['latitude'] ?? null;
$longitude = $payload['longitude'] ?? null;
$accuracy = $payload['accuracy'] ?? null;

if (!is_numeric($latitude) || !is_numeric($longitude) ||
    $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ||
    ($accuracy !== null && (!is_numeric($accuracy) || $accuracy < 0))) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid location data.']);
    exit;
}

$statement = $pdo->prepare(
    'INSERT INTO gps_locations (collector_username, latitude, longitude, accuracy, recorded_at)
     VALUES (:collector_username, :latitude, :longitude, :accuracy, UTC_TIMESTAMP())'
);
$statement->execute([
    ':collector_username' => $collectorUsername,
    ':latitude' => (float) $latitude,
    ':longitude' => (float) $longitude,
    ':accuracy' => $accuracy === null ? null : (float) $accuracy
]);

echo json_encode(['success' => true]);
