<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensors Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        industrial: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <h1 class="text-xl font-bold text-industrial-700"><?= APP_NAME ?></h1>
                    <nav class="hidden md:flex space-x-6">
                        <a href="<?= getCurrentPageUrl('index.php') ?>" class="text-gray-700 hover:text-industrial-600 px-3 py-2 text-sm font-medium transition-colors">
                            📊 Dashboard
                        </a>
                        <a href="<?= getCurrentPageUrl('analytics.php') ?>" class="text-gray-700 hover:text-industrial-600 px-3 py-2 text-sm font-medium transition-colors">
                            📈 Analytics
                        </a>
                        <a href="<?= getCurrentPageUrl('manage.php') ?>" class="text-gray-700 hover:text-industrial-600 px-3 py-2 text-sm font-medium transition-colors">
                            ⚙️ Management
                        </a>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Welcome, <?= $_SESSION['username'] ?></span>
                    <a href="<?= getCurrentLogoutUrl() ?>" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

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