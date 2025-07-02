<?php
// General application configuration

// Application settings
define('APP_NAME', 'Sensors Dashboard');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, production, testing

// Timezone configuration
define('DEFAULT_TIMEZONE', 'Europe/Athens'); // Change to your timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Error reporting (disable in production)
if ($_ENV['APP_ENV'] ?? 'development' === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}