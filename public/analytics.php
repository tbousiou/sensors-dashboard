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

// Filter stats for selected sensor
$sensorStats = array_filter($cumulativeStats, function($stat) use ($selectedSensorId) {
    return $stat['sensor_id'] == $selectedSensorId;
});

// Prepare data for charts
$dates = [];
$cumulativeHits = [];
$cumulativeVolumes = [];

foreach ($sensorStats as $stat) {
    $dates[] = date('M j', strtotime($stat['date']));
    $cumulativeHits[] = $stat['cumulative_hits'];
    $cumulativeVolumes[] = round($stat['cumulative_volume'], 2);
}
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
    <div class="grid grid-cols-1  gap-8 mb-8">
        <!-- Combined Hits and Volume Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cumulative Hits and Volume Over Time</h3>
            <canvas id="combinedChart" ></canvas>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Summary Statistics</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-sm font-medium text-gray-500 mb-1">Total Hits (30 days)</div>
                <div class="text-xl font-bold text-gray-900"><?= end($cumulativeHits) ?: 0 ?></div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-sm font-medium text-gray-500 mb-1">Total Volume (30 days)</div>
                <div class="text-xl font-bold text-gray-900"><?= number_format(end($cumulativeVolumes) ?: 0, 2) ?> L</div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart data
    const dates = <?= json_encode($dates) ?>;
    const cumulativeHits = <?= json_encode($cumulativeHits) ?>;
    const cumulativeVolumes = <?= json_encode($cumulativeVolumes) ?>;

    // Combined Chart with dual y-axes
    const combinedCtx = document.getElementById('combinedChart').getContext('2d');
    new Chart(combinedCtx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Cumulative Hits',
                data: cumulativeHits,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: false,
                tension: 0.1,
                yAxisID: 'y'
            }, {
                label: 'Cumulative Volume (L)',
                data: cumulativeVolumes,
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: false,
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Hits'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Volume (L)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../app/includes/footer.php'; ?>