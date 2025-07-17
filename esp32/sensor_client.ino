#include <HTTPClient.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>

// WiFi credentials
const char *ssid = "YOUR_WIFI_SSID";
const char *password = "YOUR_WIFI_PASSWORD";

// API configuration
const char *serverURL = "https://sensors.bytefatale.eu/api/post_sensor_data.php";
const char *statsURL = "https://sensors.bytefatale.eu/api/get_cumulative_stats.php";
const char *apiKey = "abcd1234567890";

// Sensor configuration
const int NUM_SENSORS = 6;
const int sensorIds[NUM_SENSORS] = {1, 2, 3, 4, 5, 6};
const int sensorPins[NUM_SENSORS] = {2, 4, 5, 18, 19, 21};

// Memory storage for sensor stats (for LCD display)
int sensorCounts[NUM_SENSORS] = {0}; // Total counts for each sensor


// Timing
unsigned long lastSensorRead = 0;
const unsigned long sensorInterval = 30000; // 30 seconds between readings

void setup()
{
    Serial.begin(115200);
    Serial.println("ESP32 Sensor Client Starting...");

    // Initialize sensor pins as inputs
    for (int i = 0; i < NUM_SENSORS; i++)
    {
        pinMode(sensorPins[i], INPUT);
        
    }

    // Connect to WiFi network
    WiFi.begin(ssid, password);
    Serial.print("Connecting to WiFi");
    while (WiFi.status() != WL_CONNECTED)
    {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected!");
    Serial.print("IP address: ");
    Serial.println(WiFi.localIP());

    // Load cumulative stats into memory for LCD display
    // This will fetch the current counts from the server
    // Updates the sensorCounts array
    loadSensorStats();
    
    // Example: Access sensor data for LCD display
    Serial.println("=== Example: Accessing sensor data ===");
    for (int i = 0; i < NUM_SENSORS; i++)
    {
        // Access sensor ID: sensorIds[i]
        // Access sensor count: sensorCounts[i]
        Serial.println("Sensor " + String(sensorIds[i]) + ": " + String(sensorCounts[i]) + " triggers");
    }
    
    
}

void loop()
{
    // Check WiFi connection status
    if (WiFi.status() == WL_CONNECTED)
    {
        unsigned long currentTime = millis();

        // Check if it's time to read sensors (every 30 seconds)
        if (currentTime - lastSensorRead >= sensorInterval)
        {
            Serial.println("Polling sensors...");
            
            // Example: Post data for sensor 1 (you can modify this logic)
            // In a real application, you would read sensor states and detect changes
            bool success = postSensorData(1);
            
            if (success)
            {
                Serial.println("✓ Sensor data posted successfully");
                // Update local count for sensor 1
                sensorCounts[0]++; // Increment count for first sensor
            }
            else
            {
                Serial.println("✗ Failed to post sensor data");
            }
            
            // Reload stats to keep local memory in sync with server
            // Not necessary if you only update counts locally
            // but useful if you want to keep the display updated
            loadSensorStats();
            
            // Update last read timestamp
            lastSensorRead = currentTime;
        }
    }
    else
    {
        // WiFi connection lost - attempt to reconnect
        Serial.println("WiFi disconnected, reconnecting...");
        WiFi.begin(ssid, password);
    }

    // Small delay to prevent excessive CPU usage
    delay(1000);
}

// Function to load cumulative sensor statistics from the remote API
void loadSensorStats()
{
    Serial.println("Loading sensor stats...");
    
    WiFiClientSecure client;
    HTTPClient http;
    client.setInsecure();

    http.begin(client, statsURL);
    http.addHeader("X-API-Key", apiKey);

    int httpResponseCode = http.GET();

    if (httpResponseCode >= 200 && httpResponseCode < 300)
    {
        String response = http.getString();
        DynamicJsonDocument doc(2048);
        
        if (deserializeJson(doc, response) == DeserializationError::Ok && doc["success"])
        {
            JsonArray sensors = doc["data"];
            
            // Update local memory with API stats
            for (JsonObject sensor : sensors)
            {
                int sensorId = sensor["id"];
                int totalCount = sensor["total_count"];
                
                // Find matching sensor in our array and update count
                for (int i = 0; i < NUM_SENSORS; i++)
                {
                    if (sensorIds[i] == sensorId)
                    {
                        sensorCounts[i] = totalCount;
                        break;
                    }
                }
            }
            Serial.println("✓ Sensor stats loaded successfully");
        }
        else
        {
            Serial.println("✗ Failed to parse sensor stats");
        }
    }
    else
    {
        Serial.println("✗ Failed to load sensor stats");
    }

    http.end();
}

// Function to post sensor trigger data to the remote API
bool postSensorData(int sensorId)
{
    // Create secure WiFi client for HTTPS connection
    WiFiClientSecure client;
    HTTPClient http;
    client.setInsecure(); // Skip certificate validation (for development)

    // Build API URL with sensor ID parameter
    String url = String(serverURL) + "?sensor_id=" + String(sensorId);
    http.begin(client, url);
    http.addHeader("X-API-Key", apiKey);

    // Send POST request to trigger sensor event
    int httpResponseCode = http.POST("");

    bool success = false;
    if (httpResponseCode >= 200 && httpResponseCode < 300)
    {
        String response = http.getString();
        StaticJsonDocument<512> doc;
        
        // Parse JSON response to check if operation was successful
        if (deserializeJson(doc, response) == DeserializationError::Ok && doc["success"])
        {
            success = true;
        }
    }

    // Clean up HTTP connection
    http.end();
    return success;
}

/*
  Example: How to access sensor data in your code:
  
  // Get count for specific sensor
  int sensor1Count = sensorCounts[0]; // First sensor (ID 1)
  
  // Loop through all sensors
  for (int i = 0; i < NUM_SENSORS; i++) {
      int sensorId = sensorIds[i];
      int count = sensorCounts[i];
      // Use for LCD display, calculations, etc.
  }
  
  // Find sensor with specific ID
  int findSensorCount(int targetId) {
      for (int i = 0; i < NUM_SENSORS; i++) {
          if (sensorIds[i] == targetId) {
              return sensorCounts[i];
          }
      }
      return -1; // Not found
  }
  
  // Example: Real sensor reading with edge detection
  void checkSensorChanges() {
      for (int i = 0; i < NUM_SENSORS; i++) {
          int currentState = digitalRead(sensorPins[i]);
          
          // Detect rising edge (sensor triggered)
          if (currentState == HIGH && previousSensorStates[i] == LOW) {
              Serial.println("Sensor " + String(sensorIds[i]) + " triggered!");
              postSensorData(sensorIds[i]);
          }
          
          // Update previous state
          previousSensorStates[i] = currentState;
      }
  }
*/





