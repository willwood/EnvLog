<?php

// Rename this file to config.php and fill in your actual database credentials below

$database_hostname = 'localhost';
$database_name = 'your_database_name';
$database_username = 'your_username';
$database_password = 'your_password';

define('CSV_EXPAND_JSON', true);  // true = expand JSON into CSV columns
define('DELETE_CONTROLS', true);  // Enable or disable deleting of existing content
define('EDIT_CONTROLS', true);    // Enable or disable editing of existing content
define('LAT_LON_COORDS', true);   // toggle latitude and longitude controls / mapping on and off
define('NEW_LOCATIONS', true);    // Toggles the ability for the user to add new locations on and off
$theme_color = '#198754';         // Webapp theme colour

try {
  $pdo = new PDO("mysql:host=$database_hostname;dbname=$database_name", $database_username, $database_password, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Create table if not exists
$query = "CREATE TABLE IF NOT EXISTS locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  location_name VARCHAR(255) UNIQUE NOT NULL,
  location_latitude DECIMAL(9, 6) DEFAULT NULL,
  location_longitude DECIMAL(9, 6) DEFAULT NULL
);
CREATE TABLE IF NOT EXISTS measurements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  location_id INT NOT NULL,
  measurement_date DATETIME NOT NULL,
  measurement_data JSON NOT NULL,
  FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
);";
$pdo->exec($query);

?>