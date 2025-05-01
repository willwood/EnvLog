<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM measurements WHERE id = ?");
    $stmt->execute([$id]);
    $measurement = $stmt->fetch();

    if (!$measurement) {
        echo "Measurement not found.";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $id = (int)$_POST['id'];
  $date = $_POST['measurement_date'];
  $data = $_POST['measurement_data'];

  // Parse and re-encode JSON to ensure it's valid and compact
  $parsed = json_decode($data, true);
  if ($parsed === null && json_last_error() !== JSON_ERROR_NONE) {
      echo "Invalid JSON data.";
      exit;
  }

  $compact_json = json_encode($parsed, JSON_UNESCAPED_UNICODE);

  $stmt = $pdo->prepare("UPDATE measurements SET measurement_date = ?, measurement_data = ? WHERE id = ?");
  $stmt->execute([$date, $compact_json, $id]);

  header('Location: view_table.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>EnvLog | Edit Measurement</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="<?php echo $theme_color; ?>" />
    <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main>
  <div id="jsonAlertContainer"></div>
<form method="post" class="envlog_well">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($measurement['id']); ?>">
    <div class="envlog_input_item">
      <label>Date:</label>
      <input type="datetime-local" name="measurement_date" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($measurement['measurement_date']))); ?>">
    </div>
    <div class="envlog_input_item">
      <label>Data (JSON):</label>
      <?php
      $raw_json = $measurement['measurement_data'];
      $parsed = json_decode($raw_json, true);
      $pretty_json = $parsed !== null ? json_encode($parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $raw_json;
      ?>
      <textarea name="measurement_data" rows="10" cols="50"><?php echo htmlspecialchars($pretty_json); ?></textarea>
    </div>
    <div class="envlog_input_item">
      <button type="submit">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy2-fill" viewBox="0 0 16 16">
          <path d="M12 2h-2v3h2z"/>
          <path d="M1.5 0A1.5 1.5 0 0 0 0 1.5v13A1.5 1.5 0 0 0 1.5 16h13a1.5 1.5 0 0 0 1.5-1.5V2.914a1.5 1.5 0 0 0-.44-1.06L14.147.439A1.5 1.5 0 0 0 13.086 0zM4 6a1 1 0 0 1-1-1V1h10v4a1 1 0 0 1-1 1zM3 9h10a1 1 0 0 1 1 1v5H2v-5a1 1 0 0 1 1-1"/>
        </svg>
        <span>Save Changes</span>
      </button>
    </div>
</form>
</main>

<?php require_once 'navigation.php'; ?>

<script>
document.querySelector('form').addEventListener('submit', function (e) {
    const textarea = document.querySelector('textarea[name="measurement_data"]');
    const json = textarea.value.trim();
    const alertContainer = document.getElementById('jsonAlertContainer');
    alertContainer.innerHTML = ''; // Clear previous messages

    try {
        const parsed = JSON.parse(json);
        textarea.value = JSON.stringify(parsed); // Minify JSON
    } catch (err) {
        e.preventDefault();

        // Build alert box
        const alertBox = document.createElement('div');
        alertBox.className = 'envlog_alert envlog_error';
        alertBox.innerHTML = `
            <button class="envlog_alert_close_btn" aria-label="Close">&times;</button>
            <p>Invalid JSON. Please correct the formatting and try again.</p>
        `;
        alertContainer.appendChild(alertBox);
    }
});

// Allow alerts to be dismissed
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('envlog_alert_close_btn')) {
        e.target.parentElement.remove();
    }
});
</script>

</body>
</html>
