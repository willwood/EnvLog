<?php
include 'config.php';
$showControls = (EDIT_CONTROLS || DELETE_CONTROLS);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>EnvLog | View Data</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="<?php echo $theme_color; ?>" />
    <link rel="stylesheet" href="styles.css">
</head>
<body>

  <div id="envlog_table_toolbar">
    <select id="locationFilter">
      <option value="all">All Locations</option>
      <?php
      // Get distinct locations
      $locations = $pdo->query("SELECT DISTINCT location_name FROM locations ORDER BY location_name ASC")->fetchAll();
      foreach ($locations as $loc) {
        $selected = (isset($_GET['loc']) && urldecode($_GET['loc']) === $loc['location_name']) ? "selected" : "";
        echo "<option value=\"" . htmlspecialchars($loc['location_name']) . "\" $selected>" . htmlspecialchars($loc['location_name']) . "</option>";
      }
      ?>
    </select>

    <div id="envlog_table_toolbar_right">
      <label class="json-toggle">
          <input type="checkbox" id="toggle_pretty_json"> Pretty JSON
      </label>

      <div id="envlog_ordering_buttons">
        <button id="sortAscBtn" type="button">↑</button>
        <button id="sortDescBtn" type="button">↓</button>
      </div>
    </div>

  </div>

  <div class="table_wrapper">




    <table>
      <thead>
        <tr>
            <th class="loccol">Location</th>
            <?php if (LAT_LON_COORDS): ?>
            <th class='latcol'>Latitude</th>
            <th class='loncol'>Longitude</th>
            <?php endif ?>
            <th>Date &amp; Time</th>
            <th>Data (JSON)</th>
            <?php if ($showControls): ?>
              <th>Actions</th>
            <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php
        $sortOrder = (isset($_GET['sort']) && strtolower($_GET['sort']) === 'desc') ? 'DESC' : 'ASC';

        $selectCols = "measurements.id, locations.location_name, measurements.measurement_date, measurements.measurement_data";
        if (defined('LAT_LON_COORDS') && LAT_LON_COORDS) {
          $selectCols .= ", locations.location_latitude, locations.location_longitude";
        }

        $stmt = $pdo->query("SELECT $selectCols
                            FROM measurements
                            JOIN locations ON measurements.location_id = locations.id
                            ORDER BY measurements.measurement_date $sortOrder");

        while ($row = $stmt->fetch()) {
          $location = htmlspecialchars($row['location_name']);
          $escaped_raw = htmlspecialchars($row['measurement_data'], ENT_QUOTES, 'UTF-8');
          $parsed_json = json_decode($row['measurement_data'], true);
          $pretty_json = $parsed_json ? json_encode($parsed_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Invalid JSON';

          echo "<tr data-location=\"$location\">
        <td class='loccol'>{$location}</td>";

if (LAT_LON_COORDS) {
    $lat = htmlspecialchars($row['location_latitude']);
    $lon = htmlspecialchars($row['location_longitude']);
    echo "<td class='latcol'>{$lat}</td>
          <td class='loncol'>{$lon}</td>";
}

echo "  <td>{$row['measurement_date']}</td>
        <td>
            <div class='json-data'>
                <div class='json-raw' style='display: block;'>{$escaped_raw}</div>
                <div class='json-pretty' style='display: none;'>" . nl2br(htmlspecialchars($pretty_json)) . "</div>
            </div>
        </td>";

if (EDIT_CONTROLS || DELETE_CONTROLS) {
    echo "<td>";

    if (EDIT_CONTROLS) {
        echo "<form action='index.php' method='get' style='display:inline;'>
                <input type='hidden' name='edit_id' value='{$row['id']}'>
                <button type='submit' title='Edit'>
                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pencil-fill' viewBox='0 0 16 16'>
                        <path d='M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z'/>
                    </svg>
                </button>
              </form>";
    }

    if (DELETE_CONTROLS) {
        echo "<form action='delete.php' method='post' style='display:inline;'>
                <input type='hidden' name='id' value='{$row['id']}'>
                <button type='submit' onclick=\"return confirm('Are you sure you want to delete this record?');\" title='Delete'>
                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash-fill' viewBox='0 0 16 16'>
                        <path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0'/>
                    </svg>
                </button>
              </form>";
    }

    echo "</td>";
}

echo "</tr>";

        }
        ?>
      </tbody>
    </table>
  </div>

  <script>
    // Row order
    document.getElementById('sortAscBtn').addEventListener('click', () => {
    updateSortOrder('asc');
});
document.getElementById('sortDescBtn').addEventListener('click', () => {
    updateSortOrder('desc');
});

function updateSortOrder(order) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', order);

    // Retain location filter if active
    const currentLoc = document.getElementById('locationFilter').value;
    if (currentLoc && currentLoc !== 'all') {
        url.searchParams.set('loc', currentLoc);
    } else {
        url.searchParams.delete('loc');
    }

    // Reload the page with new parameters
    window.location.href = url.toString();
}

// Toggle between raw and pretty JSON
document.getElementById('toggle_pretty_json').addEventListener('change', function () {
    const showRaw = this.checked;
    document.querySelectorAll('.json-raw').forEach(el => el.style.display = showRaw ? 'none' : 'block');
    document.querySelectorAll('.json-pretty').forEach(el => el.style.display = showRaw ? 'block' : 'none');
});

// Filter table by location
const filter = document.getElementById('locationFilter');
const rows = document.querySelectorAll('table tbody tr');
const locCols = document.querySelectorAll('.loccol');
const latCols = document.querySelectorAll('.latcol');
const lonCols = document.querySelectorAll('.loncol');

function applyLocationFilter(value) {
    const selected = value === 'all' ? null : value;

    rows.forEach(row => {
        const rowLoc = row.getAttribute('data-location');
        row.style.display = (!selected || rowLoc === selected) ? '' : 'none';
    });

    // Hide lat/lon when filtering to one location
    const hideCoords = selected !== null;
    locCols.forEach(col => col.style.display = hideCoords ? 'none' : '');
    latCols.forEach(col => col.style.display = hideCoords ? 'none' : '');
    lonCols.forEach(col => col.style.display = hideCoords ? 'none' : '');
}

filter.addEventListener('change', function () {
    applyLocationFilter(this.value);
    const newUrl = new URL(window.location.href);
    if (this.value === 'all') {
        newUrl.searchParams.delete('loc');
    } else {
        newUrl.searchParams.set('loc', this.value);
    }
    window.history.replaceState(null, '', newUrl);
});

// Auto-select based on query string
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const loc = urlParams.get('loc');
    if (loc) {
        applyLocationFilter(loc);
    }
});
</script>

<?php require_once 'navigation.php'; ?>
</body>
</html>