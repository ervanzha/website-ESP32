#include <WiFi.h>
#include <micro_ros_arduino.h>
#include "DHT.h"

#include <rcl/rcl.h>
#include <rclc/rclc.h>
#include <rclc/executor.h>
#include <std_msgs/msg/float32.h>

char ssid[] = "Trisakti";               
char password[] = "Informatika";      
char agent_ip[] = "192.168.1.8"; 
size_t agent_port = 8888;           

#define DHTPIN 23
#define DHTTYPE DHT11
#define BUZZER_PIN 22

DHT dht(DHTPIN, DHTTYPE);

rcl_publisher_t publisher_temp;
rcl_publisher_t publisher_humid;
std_msgs_msg_Float32 msg_temp;
std_msgs_msg_Float32 msg_humid;

rclc_executor_t executor;
rclc_support_t support;
rcl_node_t node;
rcl_timer_t timer;

void timer_callback(rcl_timer_t * timer, int64_t last_call_time) {
  RCLC_UNUSED(last_call_time);

  float suhu = dht.readTemperature();
  float kelembapan = dht.readHumidity();

  if (isnan(suhu) || isnan(kelembapan)) {
    Serial.println("Gagal membaca sensor DHT11");
    return;
  }

  if (suhu >= 50.0) {
    digitalWrite(BUZZER_PIN, HIGH);
  } else {
    digitalWrite(BUZZER_PIN, LOW);
  }

  msg_temp.data = suhu;
  msg_humid.data = kelembapan;

  rcl_publish(&publisher_temp, &msg_temp, NULL);
  rcl_publish(&publisher_humid, &msg_humid, NULL);

  Serial.printf("Dikirim | Suhu: %.1f °C | Kelembapan: %.1f %%\n", suhu, kelembapan);
}

void setup() {
  Serial.begin(115200);
  delay(2000);

  dht.begin();
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  set_microros_wifi_transports(ssid, password, agent_ip, agent_port);

  rcl_allocator_t allocator = rcl_get_default_allocator();
  rclc_support_init(&support, 0, NULL, &allocator);
  rclc_node_init_default(&node, "esp32_dht_node", "", &support);

  rclc_publisher_init_default(
    &publisher_temp,
    &node,
    ROSIDL_GET_MSG_TYPE_SUPPORT(std_msgs, msg, Float32),
    "dht_suhu");

  rclc_publisher_init_default(
    &publisher_humid,
    &node,
    ROSIDL_GET_MSG_TYPE_SUPPORT(std_msgs, msg, Float32),
    "dht_kelembapan");

  rclc_timer_init_default(
    &timer,
    &support,
    RCL_MS_TO_NS(5000),  
    timer_callback);

  rclc_executor_init(&executor, &support.context, 1, &allocator);
  rclc_executor_add_timer(&executor, &timer);

  Serial.println("micro-ROS Node ESP32 siap jalan!");
}

void loop() {
  rclc_executor_spin_some(&executor, RCL_MS_TO_NS(100));
}
