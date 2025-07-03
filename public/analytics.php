<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/includes/auth.php';
requireAuth();

require_once __DIR__ . '/../app/includes/sensor_data.php';
require_once __DIR__ . '/../app/includes/header.php';

// Get selected sensor or default to first sensor
$selectedSensorId = $_GET['sensor'] ?? null;
$sensors = getSensorsWithCumulativeStats();

// If no sensor selected, use the first one
if (!$selectedSensorId && !empty($sensors)) {
    $selectedSensorId = $sensors[0]['id'];
}

$cumulativeStats = getCumulativeDailyStats($selectedSensorId, 30);
?>

<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Analytics Dashboard</h2>
                <p class="text-gray-600">Cumulative sensor data trends and insights</p>
            </div>
            <a href="index.php" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Sensor Selection -->
    <div class="mb-8">
        <form action="" method="GET" class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex-1 mb-4 sm:mb-0">
                <label for="sensor" class="block text-sm font-medium text-gray-700 mb-1">Select Sensor</label>
                <select id="sensor" name="sensor" onchange="this.form.submit()" class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach ($sensors as $sensor): ?>
                        <option value="<?= $sensor['id'] ?>" <?= $selectedSensorId == $sensor['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sensor['name']) ?> 
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Cumulative Hits Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cumulative Hits Over Time</h3>
            <p class="text-gray-500 text-center py-8">Chart will be implemented here</p>
        </div>

        <!-- Cumulative Volume Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cumulative Volume Over Time</h3>
            <p class="text-gray-500 text-center py-8">Chart will be implemented here</p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../app/includes/footer.php'; ?>