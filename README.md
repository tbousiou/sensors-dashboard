# ESP32 Sensors Dashboard

A comprehensive IoT solution for monitoring multiple sensors using ESP32, with real-time data collection and web-based dashboard visualization.

## 🏗️ Project Overview

This project consists of:
- **ESP32 Client**: Reads sensor data and posts to remote API
- **Web Dashboard**: Displays real-time sensor statistics and historical data
- **REST API Backend**: Handles data storage and retrieval

## 📋 Features

- 6 configurable sensor inputs
- WiFi connectivity with auto-reconnection
- Secure HTTPS API communication
- Local memory caching of sensor statistics
- 30-second polling interval
- Real-time data visualization
- Cumulative statistics tracking

## 🔧 Hardware Requirements

### ESP32 Board
- ESP32 DevKit or compatible board
- USB cable for programming
- Stable power supply (5V/3.3V)

### Sensors
- 6x Digital sensors (motion detectors, buttons, switches, etc.)
- Pull-up/pull-down resistors if needed
- Jumper wires for connections

### Pin Configuration
| Sensor ID | GPIO Pin |
|-----------|----------|
| 1         | 2        |
| 2         | 4        |
| 3         | 5        |
| 4         | 18       |
| 5         | 19       |
| 6         | 21       |

## 🚀 Getting Started

### 1. Hardware Setup

1. Connect your sensors to the designated GPIO pins
2. Ensure proper grounding and power connections
3. Add pull-up resistors (10kΩ) if using switches/buttons

### 2. Software Installation

#### Prerequisites
- Arduino IDE 2.x or PlatformIO
- ESP32 board package installed
- Required libraries (see below)

#### Required Libraries
Install these libraries through Arduino IDE Library Manager:
```
WiFi (ESP32 built-in)
HTTPClient (ESP32 built-in)
WiFiClientSecure (ESP32 built-in)
ArduinoJson by Benoit Blanchon (v6.x)
```

### 3. Configuration

#### WiFi Setup
```cpp
const char *ssid = "YOUR_WIFI_SSID";
const char *password = "YOUR_WIFI_PASSWORD";
```

#### API Configuration
```cpp
const char *serverURL = "https://sensors.bytefatale.eu/api/post_sensor_data.php";
const char *statsURL = "https://sensors.bytefatale.eu/api/get_cumulative_stats.php";
const char *apiKey = "abcd1234567890";  // Replace with your API key
```

#### Sensor Customization
Modify these arrays to match your setup:
```cpp
const int sensorIds[NUM_SENSORS] = {1, 2, 3, 4, 5, 6};      // Logical IDs
const int sensorPins[NUM_SENSORS] = {2, 4, 5, 18, 19, 21};  // GPIO pins
```

### 4. Upload and Run

1. Connect ESP32 to computer via USB
2. Select correct board and port in Arduino IDE
3. Upload the sketch
4. Open Serial Monitor (115200 baud) to view status

## 📡 API Endpoints

### POST Sensor Data
```
POST https://sensors.bytefatale.eu/api/post_sensor_data.php?sensor_id={id}
Headers: X-API-Key: {your_api_key}
```

### GET Cumulative Statistics
```
GET https://sensors.bytefatale.eu/api/get_cumulative_stats.php
Headers: X-API-Key: {your_api_key}
```

### Response Format
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "total_count": 42,
      "last_triggered": "2024-01-15 10:30:00"
    }
  ]
}
```

## 💻 Code Usage Examples

### Accessing Sensor Data
```cpp
// Get count for specific sensor
int sensor1Count = sensorCounts[0]; // First sensor (ID 1)

// Loop through all sensors
for (int i = 0; i < NUM_SENSORS; i++) {
    int sensorId = sensorIds[i];
    int count = sensorCounts[i];
    Serial.println("Sensor " + String(sensorId) + ": " + String(count));
}
```

### Find Sensor by ID
```cpp
int findSensorCount(int targetId) {
    for (int i = 0; i < NUM_SENSORS; i++) {
        if (sensorIds[i] == targetId) {
            return sensorCounts[i];
        }
    }
    return -1; // Not found
}
```

## 🔍 Monitoring and Debugging

### Serial Output
The ESP32 provides detailed logging:
```
Connecting to WiFi....
WiFi connected!
Loading sensor stats...
✓ Sensor stats loaded successfully
=== Example: Accessing sensor data ===
Sensor 1: 42 triggers
Sensor 2: 15 triggers
...
```

### Status Indicators
- `✓` Success operations
- `✗` Failed operations
- WiFi connection status
- HTTP response codes

## ⚠️ Troubleshooting

### WiFi Issues
- **Connection fails**: Check SSID/password
- **Frequent disconnections**: Verify signal strength
- **IP conflicts**: Restart router/ESP32

### API Communication
- **HTTP 401**: Invalid API key
- **HTTP 404**: Check server URL
- **SSL errors**: Ensure stable internet connection
- **Timeout errors**: Check network stability

### Sensor Reading
- **No sensor triggers**: Verify wiring and pin configuration
- **False readings**: Add debouncing or filtering
- **Inconsistent data**: Check power supply stability

### Memory Issues
- **JSON parsing fails**: Increase buffer size in `DynamicJsonDocument`
- **Stack overflow**: Reduce local variables or use heap allocation

## 📊 Performance Optimization

### Timing Configuration
```cpp
const unsigned long sensorInterval = 30000; // Adjust polling frequency
```

### Memory Management
- Current JSON buffer: 2048 bytes
- Sensor array size: 6 sensors (configurable)
- HTTP timeout: Default (adjust if needed)

## 🔒 Security Considerations

- API key authentication
- HTTPS encrypted communication
- `client.setInsecure()` used for development (consider proper certificates for production)

## 🛠️ Customization

### Adding More Sensors
1. Increase `NUM_SENSORS` constant
2. Add entries to `sensorIds[]` and `sensorPins[]` arrays
3. Ensure sufficient GPIO pins available

### Changing Polling Frequency
Modify `sensorInterval` (minimum recommended: 10 seconds)

### Custom Sensor Logic
Implement custom reading logic in `pollAndPostSensors()` function

## 📞 Support

For issues and questions:
- Check Serial Monitor output
- Verify hardware connections
- Confirm API endpoint accessibility
- Review network connectivity

## 📄 License

This project is open source. Please check individual component licenses.

---

**Note**: Replace placeholder values (WiFi credentials, API key) with your actual configuration before deployment.