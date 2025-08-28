<?php
if (!isset($_GET['ip'])) {
    echo json_encode(['error' => 'IP not provided']);
    exit;
}

$ip = $_GET['ip'];

// API Geolokasi
$url = "https://ipapi.co/{$ip}/json/";

$response = file_get_contents($url);
if ($response === false) {
    echo json_encode(['error' => 'Failed to fetch data']);
    exit;
}

$data = json_decode($response, true);

// Ambil koordinat
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;

echo json_encode([
    'latitude' => $latitude,
    'longitude' => $longitude
]);
