<?php
include 'config.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=export.csv');

$output = fopen('php://output', 'w');

// Updated query to include location_latitude and location_longitude
$query = "SELECT locations.location_name, locations.location_latitude, locations.location_longitude,
                 measurements.measurement_date, measurements.measurement_data
          FROM measurements
          JOIN locations ON measurements.location_id = locations.id
          ORDER BY measurements.measurement_date ASC";

$stmt = $pdo->query($query);

if (CSV_EXPAND_JSON) {
    // === Expanded columns ===

    // First pass: find all unique keys
    $dynamic_keys = [];
    $data_rows = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $json_data = json_decode($row['measurement_data'], true) ?? [];
        foreach ($json_data as $key => $value) {
            $dynamic_keys[$key] = true;
        }

        $data_rows[] = [
            'location_name' => $row['location_name'],
            'location_latitude' => $row['location_latitude'],
            'location_longitude' => $row['location_longitude'],
            'measurement_date' => $row['measurement_date'],
            'measurement_data' => $json_data
        ];
    }

    // Header row with latitude and longitude columns added
    $dynamic_keys = array_keys($dynamic_keys);
    $header = array_merge(['Location', 'Latitude', 'Longitude', 'Date'], $dynamic_keys);
    fputcsv($output, $header);

    // Write rows
    foreach ($data_rows as $row) {
        $line = [
            $row['location_name'],
            $row['location_latitude'],
            $row['location_longitude'],
            $row['measurement_date']
        ];

        foreach ($dynamic_keys as $key) {
            $line[] = $row['measurement_data'][$key] ?? '';
        }

        fputcsv($output, $line);
    }

} else {
    // === Single column for JSON data ===

    fputcsv($output, ['Location', 'Latitude', 'Longitude', 'Date', 'Data (JSON)']);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $json_data = json_decode($row['measurement_data'], true);
        $row['measurement_data'] = json_encode($json_data, JSON_UNESCAPED_UNICODE);

        fputcsv($output, [
            $row['location_name'],
            $row['location_latitude'],
            $row['location_longitude'],
            $row['measurement_date'],
            $row['measurement_data']
        ]);
    }
}

fclose($output);
exit();
?>
