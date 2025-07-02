<?php
require_once 'config/config.php';
require_once 'includes/sensor_data.php';
require_once 'includes/header.php';

// Fetch sensor data
$sensors = getSensorsWithTodayStats();
?>

<!-- Main Content -->
<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Title -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Sensors Dashboard</h2>
                <p class="text-gray-600">Real-time monitoring of sensor readings and volume metrics</p>
            </div>
            <a href="pages/analytics.php" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                📊 View Analytics
            </a>
        </div>
    </div>

    <!-- Sensors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <?php foreach ($sensors as $sensor): ?>
            <?php
            $iconColors = getSensorIcon($sensor['name']);
            $isActive = $sensor['status'] === 'active';
            ?>
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 hover:shadow-lg transition-shadow">
                <!-- Centered Sensor Icon -->
                <div class="flex justify-center mb-4">
                    <div class="<?= $iconColors[0] ?> p-3 rounded-lg">
                        <svg class="w-8 h-8 <?= $iconColors[1] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            
                        </svg>
                    </div>
                </div>

                <!-- Header with title and status -->
                <div class="flex items-start justify-between mb-4">
                    <div class="text-center flex-1">
                        <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($sensor['name']) ?></h3>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($sensor['location']) ?></p>
                    </div>
                    <!-- Status Badge -->
                    <?= getStatusBadge($sensor['status']) ?>
                </div>

                <!-- Total Value -->
                <div class="mb-4">
                    <?php if ($isActive): ?>
                        <div class="text-3xl font-bold text-industrial-600 mb-1">
                            <?= number_format($sensor['total_volume_today'], 1) ?> <?= htmlspecialchars($sensor['unit']) ?>
                        </div>
                        <p class="text-sm text-gray-500">Today's Total Volume</p>
                    <?php else: ?>
                        <div class="text-3xl font-bold text-gray-400 mb-1">
                            -- <?= htmlspecialchars($sensor['unit']) ?>
                        </div>
                        <p class="text-sm text-gray-500">Today's Total Volume</p>
                    <?php endif; ?>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                    <div>
                        <?php if ($isActive): ?>
                            <div class="text-lg font-semibold text-gray-900"><?= $sensor['hits_today'] ?></div>
                        <?php else: ?>
                            <div class="text-lg font-semibold text-gray-400">--</div>
                        <?php endif; ?>
                        <p class="text-xs text-gray-500">Hits Today</p>
                    </div>
                    <div>
                        <div class="text-lg font-semibold text-gray-900">
                            <?= number_format($sensor['volume_per_hit'], 2) ?> <?= htmlspecialchars($sensor['unit']) ?>
                        </div>
                        <p class="text-xs text-gray-500">Per Hit</p>
                    </div>
                </div>

                <!-- Last Hit Time -->
                <div class="mt-4 pt-3 border-t border-gray-100 text-center">
                    <?php if ($isActive): ?>
                        <p class="text-sm text-gray-500">Last hit: <?= formatLastHitTime($sensor['last_hit_time']) ?></p>
                    <?php else: ?>
                        <p class="text-sm text-gray-400">Last hit: <span class="font-medium">Under maintenance</span></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($sensors)): ?>
            <div class="col-span-full text-center py-12">
                <div class="text-gray-400 text-lg">No sensors found</div>
                <p class="text-gray-500 mt-2">Please check your database connection and ensure sensors are configured.</p>
            </div>
        <?php endif; ?>
    </div>
    <script>
        // Auto-refresh the page every 30 seconds for real-time updates
        setTimeout(function() {
            window.location.reload();
        }, 30000);

        // Optional: Add a refresh button
        document.addEventListener('DOMContentLoaded', function() {
            // Add refresh button to page title area
            const titleDiv = document.querySelector('.mb-8');
            const refreshBtn = document.createElement('button');
            refreshBtn.innerHTML = '🔄 Refresh';
            refreshBtn.className = 'ml-4 px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600';
            refreshBtn.onclick = () => window.location.reload();
            titleDiv.querySelector('h2').appendChild(refreshBtn);
        });
    </script>
</main>

<?php require_once 'includes/footer.php'; ?>