#include <WiFi.h>
#include <HTTPClient.h>

#define LDR_PIN 33
#define BUZZER_PIN 27

const int ambangTerang = 2800;
const int ambangGelap = 50;

bool sudahBunyiTerang = false;
bool sudahBunyiGelap = false;

const char* ssid = "Auvar0810";
const char* password = "harry1105123";

// Kirim ke post_data.php biar sinkron
const char* serverUrl = "https://aqua-owl-293538.hostingersite.com/post_data.php";

void bunyiCepat(int jumlahTit, int durasi = 100, int jeda = 100) {
  for (int i = 0; i < jumlahTit; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(durasi);
    digitalWrite(BUZZER_PIN, LOW);
    delay(jeda);
  }
}

void setup() {
  Serial.begin(115200);
  pinMode(LDR_PIN, INPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan ke WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi terhubung!");
  bunyiCepat(2);
}

void loop() {
  int ldrValue = analogRead(LDR_PIN);
  Serial.printf("[LDR] Nilai: %d\n", ldrValue);

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "ldr=" + String(ldrValue);

    int httpCode = http.POST(postData);  // ✅ Perbaikan ada di sini

    if (httpCode > 0) {
      String response = http.getString();
      Serial.print("Response: ");
      Serial.println(response);
    } else {
      Serial.printf("Gagal kirim, HTTP code: %d\n", httpCode);
    }

    http.end();
  }

  // Logika buzzer
  if (ldrValue > ambangTerang && !sudahBunyiTerang) {
    Serial.println("[BUZZER] Terang banget!");
    bunyiCepat(2);
    sudahBunyiTerang = true;
    sudahBunyiGelap = false;
  } else if (ldrValue < ambangGelap && !sudahBunyiGelap) {
    Serial.println("[BUZZER] Gelap banget!");
    bunyiCepat(3);
    sudahBunyiGelap = true;
    sudahBunyiTerang = false;
  }

  delay(5000);  // kirim setiap 5 detik
}
