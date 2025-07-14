#ifndef CONFIG_H
#define CONFIG_H

// WiFi Configuration
#define WIFI_SSID "YOUR_WIFI_SSID"
#define WIFI_PASSWORD "YOUR_WIFI_PASSWORD"

// API Configuration
#define SERVER_URL "http://your-domain.com/api/post_sensor_data.php"
#define API_KEY "YOUR_API_KEY"

// Sensor Configuration
#define NUM_SENSORS 6
#define SENSOR_READ_INTERVAL 30000 // 30 seconds

// Sensor IDs (must match database)
const int SENSOR_IDS[NUM_SENSORS] = {1, 2, 3, 4, 5, 6};

// GPIO pins for sensors
const int SENSOR_PINS[NUM_SENSORS] = {2, 4, 5, 18, 19, 21};

#endif
