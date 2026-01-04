#include "DHT.h"
#include <ESP8266WiFi.h>

#define DHTPIN 2
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

const char* ssid= "";
const char* password = "";

const char* server = "http://192.168.1.81/Humidity/logs.php";

void setup(){
  Serial.begin(9600);
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while(WiFi.status() != WL_CONNECTED){
    delay(500);
    Serial.print(".");
  }
  Serial.println("Connected!");
  dht.begin();
}

// Add global variables to store previous readings
float lastTemperature = -999.0; // Initialize with an impossible value
float lastHumidity = -999.0;   // Initialize with an impossible value

void loop(){
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();

  if(isnan(humidity) || isnan(temperature)){
    Serial.println("Failed to read from DHT sensor!");
    // Consider a short delay here to prevent rapid retries on failure
    delay(1000);
    return;
  }

  // Define thresholds
  float tempThreshold = 0.1; // e.g., only log if temp changes by 0.1C
  float humThreshold = 1.0;  // e.g., only log if humidity changes by 1%

  // Check if readings have significantly changed
  if (abs(temperature - lastTemperature) > tempThreshold ||
      abs(humidity - lastHumidity) > humThreshold) {

    // Only send if there's a significant change
    String url= String(server) + "?temperature=" +temperature + "&humidity=" +humidity;
    WiFiClient client;

    if(client.connect("192.168.1.81", 80)){
      client.print(String("GET ") + url + " HTTP/1.1\r\n" +
                  "Host: localhost\r\n" +
                  "connection: close\r\n\r\n");
      Serial.println("Data sent to server");
      client.stop();

      // Update last readings only if sent successfully
      lastTemperature = temperature;
      lastHumidity = humidity;
    } else {
      Serial.println("Failed to connect to server");
    }
  } else {
    Serial.println("Readings unchanged significantly. Not sending.");
  }

  delay(10000); // Still delay for 10 seconds between *checks*
}
