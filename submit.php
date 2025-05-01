<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['location_id']) || empty($_POST['measurement_date'])) {
        die("Error: Missing required fields.");
    }

    $location_id = $_POST['location_id'];
    $measurement_date = $_POST['measurement_date'];

    $excluded_keys = [
        'location_id',
        'measurement_date',
        'location_latitude',
        'location_longitude',
        'location',
        'field_order'
    ];
    $dynamic_data = array_diff_key($_POST, array_flip($excluded_keys));

    $ordered_data = [];
    $order = json_decode($_POST['field_order'], true);

    foreach ($order as $key) {
        if (isset($dynamic_data[$key])) {
            $ordered_data[$key] = $dynamic_data[$key];
        }
    }

    $json_data = json_encode($ordered_data, JSON_UNESCAPED_UNICODE);

    try {
        $stmt = $pdo->prepare("INSERT INTO measurements (location_id, measurement_date, measurement_data) VALUES (?, ?, ?)");
        $stmt->execute([$location_id, $measurement_date, $json_data]);

        header("Location: index.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>
