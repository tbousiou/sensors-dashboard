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
 * Format last hit time
 */
function formatLastHitTime($timestamp) {
    if (!$timestamp) {
        return '<span class="font-medium text-gray-400">No hits today</span>';
    }
    
    $time = new DateTime($timestamp);
    return '<span class="font-medium text-gray-700">' . $time->format('H:i:s') . '</span>';
}

function getSensorsWithCumulativeStats() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                s.id,
                s.name,
                s.status,
                s.unit,
                s.volume_per_hit,
                COALESCE(SUM(s.volume_per_hit), 0) as total_volume,
                COUNT(sd.id) as total_hits,
                MAX(sd.timestamp) as last_hit_time
            FROM sensors s
            LEFT JOIN readings sd ON s.id = sd.sensor_id
            GROUP BY s.id, s.name, s.status, s.unit, s.volume_per_hit
            ORDER BY s.name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getSensorsWithCumulativeStats: " . $e->getMessage());
        return [];
    }
}

function getCumulativeDailyStats($sensorId = null, $days = 30) {
    global $pdo;
    
    try {
        $whereClause = $sensorId ? "WHERE s.id = :sensor_id" : "";
        $params = $sensorId ? [':sensor_id' => $sensorId] : [];
        
        $stmt = $pdo->prepare("
            WITH daily_stats AS (
                SELECT 
                    s.id as sensor_id,
                    s.name as sensor_name,
                    DATE(sd.timestamp) as date,
                    COUNT(sd.id) as daily_hits,
                    COALESCE(SUM(s.volume_per_hit), 0) as daily_volume
                FROM sensors s
                LEFT JOIN readings sd ON s.id = sd.sensor_id
                $whereClause
                GROUP BY s.id, s.name, DATE(sd.timestamp)
                ORDER BY s.id, date
            ),
            cumulative_stats AS (
                SELECT 
                    sensor_id,
                    sensor_name,
                    date,
                    daily_hits,
                    daily_volume,
                    SUM(daily_hits) OVER (PARTITION BY sensor_id ORDER BY date) as cumulative_hits,
                    SUM(daily_volume) OVER (PARTITION BY sensor_id ORDER BY date) as cumulative_volume
                FROM daily_stats
                WHERE date IS NOT NULL
            )
            SELECT * FROM cumulative_stats
            WHERE date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
            ORDER BY sensor_id, date
        ");
        
        $params[':days'] = $days;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getCumulativeDailyStats: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all sensors
 */
function getAllSensors() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM sensors ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching sensors: " . $e->getMessage());
        return [];
    }
}

/**
 * Update sensor information
 */
function updateSensor($id, $name, $volumePerHit, $unit, $status) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE sensors SET name = ?, volume_per_hit = ?, unit = ?, status = ? WHERE id = ?");
        return $stmt->execute([$name, $volumePerHit, $unit, $status, $id]);
    } catch (PDOException $e) {
        error_log("Error updating sensor: " . $e->getMessage());
        return false;
    }
}

/**
 * Clear all readings from the database
 */
function clearAllReadings() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM readings");
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error clearing readings: " . $e->getMessage());
        return false;
    }
}

function getTotalSensorReadingsCount($sensorId) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) FROM readings WHERE sensor_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sensorId]);
    
    return $stmt->fetchColumn();
}

function getSensorReadingsPaginated($sensorId, $limit = 50, $offset = 0) {
    global $pdo;
    
    $sql = "SELECT 
                r.timestamp,
                (
                    SELECT COUNT(*) * s.volume_per_hit 
                    FROM readings r2 
                    WHERE r2.sensor_id = ? 
                    AND r2.timestamp <= r.timestamp
                ) as volume
            FROM readings r
            JOIN sensors s ON r.sensor_id = s.id
            WHERE r.sensor_id = ? 
            ORDER BY r.timestamp DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sensorId, $sensorId, $limit, $offset]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSensorReadings($sensorId, $limit = 100) {
    // Keep for backward compatibility, now uses pagination function
    return getSensorReadingsPaginated($sensorId, $limit, 0);
}
?>