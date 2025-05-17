<?php
include 'config.php';

// Setup
$edit_mode = false;
$location_id = '';
$measurement_date = date('Y-m-d\TH:i');
$existing_data = [];
$edit_id = null;

// Check if editing
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    $edit_mode = true;

    $stmt = $pdo->prepare("SELECT location_id, measurement_date, measurement_data FROM measurements WHERE id = ?");
    $stmt->execute([$edit_id]);
    $record = $stmt->fetch();

    if ($record) {
        $location_id = $record['location_id'];
        $measurement_date = date('Y-m-d\TH:i', strtotime($record['measurement_date']));
        $existing_data = json_decode($record['measurement_data'], true);
    } else {
        die("Record not found.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EnvLog - <?php echo $edit_mode ? 'Edit Record' : 'Record Data'; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="<?php echo $theme_color; ?>" />
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<main>
  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="envlog_alert envlog_success">
      <button class="envlog_alert_close_btn" aria-label="Close">&times;</button>
      <p><?php echo $edit_mode ? 'Record successfully updated!' : 'Data successfully recorded!'; ?></p>
    </div>
  <?php endif; ?>

  <div id="reader_wrapper" data-envlog-location-control>
    <div id="reader"></div>
    <div id="result"></div>
  </div>

  <form method="POST" action="submit.php">
    <div class="envlog_input_item">
      <select name="location" id="locationSelect" data-envlog-location-control>
        <option value="" selected>Choose location...</option>
        <?php
          $stmt = $pdo->query("SELECT id, location_name FROM locations");
          while ($row = $stmt->fetch()) {
              $selected = ($location_id == $row['id']) ? "selected" : "";
              echo "<option value='{$row['id']}' $selected>{$row['location_name']}</option>";
          }
        ?>
        <?php if (NEW_LOCATIONS): ?>
        <option value="new">&rightarrow; Add location</option>
        <?php endif ?>
      </select>
    </div>

    <input type="hidden" name="location_id" id="location_id" value="<?= htmlspecialchars($location_id) ?>">
    <input type="hidden" name="field_order" id="field_order" value="">

    <?php if ($edit_mode): ?>
      <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_id) ?>">
    <?php endif; ?>

    <div id="envlog_data_form" class="envlog_well">
      <p><?php echo $edit_mode ? 'Editing existing data for' : 'Entering new data for'; ?> <strong id="place_name"></strong></p>

      <div class="envlog_input_item">
        <label for="measurement_date">Date and Time</label>
        <input type="datetime-local" name="measurement_date" value="<?php echo htmlspecialchars($measurement_date); ?>">
      </div>

      <div class="envlog_input_item">
        <label for="measurement_value">Measurement</label>
        <div class="envlog_input_group">
          <input type="number" name="measurement_value" id="measurement_value" step="0.01"
                 value="<?php echo htmlspecialchars($existing_data['measurement_value'] ?? ''); ?>">
          <span class="envlog_input_text">cm</span>
        </div>
      </div>

      <div class="envlog_input_item">
        <label for="soil_moisture">Soil Moisture</label>
        <div class="envlog_input_group">
          <input type="number" name="soil_moisture" id="soil_moisture" step="0.01"
                 value="<?php echo htmlspecialchars($existing_data['soil_moisture'] ?? ''); ?>">
          <span class="envlog_input_text">%</span>
        </div>
      </div>

      <div class="envlog_input_item">
        <label for="soil_temperature">Soil Temperature</label>
        <div class="envlog_input_group">
          <input type="number" name="soil_temperature" id="soil_temperature" step="0.01"
                 value="<?php echo htmlspecialchars($existing_data['soil_temperature'] ?? ''); ?>">
          <span class="envlog_input_text">ºC</span>
        </div>
      </div>

      <div class="envlog_input_item">
        <button type="submit">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
          </svg>
          <span><?php echo $edit_mode ? 'Save Changes' : 'Add Record'; ?></span>
        </button>
      </div>
    </div>
  </form>
</main>

<?php require_once 'navigation.php'; ?>

<audio id="success-sound" src="sound_fx/success.mp3" preload="auto"></audio>
<audio id="error-sound" src="sound_fx/error.mp3" preload="auto"></audio>

<script src="./node_modules/html5-qrcode/html5-qrcode.min.js"></script>
<script>
  const NEW_LOCATIONS = <?php echo json_encode(NEW_LOCATIONS); ?>;
</script>
<script src="scripts.js"></script>

</body>
</html>
