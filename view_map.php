<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>EnvLog | View Map</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="<?php echo $theme_color; ?>" />
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="node_modules/leaflet/dist/leaflet.css"/>
  <style>
    html, body, #map {
      height: 100%;
      margin: 0;
    }
  </style>
</head>
<body>

<main id="map"></main>

<?php require_once 'navigation.php'; ?>

<script src="node_modules/leaflet/dist/leaflet.js"></script>
<script>
  // Define base layers
  const satellite = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Mapping by <a href="https://verdantbytes.com" target="_blank">VerdantBytes</a> | Tiles &copy; Esri &mdash; i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP'
    });

  const openStreet = L.tileLayer(
    'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: 'Mapping by <a href="https://verdantbytes.com" target="_blank">VerdantBytes</a> | &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    });

  // Initialise the map with Satellite view
  const map = L.map('map', {
    center: [51.505, -0.09],
    zoom: 13,
    layers: [satellite]
  });

  // Layer control
  const baseMaps = {
    "Satellite": satellite,
    "Open Street Map": openStreet
  };
  L.control.layers(baseMaps).addTo(map);

  // Marker group to adjust bounds later
  const markers = L.featureGroup();

  <?php
  $stmt = $pdo->query("SELECT location_name, location_latitude, location_longitude FROM locations WHERE location_latitude IS NOT NULL AND location_longitude IS NOT NULL");
  while ($loc = $stmt->fetch()):
    $name = htmlspecialchars($loc['location_name'], ENT_QUOTES);
    $lat = (float)$loc['location_latitude'];
    $lon = (float)$loc['location_longitude'];
    $url = 'view_table.php?loc=' . urlencode($loc['location_name']);
  ?>
    L.marker([<?= $lat ?>, <?= $lon ?>])
  .bindPopup("<strong><?= $name ?></strong><br><a href='<?= $url ?>'>View Data</a>")
  .on('click', function () {
    window.location.href = "<?= $url ?>";
  })
  .addTo(markers);
  <?php endwhile; ?>

  // Add all markers and zoom to fit
  markers.addTo(map);
  if (markers.getLayers().length > 0) {
    map.fitBounds(markers.getBounds().pad(0.1));
  }
</script>
</body>
</html>
