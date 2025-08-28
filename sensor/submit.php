<?php
$host = "localhost";
$dbname = "esp_data";
$username = "esp_user";
$password = "Esp_pass";

$suhu = $_POST['suhu'];
$kelembapan = $_POST['kelembapan'];

if (!isset($suhu) || !isset($kelembapan)) {
    die("Data tidak lengkap.");
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO sensor_data (suhu, kelembapan) VALUES (:suhu, :kelembapan)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':suhu', $suhu);
    $stmt->bindParam(':kelembapan', $kelembapan);
    $stmt->execute();

    echo "Data berhasil disimpan!";
} catch(PDOException $e) {
    echo "Gagal: " . $e->getMessage();
}
?>
