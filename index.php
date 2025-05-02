<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EnvLog - Record Data</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="<?php echo $theme_color; ?>" />
  <link rel="stylesheet" href="styles.css">
  <script src="./node_modules/html5-qrcode/html5-qrcode.min.js"></script>
  <script>
    function handleLocationChange(selectElement) {
      const selectedValue = selectElement.value;
      const selectedText = selectElement.options[selectElement.selectedIndex].text;

      if (selectedValue === 'new') {
        // Redirect if 'Add New Location' is selected
        window.location.href = 'new_location.php';
        return;
      }

      if (selectedValue) {
        document.getElementById('envlog_data_form').style.display = 'flex';
        document.getElementById('location_id').value = selectedValue;
        document.getElementById('place_name').textContent = selectedText;
        document.querySelectorAll('[data-envlog-location-control]').forEach(element =>
          element.style.display = 'none'
        );
      }
    }
  </script>
</head>
<body>

<main>
  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="envlog_alert envlog_success">
      <button class="envlog_alert_close_btn" aria-label="Close">&times;</button>
      <p>Data successfully recorded!</p>
    </div>
  <?php endif; ?>

  <div id="reader_wrapper" data-envlog-location-control>
    <div id="reader"></div>
    <div id="result"></div>
  </div>

  <form method="POST" action="submit.php">
    <div class="envlog_input_item">
      <select name="location" onchange="handleLocationChange(this)" data-envlog-location-control>
        <option value="" selected>Choose location...</option>
        <?php
          $stmt = $pdo->query("SELECT id, location_name FROM locations");
          while ($row = $stmt->fetch()) {
            echo "<option value='{$row['id']}'>{$row['location_name']}</option>";
          }
        ?>
        <?php if (NEW_LOCATIONS): ?>
        <option value="new">&rightarrow; Add location</option>
        <?php endif ?>
      </select>
    </div>

    <input type="hidden" name="location_id" id="location_id">
    <input type="hidden" name="field_order" id="field_order" value="">

    <div id="envlog_data_form" class="envlog_well" style="display: none;">
      <p>Entering data for <strong id="place_name"></strong></p>

      <div class="envlog_input_item">
        <label for="measurement_date">Date and Time</label>
        <input type="datetime-local" name="measurement_date" value="<?php echo date('Y-m-d\TH:i'); ?>">
      </div>

      <div class="envlog_input_item">
        <label for="measurement_value">Measurement</label>
        <div class="envlog_input_group">
          <input type="number" name="measurement_value" id="measurement_value" step="0.01">
          <span class="envlog_input_text">cm</span>
        </div>
      </div>

      <div class="envlog_input_item">
        <label for="soil_moisture">Soil Moisture</label>
        <div class="envlog_input_group">
          <input type="number" name="soil_moisture" id="soil_moisture" step="0.01">
          <span class="envlog_input_text">%</span>
        </div>
      </div>

      <div class="envlog_input_item">
        <label for="soil_temperature">Soil Temperature</label>
        <div class="envlog_input_group">
          <input type="number" name="soil_temperature" id="soil_temperature" step="0.01">
          <span class="envlog_input_text">ºC</span>
        </div>
      </div>

      <div class="envlog_input_item">
        <button type="submit">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
          </svg>
          <span>Add Record</span>
        </button>
      </div>
    </div>
  </form>

</main>

<?php require_once 'navigation.php'; ?>

<audio id="success-sound" src="sound_fx/success.mp3" preload="auto"></audio>
<audio id="error-sound" src="sound_fx/error.mp3" preload="auto"></audio>

<script>
  document.querySelector('form').addEventListener('submit', function (e) {
    const inputs = [...this.elements].filter(el =>
      el.name && el.type !== 'hidden'
    );
    const order = inputs.map(input => input.name);
    document.getElementById('field_order').value = JSON.stringify(order);
  });

  const successSound = document.getElementById('success-sound');
  const errorSound = document.getElementById('error-sound');
  const scanner = new Html5QrcodeScanner('reader', {
    qrbox: { width: 200, height: 200 },
    fps: 20,
    videoConstraints: {
      facingMode: { exact: "environment" }
    }
  });

  scanner.render(onScanSuccess, onScanError);

  function onScanSuccess(result) {
    const selectMenu = document.querySelector('select[name="location"]');
    const resultText = document.querySelector('#result');
    const options = Array.from(selectMenu.options);
    const matchedOption = options.find(option => option.text === result);

    if (matchedOption) {
      successSound.play();
      selectMenu.value = matchedOption.value;
      handleLocationChange(selectMenu);
    } else {
      errorSound.play();
      resultText.innerHTML = `<div class="envlog_alert envlog_error">
        <button class="envlog_alert_close_btn" aria-label="Close">&times;</button>
        <p><strong>${result}</strong> is not a location in the database.
        <?php if (NEW_LOCATIONS): ?>
        <a href="new_location.php?new_loc=${encodeURIComponent(result)}">Click here</a> to setup this new location.
        <?php else: ?>
        <a href="index.php">Click here</a> to scan again or select an existing location from the menu below.
        <?php endif; ?>
        </p>
      </div>`;
    }
    scanner.clear();
  }

  function onScanError(err) {
    errorSound.play();
    document.getElementById('result').innerHTML = `<div class="envlog_alert envlog_error">
        <button class="envlog_alert_close_btn" aria-label="Close">&times;</button>
        <p><strong>An error occurred:</strong> ${err}</p>
        </div>`;
    console.error(err);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.envlog_alert .envlog_alert_close_btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const alert = this.closest('.envlog_alert');
        if (alert) {
          alert.style.display = 'none';
        }
      });
    });
  });

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('envlog_alert_close_btn')) {
        e.target.parentElement.remove();
    }
});
</script>

</body>
</html>
