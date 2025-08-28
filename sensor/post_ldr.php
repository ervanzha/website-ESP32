<?php
$host = "localhost";
$dbname = "esp_data";
$username = "esp_user";
$password = "Esp_pass";

$ldr = $_POST['ldr'];

if (!isset($ldr)) {
    die("Data tidak lengkap.");
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO ldr_data (nilai) VALUES (:nilai)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nilai', $ldr);
    $stmt->execute();

    echo "OK";
} catch(PDOException $e) {
    echo "Gagal: " . $e->getMessage();
}
?>
