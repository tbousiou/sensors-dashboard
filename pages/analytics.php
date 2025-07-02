<?php
require_once '../config/config.php';
require_once '../includes/sensor_data.php';
require_once '../includes/header.php';

$sensors = getSensorsWithTodayStats();
?>

<!-- Main Content -->
<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Title -->
    <div class="mb-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Sensor Analytics</h2>
        <p class="text-gray-600">30-day trend analysis and detailed charts for all sensors</p>
        <a href="../index.php" class="inline-flex items-center mt-3 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <?php foreach ($sensors as $sensor): ?>
            <?php 
            $iconColors = getSensorIcon($sensor['name']);
            $isActive = $sensor['status'] === 'active';
            ?>
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <!-- Sensor Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="<?= $iconColors[0] ?> p-2 rounded-lg mr-3">
                            <svg class="w-6 h-6 <?= $iconColors[1] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v4l5 9H5l5-9V3h4-4"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3h6"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($sensor['name']) ?></h3>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($sensor['location']) ?></p>
                        </div>
                    </div>
                    <?= getStatusBadge($sensor['status']) ?>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="text-center">
                        <div class="text-xl font-bold text-gray-900">
                            <?= $isActive ? $sensor['hits_today'] : '--' ?>
                        </div>
                        <p class="text-xs text-gray-500">Today's Hits</p>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-blue-600">
                            <?= $isActive ? number_format($sensor['total_volume_today'], 1) : '--' ?> <?= htmlspecialchars($sensor['unit']) ?>
                        </div>
                        <p class="text-xs text-gray-500">Today's Volume</p>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-gray-900">
                            <?= number_format($sensor['volume_per_hit'], 2) ?> <?= htmlspecialchars($sensor['unit']) ?>
                        </div>
                        <p class="text-xs text-gray-500">Per Hit</p>
                    </div>
                </div>

                <!-- Chart Container -->
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-sm font-medium text-gray-700">30-Day Trend</h4>
                        <div class="flex space-x-2">
                            <button onclick="switchChart(<?= $sensor['id'] ?>, 'hits')" 
                                    id="btn-hits-<?= $sensor['id'] ?>"
                                    class="px-2 py-1 text-xs bg-blue-500 text-white rounded">
                                Hits
                            </button>
                            <button onclick="switchChart(<?= $sensor['id'] ?>, 'volume')" 
                                    id="btn-volume-<?= $sensor['id'] ?>"
                                    class="px-2 py-1 text-xs bg-gray-300 text-gray-700 rounded">
                                Volume
                            </button>
                        </div>
                    </div>
                    <div class="h-64 bg-gray-50 rounded-lg p-2">
                        <canvas id="chart-<?= $sensor['id'] ?>" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Last Hit Info -->
                <div class="text-center text-sm text-gray-500">
                    Last hit: <?= $isActive ? formatLastHitTime($sensor['last_hit_time']) : '<span class="font-medium text-gray-400">Under maintenance</span>' ?>
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
</main>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let charts = {};
let chartData = {};

// Load all charts on page load
document.addEventListener('DOMContentLoaded', async function() {
    <?php foreach ($sensors as $sensor): ?>
        await loadSensorChart(<?= $sensor['id'] ?>, '<?= htmlspecialchars($sensor['name']) ?>');
    <?php endforeach; ?>
});

async function loadSensorChart(sensorId, sensorName) {
    try {
        const response = await fetch(`../api/get_sensor_chart_data.php?sensor_id=${sensorId}&days=30`);
        const data = await response.json();
        
        if (data.error) {
            console.error('Error loading chart data:', data.error);
            return;
        }
        
        // Store data for switching between views
        chartData[sensorId] = data;
        
        const ctx = document.getElementById(`chart-${sensorId}`).getContext('2d');
        
        charts[sensorId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Daily Hits',
                    data: data.hits,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: `${sensorName} - Daily Hits`,
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    } catch (error) {
        console.error('Error loading chart:', error);
        // Show error message in chart area
        const ctx = document.getElementById(`chart-${sensorId}`).getContext('2d');
        ctx.fillStyle = '#6B7280';
        ctx.font = '14px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Error loading chart data', ctx.canvas.width / 2, ctx.canvas.height / 2);
    }
}

function switchChart(sensorId, type) {
    const chart = charts[sensorId];
    const data = chartData[sensorId];
    
    if (!chart || !data) return;
    
    // Update button states
    document.getElementById(`btn-hits-${sensorId}`).className = 
        type === 'hits' ? 'px-2 py-1 text-xs bg-blue-500 text-white rounded' : 'px-2 py-1 text-xs bg-gray-300 text-gray-700 rounded';
    document.getElementById(`btn-volume-${sensorId}`).className = 
        type === 'volume' ? 'px-2 py-1 text-xs bg-blue-500 text-white rounded' : 'px-2 py-1 text-xs bg-gray-300 text-gray-700 rounded';
    
    // Update chart data
    if (type === 'hits') {
        chart.data.datasets[0].data = data.hits;
        chart.data.datasets[0].label = 'Daily Hits';
        chart.data.datasets[0].borderColor = 'rgb(59, 130, 246)';
        chart.data.datasets[0].backgroundColor = 'rgba(59, 130, 246, 0.1)';
        chart.options.plugins.title.text = chart.options.plugins.title.text.replace('Daily Volume', 'Daily Hits');
    } else {
        chart.data.datasets[0].data = data.volumes;
        chart.data.datasets[0].label = 'Daily Volume (L)';
        chart.data.datasets[0].borderColor = 'rgb(16, 185, 129)';
        chart.data.datasets[0].backgroundColor = 'rgba(16, 185, 129, 0.1)';
        chart.options.plugins.title.text = chart.options.plugins.title.text.replace('Daily Hits', 'Daily Volume');
    }
    
    chart.update();
}

// Auto-refresh charts every 5 minutes
setInterval(async function() {
    <?php foreach ($sensors as $sensor): ?>
        await loadSensorChart(<?= $sensor['id'] ?>, '<?= htmlspecialchars($sensor['name']) ?>');
    <?php endforeach; ?>
}, 300000); // 5 minutes
</script>

<?php require_once '../includes/footer.php'; ?>
