#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>

// --- WiFi Settings ---
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// --- MQTT Settings ---
const char* mqtt_server = "broker.hivemq.com";
const int mqtt_port = 1883;

WiFiClient espClient;
PubSubClient client(espClient);

unsigned long lastMsg = 0;
#define MSG_INTERVAL 5000 // Send data every 5 seconds

void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
}

void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  
  String messageTemp;
  for (int i = 0; i < length; i++) {
    Serial.print((char)payload[i]);
    messageTemp += (char)payload[i];
  }
  Serial.println();

  // Handle incoming commands
  if (String(topic) == "cta/commands") {
    StaticJsonDocument<200> doc;
    DeserializationError error = deserializeJson(doc, messageTemp);
    if (!error) {
      if (doc.containsKey("fan")) {
        bool fanOn = doc["fan"];
        Serial.print("Fan state changed to: ");
        Serial.println(fanOn ? "ON" : "OFF");
        // digitalWrite(FAN_PIN, fanOn ? HIGH : LOW);
      }
    }
  }
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    
    // Create a random client ID
    String clientId = "ESP32Client-";
    clientId += String(random(0xffff), HEX);
    
    if (client.connect(clientId.c_str())) {
      Serial.println("connected");
      // Subscribe to commands topic
      client.subscribe("cta/commands");
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  unsigned long now = millis();
  if (now - lastMsg > MSG_INTERVAL) {
    lastMsg = now;

    // Read real sensors here, generating fake data for demo
    float temp = 24.5 + random(-10, 10) / 10.0;
    float hum = 60.0 + random(-50, 50) / 10.0;
    float current = 3.2 + random(-5, 5) / 10.0;

    StaticJsonDocument<200> doc;
    doc["temperature"] = temp;
    doc["humidity"] = hum;
    doc["current"] = current;

    char msg[128];
    serializeJson(doc, msg);

    Serial.print("Publishing message: ");
    Serial.println(msg);
    client.publish("cta/sensors", msg);
  }
}
