<?php
$conn = mysqli_connect("localhost", "u683623856_esp_user", "Esp_pass321", "u683623856_esp_data");

$query = "SELECT gps1.device_id, gps1.latitude, gps1.longitude
          FROM gps_data gps1
          INNER JOIN (
              SELECT device_id, MAX(waktu) as max_waktu
              FROM gps_data
              GROUP BY device_id
          ) gps2 ON gps1.device_id = gps2.device_id AND gps1.waktu = gps2.max_waktu";

$result = mysqli_query($conn, $query);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
