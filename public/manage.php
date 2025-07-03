<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

require_once __DIR__ . '/includes/sensor_data.php';
require_once __DIR__ . '/includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_sensor':
                updateSensor($_POST['sensor_id'], $_POST['name'], $_POST['volume_per_hit'], $_POST['unit'], $_POST['status']);
                break;
            case 'clear_readings':
                clearAllReadings();
                break;
            case 'export_excel':
                exportToExcel();
                break;
        }
    }
}

$sensors = getAllSensors();
?>

<!-- Management Page Content -->
<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Sensor Management</h2>
                <p class="text-gray-600">Manage sensor configurations and data</p>
            </div>
            <div class="flex space-x-3">
                <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-8">
        <div class="flex flex-wrap gap-4 justify-center">
            <button onclick="exportData()" class="inline-flex items-center px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                📊 Export to Excel
            </button>
            <button onclick="confirmClearData()" class="inline-flex items-center px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                🗑️ Clear All Readings
            </button>
        </div>
    </div>

    <!-- Sensors Management Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($sensors as $sensor): ?>
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 min-w-60">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($sensor['name']) ?></h3>
                    <button onclick="editSensor(<?= $sensor['id'] ?>, '<?= htmlspecialchars($sensor['name']) ?>', <?= $sensor['volume_per_hit'] ?>, '<?= htmlspecialchars($sensor['unit']) ?>', '<?= htmlspecialchars($sensor['status']) ?>')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        ✏️ Edit
                    </button>
                </div>
                <div class="space-y-2 text-sm">
                    <div><span class="font-semibold">Volume per hit:</span> <?= $sensor['volume_per_hit'] ?> <?= htmlspecialchars($sensor['unit']) ?></div>
                    <div><span class="font-semibold">Status:</span> <?= getStatusBadge($sensor['status']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Edit Sensor Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Sensor</h3>
            <form id="editForm">
                <input type="hidden" id="sensorId" name="sensor_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" id="sensorName" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Volume per Hit</label>
                        <input type="number" id="sensorVolume" name="volume_per_hit" step="0.01" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <input type="text" id="sensorUnit" name="unit" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="sensorStatus" name="status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Management functionality
function editSensor(id, name, volumePerHit, unit, status) {
    document.getElementById('sensorId').value = id;
    document.getElementById('sensorName').value = name;
    document.getElementById('sensorVolume').value = volumePerHit;
    document.getElementById('sensorUnit').value = unit;
    document.getElementById('sensorStatus').value = status;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'update_sensor');
    
    fetch('manage.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating sensor');
    });
});

function confirmClearData() {
    if (confirm('Are you sure you want to clear all sensor readings? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'clear_readings');
        
        fetch('manage.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            alert('All readings have been cleared');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error clearing data');
        });
    }
}

function exportData() {
    window.location.href = 'export.php';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>