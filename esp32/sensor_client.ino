#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>

// WiFi credentials
const char *ssid = "YOUR_WIFI_SSID";
const char *password = "YOUR_WIFI_PASSWORD";

// API configuration
const char *serverURL = "https://sensors.bytefatale.eu/api/post_sensor_data.php";
const char *apiKey = "YOUR_API_KEY";

// Sensor configuration
const int NUM_SENSORS = 6;
const int sensorIds[NUM_SENSORS] = {1, 2, 3, 4, 5, 6};     // Adjust IDs as needed
const int sensorPins[NUM_SENSORS] = {2, 4, 5, 18, 19, 21}; // GPIO pins for sensors

// Timing
unsigned long lastSensorRead = 0;
const unsigned long sensorInterval = 30000; // 30 seconds between readings

void setup()
{
    Serial.begin(115200);

    // Initialize sensor pins
    for (int i = 0; i < NUM_SENSORS; i++)
    {
        pinMode(sensorPins[i], INPUT);
    }

    // Connect to WiFi
    WiFi.begin(ssid, password);
    Serial.print("Connecting to WiFi");

    while (WiFi.status() != WL_CONNECTED)
    {
        delay(500);
        Serial.print(".");
    }

    Serial.println();
    Serial.println("WiFi connected!");
    Serial.print("IP address: ");
    Serial.println(WiFi.localIP());
}

void loop()
{
    if (WiFi.status() == WL_CONNECTED)
    {
        unsigned long currentTime = millis();

        // Check if it's time to read sensors
        if (currentTime - lastSensorRead >= sensorInterval)
        {
            readAndPostAllSensors();
            lastSensorRead = currentTime;
        }
    }
    else
    {
        Serial.println("WiFi disconnected, reconnecting...");
        WiFi.begin(ssid, password);
    }

    delay(1000);
}

void readAndPostAllSensors()
{
    Serial.println("Reading all sensors...");

    for (int i = 0; i < NUM_SENSORS; i++)
    {
        // Read sensor (adjust logic based on your sensor type)
        int sensorValue = digitalRead(sensorPins[i]);

        // Only post if sensor is triggered (HIGH)
        if (sensorValue == HIGH)
        {
            postSensorData(sensorIds[i]);
            delay(100); // Small delay between requests
        }
    }
}

void postSensorData(int sensorId)
{
    WiFiClientSecure client;
    HTTPClient http;

    client.setInsecure();

    String url = String(serverURL) + "?sensor_id=" + String(sensorId);

    http.begin(client, url);
    http.addHeader("X-API-Key", apiKey);

    String postData = "";
    int httpResponseCode = http.POST(postData);

    if (httpResponseCode >= 200 && httpResponseCode < 300)
    {
        String response = http.getString();

        // Parse JSON response
        StaticJsonDocument<512> doc;
        DeserializationError error = deserializeJson(doc, response);

        if (error)
        {
            Serial.println("✗ JSON parsing failed for sensor " + String(sensorId));
        }
        else if (doc["success"])
        {
            Serial.println("✓ Sensor " + String(sensorId) + " data posted successfully");
        }
        else
        {
            Serial.println("✗ API Error for sensor " + String(sensorId) + ": " + doc["error"].as<String>());
        }
    }
    else
    {
        Serial.println("✗ HTTP Error for sensor " + String(sensorId) + ": " + String(httpResponseCode));
    }

    http.end();
}

// Alternative function to post data with custom timestamp
void postSensorDataWithTimestamp(int sensorId, String timestamp)
{
    WiFiClientSecure client;
    HTTPClient http;

    client.setInsecure();

    String url = String(serverURL) + "?sensor_id=" + String(sensorId);

    http.begin(client, url);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    http.addHeader("X-API-Key", apiKey);

    // Include timestamp in POST data
    String postData = "timestamp=" + timestamp;

    int httpResponseCode = http.POST(postData);

    if (httpResponseCode > 0)
    {
        String response = http.getString();
        Serial.println("Sensor " + String(sensorId) + " response: " + response);
    }
    else
    {
        Serial.println("HTTP Error for sensor " + String(sensorId) + ": " + String(httpResponseCode));
    }

    http.end();
}

// Function to post to all sensors at once (for testing)
void postToAllSensors()
{
    for (int i = 0; i < NUM_SENSORS; i++)
    {
        postSensorData(sensorIds[i]);
        delay(200); // Delay between requests
    }
}
// Function to post to all sensors at once (for testing)
void postToAllSensors()
{
    for (int i = 0; i < NUM_SENSORS; i++)
    {
        postSensorData(sensorIds[i]);
        delay(200); // Delay between requests
    }
}
// Function to post to all sensors at once (for testing)
void postToAllSensors()
{
    for (int i = 0; i < NUM_SENSORS; i++)
    {
        postSensorData(sensorIds[i]);
        delay(200); // Delay between requests
    }
}
