<?php
// Load environment variables the same way as database.php
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

require_once '../config/config.php';
require_once '../includes/sensor_data.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
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
    $providedKey = $_SERVER['HTTP_X_API_KEY'] ?? $_POST['api_key'] ?? $_GET['api_key'] ?? null;
    return $providedKey === $validKey;
}

// Validate API key
if (!validateApiKey($apiKey)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or missing API key']);
    exit;
}

// Get sensor ID from URL parameter
$sensorId = $_GET['sensor_id'] ?? null;
if (!$sensorId) {
    http_response_code(400);
    echo json_encode(['error' => 'Sensor ID is required']);
    exit;
}

// Validate sensor exists
try {
    $stmt = $pdo->prepare("SELECT id, name, status FROM sensors WHERE id = ?");
    $stmt->execute([$sensorId]);
    $sensor = $stmt->fetch();
    
    if (!$sensor) {
        http_response_code(404);
        echo json_encode(['error' => 'Sensor not found']);
        exit;
    }
    
    if ($sensor['status'] !== 'active') {
        http_response_code(400);
        echo json_encode(['error' => 'Sensor is not active']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

// Get optional timestamp (default to current time)
$timestamp = $_POST['timestamp'] ?? date('Y-m-d H:i:s');

// Validate timestamp format
if (!DateTime::createFromFormat('Y-m-d H:i:s', $timestamp)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid timestamp format. Use YYYY-MM-DD HH:MM:SS']);
    exit;
}

// Insert sensor hit
try {
    $stmt = $pdo->prepare("INSERT INTO readings (sensor_id, timestamp) VALUES (?, ?)");
    $stmt->execute([$sensorId, $timestamp]);
    
    $hitId = $pdo->lastInsertId();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'hit_id' => $hitId,
        'sensor_id' => $sensorId,
        'sensor_name' => $sensor['name'],
        'timestamp' => $timestamp,
        'message' => 'Sensor data recorded successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to record sensor data']);
}
?>