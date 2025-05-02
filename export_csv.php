<?php
include 'config.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=export.csv');

$output = fopen('php://output', 'w');

$includeLatLon = defined('LAT_LON_COORDS') && LAT_LON_COORDS;

// Build SELECT clause
$selectFields = "locations.location_name, measurements.measurement_date, measurements.measurement_data";
if ($includeLatLon) {
    $selectFields .= ", locations.location_latitude, locations.location_longitude";
}

$query = "SELECT $selectFields
          FROM measurements
          JOIN locations ON measurements.location_id = locations.id
          ORDER BY measurements.measurement_date ASC";

$stmt = $pdo->query($query);

if (CSV_EXPAND_JSON) {
    // === Expanded columns ===

    $dynamic_keys = [];
    $data_rows = [];

    // Collect dynamic keys and rows
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $json_data = json_decode($row['measurement_data'], true) ?? [];
        foreach ($json_data as $key => $value) {
            $dynamic_keys[$key] = true;
        }

        $data_rows[] = [
            'location_name'      => $row['location_name'],
            'location_latitude'  => $row['location_latitude'] ?? null,
            'location_longitude' => $row['location_longitude'] ?? null,
            'measurement_date'   => $row['measurement_date'],
            'measurement_data'   => $json_data
        ];
    }

    $dynamic_keys = array_keys($dynamic_keys);

    // Build header
    $header = ['Location'];
    if ($includeLatLon) {
        $header[] = 'Latitude';
        $header[] = 'Longitude';
    }
    $header[] = 'Date';
    $header = array_merge($header, $dynamic_keys);
    fputcsv($output, $header);

    // Write rows
    foreach ($data_rows as $row) {
        $line = [$row['location_name']];

        if ($includeLatLon) {
            $line[] = $row['location_latitude'];
            $line[] = $row['location_longitude'];
        }

        $line[] = $row['measurement_date'];

        foreach ($dynamic_keys as $key) {
            $line[] = $row['measurement_data'][$key] ?? '';
        }

        fputcsv($output, $line);
    }

} else {
    // === Single column for JSON data ===

    $header = ['Location'];
    if ($includeLatLon) {
        $header[] = 'Latitude';
        $header[] = 'Longitude';
    }
    $header[] = 'Date';
    $header[] = 'Data (JSON)';
    fputcsv($output, $header);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $json_data = json_decode($row['measurement_data'], true);
        $encoded_data = json_encode($json_data, JSON_UNESCAPED_UNICODE);

        $line = [$row['location_name']];

        if ($includeLatLon) {
            $line[] = $row['location_latitude'];
            $line[] = $row['location_longitude'];
        }

        $line[] = $row['measurement_date'];
        $line[] = $encoded_data;

        fputcsv($output, $line);
    }
}

fclose($output);
exit();
?>
