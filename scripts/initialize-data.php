<?php
// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require_once __DIR__ . '/../app/config/database.php';

try {
    // Use environment variables for database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'sensors_dashboard';
    $username = $_ENV['DB_USER'] ?? 'devuser';
    $password = $_ENV['DB_PASS'] ?? 'devpass123';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    // Clear existing data
    $pdo->exec("DELETE FROM readings");
    $pdo->exec("DELETE FROM sensors");
    
    // Insert 6 sensors
    $sensorStmt = $pdo->prepare("INSERT INTO sensors (id, name, volume_per_hit, unit, status) VALUES (?, ?, ?, 'L', 'active')");
    
    for ($i = 1; $i <= 6; $i++) {
        $volumePerHit = round(1 + (mt_rand(0, 400) / 100), 2); // 1.00 to 5.00
        $sensorStmt->execute([$i, "Sensor $i", $volumePerHit]);
    }
    
    // Insert readings for 30 days
    $readingStmt = $pdo->prepare("INSERT INTO readings (sensor_id, timestamp) VALUES (?, ?)");
    
    for ($day = 0; $day < 30; $day++) {
        for ($sensorId = 1; $sensorId <= 6; $sensorId++) {
            $readingsPerDay = mt_rand(5, 15); // Random 5-15 readings per day
            
            for ($reading = 0; $reading < $readingsPerDay; $reading++) {
                $randomHour = mt_rand(0, 23);
                $randomMinute = mt_rand(0, 59);
                $randomSecond = mt_rand(0, 59);
                
                $timestamp = date('Y-m-d H:i:s', strtotime("-$day days $randomHour:$randomMinute:$randomSecond"));
                $readingStmt->execute([$sensorId, $timestamp]);
            }
        }
    }
    
    echo "Sample data generated successfully!\n";
    echo "- 6 sensors created\n";
    echo "- Readings generated for 30 days\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>