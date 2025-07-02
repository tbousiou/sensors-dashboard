<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Tailwind config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'industrial': {
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

<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- App Name -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-industrial-700">
                            <?= APP_NAME ?>
                        </h1>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-4">
                    <a href="<?= str_contains($_SERVER['REQUEST_URI'], 'pages/') ? '../index.php' : 'index.php' ?>" 
                       class="text-gray-500 hover:text-industrial-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Dashboard
                    </a>
                    <a href="<?= str_contains($_SERVER['REQUEST_URI'], 'pages/') ? 'analytics.php' : 'pages/analytics.php' ?>" 
                       class="text-gray-500 hover:text-industrial-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Analytics
                    </a>
                    <a href="#" class="text-gray-500 hover:text-industrial-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </header>
