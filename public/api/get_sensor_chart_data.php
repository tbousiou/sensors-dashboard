<?php

require_once '../../app/config/config.php';
require_once '../../app/includes/auth.php';
requireAuth();

require_once '../../app/includes/sensor_data.php';

header('Content-Type: application/json');

$sensorId = $_GET['sensor_id'] ?? null;
$days = $_GET['days'] ?? 30;

if (!$sensorId) {
    http_response_code(400);
    echo json_encode(['error' => 'Sensor ID is required']);
    exit;
}

try {
    $data = getCumulativeDailyStats($sensorId, $days);
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch sensor data']);
}
?>