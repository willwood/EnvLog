<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EnvLog - Add New Location</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="<?php echo $theme_color; ?>" />
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<main>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $location_name = trim($_POST['location_name']);
    $location_latitude = isset($_POST['location_latitude']) && $_POST['location_latitude'] !== ''
      ? $_POST['location_latitude']
      : null;
    $location_longitude = isset($_POST['location_longitude']) && $_POST['location_longitude'] !== ''
      ? $_POST['location_longitude']
      : null;

    // Validate latitude and longitude if they are not null
    if (!is_null($location_latitude) && !filter_var($location_latitude, FILTER_VALIDATE_FLOAT)) {
      die("Error: Invalid latitude value.");
    }
    if (!is_null($location_longitude) && !filter_var($location_longitude, FILTER_VALIDATE_FLOAT)) {
      die("Error: Invalid longitude value.");
    }

    try {
        // Check if the location already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM locations WHERE location_name = ?");
        $checkStmt->execute([$location_name]);
        $locationExists = $checkStmt->fetchColumn() > 0;

        if ($locationExists) {
            // Location exists, show error message
            $error_message = "Error: A location with that name already exists.";
        } else {
            // Insert new location into the database
            $stmt = $pdo->prepare("INSERT INTO locations (location_name, location_latitude, location_longitude)
                                   VALUES (?, ?, ?)");
            $stmt->execute([$location_name, $location_latitude, $location_longitude]);
            // Set success message
            $success_message = "New location added. <a href='index.php'>Click here</a> to choose the location and add data.";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
  }
  ?>

  <!-- Show success or error message if available -->
  <?php if (isset($success_message)): ?>
    <div class="envlog_alert envlog_success">
      <button class="envlog_alert_close_btn" aria-label="Close">&times;</button>
      <p><?php echo $success_message; ?></p>
    </div>
  <?php elseif (isset($error_message)): ?>
    <div class="envlog_alert envlog_error">
      <button class="envlog_alert_close_btn" aria-label="Close">&times;</button>
      <p><?php echo $error_message; ?></p>
    </div>
  <?php endif; ?>

  <!-- Location form -->
  <form method="POST" action="new_location.php" class="envlog_well">
  <div class="envlog_input_item">
    <label for="location_name">New location name</label>
    <input type="text" name="location_name" id="location_name" placeholder="Location Name" required>
  </div>
  <?php if (LAT_LON_COORDS): ?>
  <div class="envlog_well">
    <div class="envlog_input_item">
      <label for="location_latitude">Latitude</label>
      <input type="text" name="location_latitude" id="location_latitude">
    </div>
    <div class="envlog_input_item">
      <label for="location_longitude">Longitude</label>
      <input type="text" name="location_longitude" id="location_longitude">
    </div>
    <div class="envlog_input_item">
      <button type="button" id="lat_lon_button">Acquire Coordinates</button>
    </div>
  </div>
  <?php endif; ?>
  <div class="envlog_input_item">
    <button type="submit">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
        <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/>
        <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
      </svg>
      <span>Add Location</span>
    </button>
  </div>
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const latLonBtn = document.querySelector('#lat_lon_button');
      const newLatitude = document.querySelector('#location_latitude');
      const newLongitude = document.querySelector('#location_longitude');

      latLonBtn.addEventListener('click', () => {
        navigator.geolocation.getCurrentPosition(position => {
          const { latitude, longitude } = position.coords;
          if (!newLatitude.value) {
            newLatitude.value = latitude;
          }
          if (!newLongitude.value) {
            newLongitude.value = longitude;
          }
        });
      });

      const urlParams = new URLSearchParams(window.location.search);
      const newLoc = urlParams.get('new_loc');

      if (newLoc !== null) {
        const locationInput = document.getElementById('location_name');
        if (locationInput) {
          locationInput.value = newLoc;
        }

        // Remove 'new_loc' from the URL without reloading
        urlParams.delete('new_loc');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
      }
    });

    document.addEventListener('click', function (e) {
    if (e.target.classList.contains('envlog_alert_close_btn')) {
        e.target.parentElement.remove();
    }
    });
  </script>

</main>

<?php require_once 'navigation.php'; ?>

</body>
</html>
