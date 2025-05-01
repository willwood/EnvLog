<?php
include 'config.php';

header('Content-Type: application/json');

$query = "SELECT locations.location_name, locations.location_latitude, locations.location_longitude,
                 measurements.measurement_date, measurements.measurement_data
          FROM measurements
          JOIN locations ON measurements.location_id = locations.id
          ORDER BY measurements.measurement_date ASC";

$stmt = $pdo->query($query);
$results = [];

while ($row = $stmt->fetch()) {
    $json_data = json_decode($row['measurement_data'], true) ?? [];

    $results[] = [
        'Location'  => $row['location_name'],
        'Latitude'  => $row['location_latitude'],
        'Longitude' => $row['location_longitude'],
        'Date'      => $row['measurement_date'],
        'Data'      => $json_data
    ];
}

$options = JSON_UNESCAPED_UNICODE;

echo json_encode($results, $options);
exit();
?>
