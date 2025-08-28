<?php
$host = "localhost";
$dbname = "u683623856_esp_data";
$username = "u683623856_esp_user";
$password = "Esp_pass321";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
