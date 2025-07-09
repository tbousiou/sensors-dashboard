<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensors Dashboard</title>
    <link href="assets/css/style.css" rel="stylesheet">
    
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <h1 class="text-xl font-bold text-sky-700"><?= APP_NAME ?></h1>
                    <nav class="hidden md:flex space-x-6">
                        <a href="<?= getCurrentPageUrl('index.php') ?>" class="text-gray-700 hover:text-sky-600 px-3 py-2 text-sm font-medium transition-colors">
                            📊 Dashboard
                        </a>
                        <a href="<?= getCurrentPageUrl('analytics.php') ?>" class="text-gray-700 hover:text-sky-600 px-3 py-2 text-sm font-medium transition-colors">
                            📈 Analytics
                        </a>
                        <a href="<?= getCurrentPageUrl('manage.php') ?>" class="text-gray-700 hover:text-sky-600 px-3 py-2 text-sm font-medium transition-colors">
                            ⚙️ Management
                        </a>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600 hidden sm:block">Welcome, <?= $_SESSION['username'] ?></span>
                    <a href="<?= getCurrentLogoutUrl() ?>" class="text-red-600 hover:text-red-800 hidden sm:block">Logout</a>
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-sky-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sky-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
            <!-- Mobile menu -->
            <div id="mobile-menu" class="md:hidden hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 border-t border-gray-200">
                    <a href="<?= getCurrentPageUrl('index.php') ?>" class="text-gray-700 hover:text-sky-600 hover:bg-gray-50 block px-3 py-2 text-base font-medium transition-colors">
                        📊 Dashboard
                    </a>
                    <a href="<?= getCurrentPageUrl('analytics.php') ?>" class="text-gray-700 hover:text-sky-600 hover:bg-gray-50 block px-3 py-2 text-base font-medium transition-colors">
                        📈 Analytics
                    </a>
                    <a href="<?= getCurrentPageUrl('manage.php') ?>" class="text-gray-700 hover:text-sky-600 hover:bg-gray-50 block px-3 py-2 text-base font-medium transition-colors">
                        ⚙️ Management
                    </a>
                    <div class="border-t border-gray-200 pt-3">
                        <span class="text-sm text-gray-600 block px-3 py-1">Welcome, <?= $_SESSION['username'] ?></span>
                        <a href="<?= getCurrentLogoutUrl() ?>" class="text-red-600 hover:text-red-800 hover:bg-red-50 block px-3 py-2 text-base font-medium transition-colors">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>

<?php
function getCurrentLogoutUrl() {
    $currentDir = dirname($_SERVER['PHP_SELF']);
    if (strpos($currentDir, '/api') !== false) {
        return '../logout.php';
    }
    return 'logout.php';
}

function getCurrentPageUrl($page) {
    $currentDir = dirname($_SERVER['PHP_SELF']);
    if (strpos($currentDir, '/api') !== false) {
        return '../' . $page;
    }
    return $page;
}
?>