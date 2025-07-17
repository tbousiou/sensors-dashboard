<?php

// Load environment variables the same way as database.php
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

require_once '../config/config.php';
require_once '../includes/sensor_data.php';

header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use GET.']);
    exit;
}

// Get API key from environment (same pattern as database.php)
$apiKey = $_ENV['API_KEY'] ?? null;

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(['error' => 'API key not configured']);
    exit;
}

// Check API key authentication
function validateApiKey($validKey) {
    $providedKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
    return $providedKey === $validKey;
}

// Validate API key
if (!validateApiKey($apiKey)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or missing API key']);
    exit;
}

// Get cumulative stats for all sensors
try {
    $stats = getSensorsWithCumulativeStats();
    
    // Filter only active sensors (optional - remove if you want all sensors)
    $activeStats = array_filter($stats, function($sensor) {
        return $sensor['status'] === 'active';
    });
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => array_values($activeStats), // Re-index array after filtering
        'total_sensors' => count($activeStats),
        'message' => 'Cumulative stats retrieved successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve cumulative stats']);
}
?>