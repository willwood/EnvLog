<?php
include 'config.php';

header('Content-Type: application/json');

// Build SELECT clause conditionally
$selectFields = "locations.location_name, measurements.measurement_date, measurements.measurement_data";
$includeLatLon = defined('LAT_LON_COORDS') && LAT_LON_COORDS;

if ($includeLatLon) {
    $selectFields .= ", locations.location_latitude, locations.location_longitude";
}

$query = "SELECT $selectFields
          FROM measurements
          JOIN locations ON measurements.location_id = locations.id
          ORDER BY measurements.measurement_date ASC";

$stmt = $pdo->query($query);
$results = [];

while ($row = $stmt->fetch()) {
    $json_data = json_decode($row['measurement_data'], true) ?? [];

    // Construct array in desired key order
    if ($includeLatLon) {
        $entry = [
            'Location'  => $row['location_name'],
            'Latitude'  => $row['location_latitude'],
            'Longitude' => $row['location_longitude'],
            'Date'      => $row['measurement_date'],
            'Data'      => $json_data
        ];
    } else {
        $entry = [
            'Location' => $row['location_name'],
            'Date'     => $row['measurement_date'],
            'Data'     => $json_data
        ];
    }

    $results[] = $entry;
}

echo json_encode($results, JSON_UNESCAPED_UNICODE);
exit();
?>
