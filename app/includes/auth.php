<?php
session_start();

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Get auth credentials from .env file
 */
function getAuthCredentials() {
    $envFile = __DIR__ . '/../../.env';  // Updated path
    $credentials = ['username' => 'admin', 'password' => 'admin']; // defaults
    
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, 'AUTH_USERNAME=') === 0) {
                $credentials['username'] = trim(substr($line, 14));
            }
            if (strpos($line, 'AUTH_PASSWORD=') === 0) {
                $credentials['password'] = trim(substr($line, 14));
            }
        }
    }
    
    return $credentials;
}

/**
 * Authenticate user with credentials
 */
function authenticate($username, $password) {
    $credentials = getAuthCredentials();
    
    if ($username === $credentials['username'] && $password === $credentials['password']) {
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
    if (strpos($currentDir, '/api') !== false) {
        return '../login.php';
    }
    return 'login.php';
}
?>