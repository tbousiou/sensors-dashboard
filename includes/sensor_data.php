<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Get all sensors with their today's statistics
 */
function getSensorsWithTodayStats() {
    global $pdo;
    
    $sql = "
        SELECT 
            s.id,
            s.name,
            s.location,
            s.volume_per_hit,
            s.unit,
            s.status,
            COUNT(r.id) as hits_today,
            COUNT(r.id) * s.volume_per_hit as total_volume_today,
            MAX(r.timestamp) as last_hit_time
        FROM sensors s
        LEFT JOIN readings r ON s.id = r.sensor_id 
            AND DATE(r.timestamp) = CURDATE()
        GROUP BY s.id, s.name, s.location, s.volume_per_hit, s.unit, s.status
        ORDER BY s.id
    ";
    
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching sensor data: " . $e->getMessage());
        return [];
    }
}

/**
 * Get sensor status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>',
        'inactive' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>',
        'maintenance' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Maintenance</span>'
    ];
    
    return $badges[$status] ?? $badges['inactive'];
}

/**
 * Get sensor icon based on sensor name/type
 */
function getSensorIcon($sensorName) {
    $icons = [
        'Kitchen Tap' => ['bg-blue-100', 'text-blue-600'],
        'Bathroom Shower' => ['bg-cyan-100', 'text-cyan-600'],
        'Garden Sprinkler' => ['bg-green-100', 'text-green-600'],
        'Laundry Machine' => ['bg-orange-100', 'text-orange-600'],
    ];
    
    // Default icon colors
    $default = ['bg-gray-100', 'text-gray-600'];
    
    foreach ($icons as $name => $colors) {
        if (stripos($sensorName, $name) !== false) {
            return $colors;
        }
    }
    
    return $default;
}

/**
 * Format last hit time
 */
function formatLastHitTime($timestamp) {
    if (!$timestamp) {
        return '<span class="font-medium text-gray-400">No hits today</span>';
    }
    
    $time = new DateTime($timestamp);
    return '<span class="font-medium text-gray-700">' . $time->format('H:i:s') . '</span>';
}
?>