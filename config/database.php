<?php
// Load environment variables from .env file
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Database configuration for sensors dashboard
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'sensors_dashboard';
$username = $_ENV['DB_USERNAME'] ?? '';  // No fallback for security
$password = $_ENV['DB_PASSWORD'] ?? '';  // No fallback for security
$charset = 'utf8mb4';

// Validate that required environment variables are set
if (empty($password)) {
    die("Database password not configured. Please set DB_PASSWORD environment variable.");
}

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// PDO options for better security and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create PDO connection
    $pdo = new PDO($dsn, $username, $password, $options);

    // Optional: Set timezone to match your local timezone
    $pdo->exec("SET time_zone = '+00:00'"); // Use UTC or change to your timezone

} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

// Function to test database connection
function testDatabaseConnection()
{
    global $pdo;
    try {
        $stmt = $pdo->query('SELECT 1');
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Optional: Display connection status for debugging (remove in production)
if (isset($_GET['test_db'])) {
    if (testDatabaseConnection()) {
        echo "✅ Database connection successful!";
    } else {
        echo "❌ Database connection failed!";
    }
    exit;
}
