<?php
date_default_timezone_set('Asia/Jakarta');

$conn = mysqli_connect("localhost", "esp_user", "Esp_pass", "esp_data");

$device = $_POST['device_id'] ?? '';
$lat    = $_POST['lat'] ?? '';
$lon    = $_POST['lon'] ?? '';

if ($device && $lat && $lon) {
    $stmt = $conn->prepare("INSERT INTO gps_data (device_id, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $device, $lat, $lon);
    $stmt->execute();
    echo "OK";
} else {
    echo "Missing data";
}
