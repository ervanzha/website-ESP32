<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$dbuser = 'esp_user';
$dbpass = 'Esp_pass';
$dbname = 'esp_data';

$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$role = $_SESSION['role'];

$sensorData = mysqli_query($conn, "SELECT * FROM sensor_data ORDER BY waktu DESC LIMIT 20");
$dataSensor = mysqli_fetch_all($sensorData, MYSQLI_ASSOC);

$ldrData = mysqli_query($conn, "SELECT * FROM ldr_data ORDER BY waktu DESC LIMIT 20");
$dataLdr = mysqli_fetch_all($ldrData, MYSQLI_ASSOC);

$latestSensor = mysqli_query($conn, "SELECT suhu, kelembapan, waktu FROM sensor_data ORDER BY waktu DESC LIMIT 1");
$latestLdr = mysqli_query($conn, "SELECT nilai, waktu FROM ldr_data ORDER BY waktu DESC LIMIT 1");

$currentSuhu = mysqli_fetch_assoc($latestSensor)['suhu'] ?? 'N/A';
$currentKelembapan = mysqli_fetch_assoc($latestSensor)['kelembapan'] ?? 'N/A';
$currentLdr = mysqli_fetch_assoc($latestLdr)['nilai'] ?? 'N/A';
$latestSensorTime = mysqli_fetch_assoc($latestSensor)['waktu'] ?? 'N/A';
$latestLdrTime = mysqli_fetch_assoc($latestLdr)['waktu'] ?? 'N/A';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ESP32 Monitoring Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5; /* Light gray background */
            color: #333;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e0e0e0;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); /* Subtle shadow */
        }
        .navbar-brand {
            font-weight: 700;
            color: #007bff !important;
            font-size: 1.5rem;
        }
        .nav-link {
            color: #555 !important;
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: #007bff !important;
        }
        .navbar-text {
            color: #666 !important;
        }

        /* Main Container Styling */
        .main-content-wrapper {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            margin: 30px auto;
            max-width: 1200px; /* Max width for content, adjust as needed */
        }

        /* Section Content General */
        .section-content {
            display: none;
            padding: 20px 0; /* Adjusted padding */
            min-height: calc(100vh - 200px); /* Adjust based on navbar/footer height */
        }
        section.active {
            display: block;
        }

        #beranda {
            padding: 0; 
        }
        .card-info {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px; /* Space between cards */
        }
        .card-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .card-info img {
            width: 100%;
            height: 250px;
            object-fit: cover; 
        }
        .card-info .card-body {
            padding: 25px;
        }
        .card-info .card-title {
            font-weight: 600;
            font-size: 1.4rem;
            color: #007bff;
            margin-bottom: 15px;
        }
        .card-info .card-text {
            color: #555;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .card-info .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: #777;
            padding: 15px 25px;
        }

        /* General Chart/Table/Map/Camera Styling */
        #chartSuhu, #chartKelembapan, #chartLdr {
            height: 350px; /* Set a fixed height for charts, slightly taller */
            width: 100%;
            margin-bottom: 30px; /* Space below charts */
        }
        .table-bordered {
            border: 1px solid #dee2e6;
            border-radius: 8px; /* Rounded corners for table */
            overflow: hidden; /* Ensures rounded corners apply to table */
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 12px;
        }
        .table-bordered thead {
            background-color: #007bff;
            color: white;
        }
        #map {
            height: 500px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        #kamera img {
            border: 2px solid #007bff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        #kamera p.text-muted {
            font-size: 0.9rem;
            margin-top: 10px;
        }

        /* Footer */
        footer {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">ESP32 Monitoring</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" onclick="showSection('beranda')">🏠 Beranda</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownSuhu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        🌡️ Suhu & Kelembapan
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownSuhu">
                        <li><a class="dropdown-item" onclick="showSection('grafik_suhu')">📊 Grafik</a></li>
                        <li><a class="dropdown-item" onclick="showSection('tabel_suhu')">📋 Tabel</a></li>
                        <li><a class="dropdown-item" onclick="showSection('lokasi')">🗺️ Lokasi</a></li>
                        <li><a class="dropdown-item" onclick="showSection('kamera')">📷 Kamera</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLDR" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        💡 Intensitas LDR
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownLDR">
                        <li><a class="dropdown-item" onclick="showSection('grafik_ldr')">📊 Grafik</a></li>
                        <li><a class="dropdown-item" onclick="showSection('tabel_ldr')">📋 Tabel</a></li>
                        <li><a class="dropdown-item" onclick="showSection('lokasi')">🗺️ Lokasi</a></li>
                        <li><a class="dropdown-item" onclick="showSection('kamera')">📷 Kamera</a></li>
                    </ul>
                </li>
            </ul>
            <span class="navbar-text me-3">👤 <?= $_SESSION['user'] ?> (<?= $role ?>)</span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container main-content-wrapper"> <section id="beranda" class="section-content active">
    <h2 class="text-center mb-5 fw-bold" style="color: #007bff;">Informasi Sistem Monitoring</h2>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-info">
                <img src="https://i.ibb.co/pvckSnp8/Monitoring-Suhu-Kelembapan-dan-Intensitas-Cahaya-g-AMBAR.jpg" class="card-img-top" alt="Monitoring Suhu, Kelembapan, dan Intensitas Cahaya">
                <div class="card-body">
                    <h5 class="card-title">Monitoring Suhu, Kelembapan, dan Intensitas Cahaya dengan ESP32</h5>
                    <p class="card-text">Website ini menyediakan pemantauan suhu, kelembapan, dan intensitas cahaya secara real-time menggunakan perangkat ESP32. Dilengkapi dengan tampilan grafik, tabel, peta lokasi, dan kamera. Anda dapat memantau kondisi lingkungan dengan mudah dan efisien.</p>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Diperbarui: 5/8/2024 - 1 min read</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-info">
                <img src="https://i.ibb.co/SwsF63H0/b1-jpg.png" alt="b1-jpg" border="0"></a>" class="card-img-top" alt="Monitoring Suhu dan Kelembapan Secara Real-Time">
                <div class="card-body">
                    <h5 class="card-title">Analisis Data Suhu dan Kelembapan Secara Real-Time</h5>
                    <p class="card-text">Dapatkan informasi suhu dan kelembapan terkini dari sensor ESP32 Anda. Sistem ini menampilkan data dalam bentuk grafik yang mudah dipahami, tabel detail, serta lokasi perangkat. Temukan solusi terbaik untuk kebutuhan monitoring Anda di sini.</p>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Diperbarui: 5/8/2024 - 1 min read</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-info">
                <img src="https://i.ibb.co/5gJ6H064/3web.jpg" class="card-img-top" alt="Monitoring Intensitas LDR">
                <div class="card-body">
                    <h5 class="card-title">Pengawasan Intensitas Cahaya Lingkungan dengan LDR</h5>
                    <p class="card-text">Pantau tingkat pencahayaan di sekitar perangkat Anda menggunakan sensor LDR. Data intensitas cahaya disajikan secara visual melalui grafik dan tabel untuk memudahkan analisis perubahan kondisi cahaya dari waktu ke waktu.</p>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Diperbarui: 5/8/2024 - 1 min read</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-info">
                <img src="https://i.ibb.co/twTcR5qX/d.jpg" class="card-img-top" alt="Lokasi dan Kamera">
                <div class="card-body">
                    <h5 class="card-title">Pemetaan Lokasi dan Tampilan Kamera Perangkat</h5>
                    <p class="card-text">Lihat lokasi geografis perangkat Anda secara real-time melalui peta interaktif. Fitur kamera live juga memungkinkan Anda untuk melihat lingkungan sekitar perangkat, memberikan pemahaman visual yang komprehensif.</p>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Diperbarui: 5/8/2024 - 1 min read</small>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="grafik_suhu" class="section-content">
    <h4 class="mb-4 text-center">📊 Data Grafik Suhu & Kelembapan</h4>
    <div class="row">
        <div class="col-md-6"><canvas id="chartSuhu"></canvas></div>
        <div class="col-md-6"><canvas id="chartKelembapan"></canvas></div>
    </div>
</section>

<section id="grafik_ldr" class="section-content">
    <h4 class="mb-4 text-center">📊 Data Grafik LDR</h4>
    <div class="row">
        <div class="col-md-12"><canvas id="chartLdr"></canvas></div>
    </div>
</section>

<section id="tabel_suhu" class="section-content">
    <h4 class="mb-4 text-center">📋 Data Tabel Suhu & Kelembapan</h4>
    <table class="table table-bordered table-sm">
        <thead><tr><th>Waktu</th><th>Suhu (°C)</th><th>Kelembapan (%)</th></tr></thead>
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
</section>

<section id="tabel_ldr" class="section-content">
    <h4 class="mb-4 text-center">📋 Data Tabel LDR - Cahaya</h4>
    <table class="table table-bordered table-sm">
        <thead><tr><th>Waktu</th><th>Nilai LDR</th></tr></thead>
        <tbody>
            <?php foreach ($dataLdr as $row): ?>
            <tr>
                <td><?= $row['waktu'] ?></td>
                <td><?= $row['nilai'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section id="lokasi" class="section-content">
    <h4 class="mb-4 text-center">🗺️ Lokasi Realtime Perangkat</h4>
    <div id="map"></div>
</section>

<section id="kamera" class="section-content">
  <h4>📷 Kamera Live Drone</h4>
  <p>Berikut adalah tampilan live dari kamera ESP32-CAM melalui YouTube:</p>
  <div class="ratio ratio-16x9">
    <iframe src="https://www.youtube.com/embed/WqrkMoAoSts" 
            title="ESP32 CAM Live" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
    </iframe>
  </div>
  <p class="text-muted mt-2">Streaming via YouTube Live.</p>
</section>


</div> <footer style="background-color: #333; color: white; padding: 40px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h3 style="margin-bottom: 25px; font-size: 1.8em; color: white;">TENTANG KAMI</h3>

        <div style="display: flex; justify-content: center; gap: 30px; margin-bottom: 30px;">
            <a href="https://www.instagram.com/favianqd/" target="_blank" style="color: white; text-decoration: none;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" alt="Instagram" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
            </a>
            <a href="https://www.linkedin.com/in/reprafs" target="_blank" style="color: white; text-decoration: none;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png" alt="LinkedIn" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
            </a>
            <a href="https://www.youtube.com/@muhammadervanzha4821" target="_blank" style="color: white; text-decoration: none;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/09/YouTube_full-color_icon_%282017%29.svg" alt="YouTube" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
            </a>
            <a href="https://www.tiktok.com/@nontotalos" target="_blank" style="color: white; text-decoration: none;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Tiktok_icon.svg/96px-Tiktok_icon.svg.png" alt="TikTok" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
            </a>
        </div>

        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 60px; margin-bottom: 40px;">
            <div style="text-align: center; background-color: black; color: white; padding: 15px 30px; border-radius: 8px;">
                <p style="margin: 0; font-size: 1.4em; font-weight: bold;">+62 811 2222 333</p>
                <p style="margin: 5px 0 0; font-size: 1.1em;">Ervan</p>
            </div>
            <div style="text-align: center; background-color: black; color: white; padding: 15px 30px; border-radius: 8px;">
                <p style="margin: 0; font-size: 1.4em; font-weight: bold;">+62 822 3333 444</p>
                <p style="margin: 5px 0 0; font-size: 1.1em;">Favian</p>
            </div>
        </div>

        <p style="font-size: 0.9em; opacity: 0.8;">&copy; <?= date('Y') ?> ESP32 Monitoring. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
function showSection(id) {
    document.querySelectorAll('.section-content').forEach(el => el.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    if (id === 'lokasi') {
        setTimeout(() => map.invalidateSize(), 300);
    }
}


const waktuSensor = <?= json_encode(array_reverse(array_column($dataSensor, 'waktu'))) ?>;
const suhu = <?= json_encode(array_reverse(array_column($dataSensor, 'suhu'))) ?>;
const kelembapan = <?= json_encode(array_reverse(array_column($dataSensor, 'kelembapan'))) ?>;
const waktuLdr = <?= json_encode(array_reverse(array_column($dataLdr, 'waktu'))) ?>;
const ldr = <?= json_encode(array_reverse(array_column($dataLdr, 'nilai'))) ?>;


new Chart(document.getElementById('chartSuhu'), {
    type: 'line',
    data: {
        labels: waktuSensor,
        datasets: [{
            label: 'Suhu (°C)',
            data: suhu,
            borderColor: 'red',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});


new Chart(document.getElementById('chartKelembapan'), {
    type: 'line',
    data: {
        labels: waktuSensor,
        datasets: [{
            label: 'Kelembapan (%)',
            data: kelembapan,
            borderColor: 'blue',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});


new Chart(document.getElementById('chartLdr'), {
    type: 'line',
    data: {
        labels: waktuLdr,
        datasets: [{
            label: 'Intensitas LDR',
            data: ldr,
            borderColor: 'green',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});


let map = L.map('map').setView([-6.2, 106.8], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

let markers = {};
function loadMarkers() {
    fetch('get_gps_data.php')
        .then(res => res.json())
        .then(data => {
            data.forEach(dev => {
                const latlng = [parseFloat(dev.latitude), parseFloat(dev.longitude)];
                if (markers[dev.device_id]) {
                    markers[dev.device_id].setLatLng(latlng);
                } else {
                    markers[dev.device_id] = L.marker(latlng)
                        .addTo(map)
                        .bindPopup("Device: " + dev.device_id + "<br>Waktu: " + dev.waktu);
                }
            });
        })
        .catch(error => console.error('Error fetching GPS data:', error));
}
setInterval(loadMarkers, 5000);
loadMarkers();

document.addEventListener('DOMContentLoaded', function() {
    showSection('beranda'); 
});
</script>
</body>
</html>
