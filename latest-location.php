<?php
require 'config.php';
header('Content-Type: application/json');

$statement = $pdo->query(
    'SELECT latitude, longitude, accuracy, recorded_at
     FROM gps_locations
     ORDER BY recorded_at DESC
     LIMIT 1'
);
$location = $statement->fetch();
echo json_encode($location ?: ['latitude' => null, 'longitude' => null]);
