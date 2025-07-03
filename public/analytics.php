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

// Pagination for sensor readings
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Get total count for pagination
$totalReadings = getTotalSensorReadingsCount($selectedSensorId);
$totalPages = ceil($totalReadings / $perPage);

// Get detailed sensor readings for the table with pagination
$sensorReadings = getSensorReadingsPaginated($selectedSensorId, $perPage, $offset);

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

    <!-- Sensor Selection and Summary Stats Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Sensor Selection -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <form action="" method="GET">
                <label for="sensor" class="block text-sm font-medium text-gray-700 mb-2">Select Sensor</label>
                <select id="sensor" name="sensor" onchange="this.form.submit()" class="block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach ($sensors as $sensor): ?>
                        <option value="<?= $sensor['id'] ?>" <?= $selectedSensorId == $sensor['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sensor['name']) ?> 
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="bg-white rounded-xl shadow-lg p-6">
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
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 gap-8 mb-8">
        <!-- Combined Hits and Volume Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cumulative Hits and Volume Over Time</h3>
            <canvas id="combinedChart"></canvas>
        </div>
    </div>

    <!-- Sensor Readings Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Recent Sensor Readings</h3>
            <div class="text-sm text-gray-500">
                Showing <?= min($offset + 1, $totalReadings) ?>-<?= min($offset + $perPage, $totalReadings) ?> of <?= $totalReadings ?> readings
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Counter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cumulative Volume (L)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $counter = $totalReadings - $offset;
                    foreach ($sensorReadings as $reading): 
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $counter ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('Y-m-d H:i:s', strtotime($reading['timestamp'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= number_format($reading['volume'], 2) ?></td>
                        </tr>
                    <?php 
                    $counter--;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">
            <div class="flex flex-1 justify-between sm:hidden">
                <?php if ($page > 1): ?>
                    <a href="?sensor=<?= $selectedSensorId ?>&page=<?= $page - 1 ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?sensor=<?= $selectedSensorId ?>&page=<?= $page + 1 ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $totalPages ?></span>
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?sensor=<?= $selectedSensorId ?>&page=1" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0" title="First page">
                                <span class="sr-only">First</span>
                                ⇤
                            </a>
                            <a href="?sensor=<?= $selectedSensorId ?>&page=<?= $page - 1 ?>" class="relative inline-flex items-center px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0" title="Previous page">
                                <span class="sr-only">Previous</span>
                                ←
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="?sensor=<?= $selectedSensorId ?>&page=<?= $i ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $i == $page ? 'bg-blue-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?sensor=<?= $selectedSensorId ?>&page=<?= $page + 1 ?>" class="relative inline-flex items-center px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0" title="Next page">
                                <span class="sr-only">Next</span>
                                →
                            </a>
                            <a href="?sensor=<?= $selectedSensorId ?>&page=<?= $totalPages ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0" title="Last page">
                                <span class="sr-only">Last</span>
                                ⇥
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
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