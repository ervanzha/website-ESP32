    <?php
// Koneksi ke DB
$host = "localhost";
$dbname = "u683623856_esp_data";
$username = "u683623856_esp_user";
$password = "Esp_pass321";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil 20 data terbaru untuk suhu/kelembapan
    $stmtSensor = $conn->query("SELECT * FROM sensor_data ORDER BY waktu DESC LIMIT 20");
    $dataSensor = $stmtSensor->fetchAll(PDO::FETCH_ASSOC);

    // Ambil 20 data terbaru untuk LDR
    $stmtLdr = $conn->query("SELECT * FROM ldr_data ORDER BY waktu DESC LIMIT 20");
    $dataLdr = $stmtLdr->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Realtime Sensor ESP32</title>
    <meta http-equiv="refresh" content="10">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2 class="text-center mb-4">📡 Monitoring Realtime Sensor ESP32</h2>

    <!-- Grafik 2 kolom -->
    <div class="row">
        <!-- Suhu & Kelembapan -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">🌡️ Suhu & Kelembapan</div>
                <div class="card-body">
                    <canvas id="chartSuhu"></canvas>
                </div>
            </div>
        </div>

        <!-- LDR -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">💡 Intensitas Cahaya (LDR)</div>
                <div class="card-body">
                    <canvas id="chartLdr"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel gabungan -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">📋 Data Sensor Terbaru</div>
        <div class="card-body table-responsive">
            <h5>Suhu & Kelembapan</h5>
            <table class="table table-bordered mb-4">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Suhu (°C)</th>
                        <th>Kelembapan (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataSensor as $row): ?>
                        <tr>
                            <td><?= $row['waktu'] ?></td>
                            <td><?= $row['suhu'] ?></td>
                            <td><?= $row['kelembapan'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h5>LDR - Cahaya</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Nilai LDR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataLdr as $row): ?>
                        <tr>
                            <td><?= $row['waktu'] ?></td>
                            <td><?= $row['nilai'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SCRIPT GRAFIK -->
<script>
// Data suhu & kelembapan
const waktuSensor = <?= json_encode(array_reverse(array_column($dataSensor, 'waktu'))) ?>;
const suhu = <?= json_encode(array_reverse(array_column($dataSensor, 'suhu'))) ?>;
const kelembapan = <?= json_encode(array_reverse(array_column($dataSensor, 'kelembapan'))) ?>;

// Data LDR
const waktuLdr = <?= json_encode(array_reverse(array_column($dataLdr, 'waktu'))) ?>;
const ldr = <?= json_encode(array_reverse(array_column($dataLdr, 'nilai'))) ?>;

// Chart Suhu & Kelembapan
new Chart(document.getElementById('chartSuhu'), {
    type: 'line',
    data: {
        labels: waktuSensor,
        datasets: [
            {
                label: 'Suhu (°C)',
                data: suhu,
                borderColor: 'red',
                backgroundColor: 'rgba(255,0,0,0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Kelembapan (%)',
                data: kelembapan,
                borderColor: 'blue',
                backgroundColor: 'rgba(0,0,255,0.1)',
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Grafik Realtime Suhu & Kelembapan' },
            legend: { position: 'top' }
        },
        scales: { y: { beginAtZero: true } }
    }
});

// Chart LDR
new Chart(document.getElementById('chartLdr'), {
    type: 'line',
    data: {
        labels: waktuLdr,
        datasets: [{
            label: 'Nilai LDR',
            data: ldr,
            borderColor: 'orange',
            backgroundColor: 'rgba(255,165,0,0.3)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Grafik Intensitas Cahaya (LDR)' }
        },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

</body>
</html>
