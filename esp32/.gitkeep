#include <WiFi.h>
#include <HTTPClient.h>
#include "DHT.h"

#define DHTPIN 23       
#define DHTTYPE DHT11   
#define BUZZER_PIN 22   

const char* ssid = "trisakti";          
const char* password = "informatika"; 
const char* serverName = "https://aqua-owl-293538.hostingersite.com/post_data.php"; 

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(115200);
  dht.begin();
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  
  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi terhubung");
}

void loop() {
  float suhu = dht.readTemperature();
  float kelembapan = dht.readHumidity();

  if (isnan(suhu) || isnan(kelembapan)) {
    Serial.println("Gagal membaca sensor DHT11");
    delay(10000);
    return;
  }

  Serial.printf("Suhu: %.1f °C, Kelembapan: %.1f %%\n", suhu, kelembapan);

  if (suhu >= 50.0) {
    digitalWrite(BUZZER_PIN, HIGH);
  } else {
    digitalWrite(BUZZER_PIN, LOW);
  }

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "device_id=DHT11-01";
    postData += "&suhu=" + String(suhu, 1);
    postData += "&kelembapan=" + String(kelembapan, 1);

    int httpResponseCode = http.POST(postData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Response: " + response);
    } else {
      Serial.printf("Error: %d\n", httpResponseCode);
    }

    http.end();
  }

  delay(15000); 
}
