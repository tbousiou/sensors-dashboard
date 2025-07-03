<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
requireAuth();

require_once __DIR__ . '/includes/sensor_data.php';
require_once __DIR__ . '/includes/header.php';

// Fetch sensor data
$sensors = getSensorsWithCumulativeStats();


?>

<!-- Main Content -->
<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Title -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Dashboard</h2>
                <p class="text-gray-600">Real-time monitoring of sensor readings and cumulative metrics</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="window.location.reload()" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    🔄 Refresh
                </button>
               
            </div>
        </div>
    </div>

    <!-- Sensors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($sensors as $sensor): ?>
            <?php
            $isActive = $sensor['status'] === 'active';
            ?>
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300">
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="bg-white p-2.5 rounded-lg shadow-sm">
                                <img src="assets/images/bottle.png" alt="Sensor" class="w-8 h-8">
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($sensor['name']) ?></h3>
                            </div>
                        </div>
                        <?= getStatusBadge($sensor['status']) ?>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="p-6">
                    <!-- Primary Metric -->
                    <div class="text-center mb-6">
                        <?php if ($isActive && $sensor['total_volume'] > 0): ?>
                            <div class="text-4xl font-black text-industrial-600 mb-2 tracking-tight">
                                <?= number_format($sensor['total_volume'], 1) ?> <?= htmlspecialchars($sensor['unit']) ?>
                            </div>
                        <?php else: ?>
                            <div class="text-4xl font-black text-gray-400 mb-2 tracking-tight">
                                -- <?= htmlspecialchars($sensor['unit']) ?>
                            </div>
                        <?php endif; ?>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Volume</p>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div class="text-center">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <?php if ($isActive && $sensor['total_hits'] > 0): ?>
                                    <div class="text-2xl font-bold text-gray-900 mb-1"><?= $sensor['total_hits'] ?></div>
                                <?php else: ?>
                                    <div class="text-2xl font-bold text-gray-400 mb-1">--</div>
                                <?php endif; ?>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Hits</p>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-2xl font-bold text-gray-900 mb-1">
                                    <?= number_format($sensor['volume_per_hit'], 2) ?> <?= htmlspecialchars($sensor['unit']) ?>
                                </div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Per Hit</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <?php if ($isActive): ?>
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <p class="text-sm text-gray-600">
                                    Last hit: <span class="font-semibold text-gray-800">
                                        <?= $sensor['last_hit_time'] ? formatLastHitTime($sensor['last_hit_time']) : 'No hits' ?>
                                    </span>
                                </p>
                            <?php else: ?>
                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                <p class="text-sm text-gray-600">
                                    Last hit: <span class="font-semibold text-gray-500">Under maintenance</span>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($sensors)): ?>
            <div class="col-span-full text-center py-12">
                <div class="text-gray-400 text-lg">No sensors found</div>
                <p class="text-gray-500 mt-2">Please check your database connection and ensure sensors are configured.</p>
                <?php if (isset($pdo)): ?>
                    <p class="text-gray-500 mt-1">Database connection: OK</p>
                <?php else: ?>
                    <p class="text-red-500 mt-1">Database connection: Failed</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <script>
        // Auto-refresh the page every 30 seconds for real-time updates
        setTimeout(function() {
            window.location.reload();
        }, 30000);

        
    </script>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>