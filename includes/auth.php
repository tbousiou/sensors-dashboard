<?php
session_start();
// Load environment variables from .env file
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Authenticate user with credentials
 */
function authenticate($username, $password) {
    $validUsername = $_ENV['AUTH_USERNAME'] ?? 'admin';
    $validPassword = $_ENV['AUTH_PASSWORD'] ?? 'admin';
    
    if ($username === $validUsername && $password === $validPassword) {
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $username;
        return true;
    }
    
    return false;
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: ' . getLoginUrl());
        exit;
    }
}

/**
 * Get login URL based on current directory
 */
function getLoginUrl() {
    $currentDir = dirname($_SERVER['PHP_SELF']);
    if (strpos($currentDir, '/pages') !== false) {
        return '../login.php';
    }
    return 'login.php';
}
?>
