<!DOCTYPE html>
<html>
<head>
  <title>Live GPS Map</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    #map { height: 600px; width: 100%; }
  </style>
</head>
<body>

<h3>🌍 Lokasi Real-Time ESP32</h3>
<div id="map" style="height: 500px;"></div>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map = L.map('map').setView([-6.2, 106.8], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap'
}).addTo(map);

let markers = {};

function loadMarkers() {
  fetch("https://aqua-owl-293538.hostingersite.com/get_gps_data.php")
    .then(res => res.json())
    .then(data => {
      data.forEach(dev => {
        if (markers[dev.device_id]) {
          markers[dev.device_id].setLatLng([dev.latitude, dev.longitude]);
        } else {
          markers[dev.device_id] = L.marker([dev.latitude, dev.longitude])
            .addTo(map)
            .bindPopup(`Device: ${dev.device_id}`);
        }
      });
    });
}
setInterval(loadMarkers, 5000);
loadMarkers();
</script>

</body>
</html>
