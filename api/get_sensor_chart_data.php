<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

require_once '../config/database.php';

header('Content-Type: application/json');

$sensor_id = $_GET['sensor_id'] ?? null;
$days = $_GET['days'] ?? 30;

if (!$sensor_id) {
    echo json_encode(['error' => 'sensor_id required']);
    exit;
}

try {
    // Get daily hit counts for the last X days
    // Note: Using a simpler approach for better MySQL compatibility
    $sql = "
        SELECT 
            DATE(r.timestamp) as date,
            COUNT(r.id) as hits,
            COUNT(r.id) * s.volume_per_hit as volume
        FROM sensors s
        LEFT JOIN readings r ON s.id = r.sensor_id 
            AND r.timestamp >= CURDATE() - INTERVAL ? DAY
        WHERE s.id = ?
        GROUP BY DATE(r.timestamp), s.volume_per_hit
        ORDER BY date
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$days - 1, $sensor_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create a complete date range and fill missing dates with 0
    $labels = [];
    $hits = [];
    $volumes = [];
    
    // Generate all dates in range
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('M j', strtotime($date));
        
        // Find data for this date
        $found = false;
        foreach ($data as $row) {
            if ($row['date'] === $date) {
                $hits[] = (int)$row['hits'];
                $volumes[] = (float)$row['volume'];
                $found = true;
                break;
            }
        }
        
        // If no data found for this date, add 0
        if (!$found) {
            $hits[] = 0;
            $volumes[] = 0.0;
        }
    }
    
    echo json_encode([
        'labels' => $labels,
        'hits' => $hits,
        'volumes' => $volumes
    ]);
    
} catch (PDOException $e) {
    error_log("Chart data error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>
