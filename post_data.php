<?php
date_default_timezone_set('Asia/Jakarta');
$conn = mysqli_connect("localhost", "u683623856_esp_user", "Esp_pass321", "u683623856_esp_data");

$device     = $_POST['device_id'] ?? null;
$lat        = $_POST['lat'] ?? null;
$lon        = $_POST['lon'] ?? null;
$suhu       = $_POST['suhu'] ?? null;
$kelembapan = $_POST['kelembapan'] ?? null;
$ldr        = $_POST['ldr'] ?? null;
$waktu      = date('Y-m-d H:i:s');

// Jika tidak ada lat/lon dari ESP, coba pakai IP
if (!$lat || !$lon) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $geo = @file_get_contents("https://ipapi.co/{$ip}/json/");
    $geoData = json_decode($geo, true);
    $lat = $geoData['latitude'] ?? null;
    $lon = $geoData['longitude'] ?? null;
}

if ($suhu && $kelembapan) {
    $stmt = $conn->prepare("INSERT INTO sensor_data (suhu, kelembapan, waktu) VALUES (?, ?, ?)");
    $stmt->bind_param("dds", $suhu, $kelembapan, $waktu);
    $stmt->execute();
}

if ($device && $lat && $lon) {
    $stmt = $conn->prepare("INSERT INTO gps_data (device_id, latitude, longitude, waktu) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdds", $device, $lat, $lon, $waktu);
    $stmt->execute();
}

if ($ldr) {
    $stmt = $conn->prepare("INSERT INTO ldr_data (nilai, waktu) VALUES (?, ?)");
    $stmt->bind_param("ds", $ldr, $waktu);
    $stmt->execute();
}

echo "OK";
?>
