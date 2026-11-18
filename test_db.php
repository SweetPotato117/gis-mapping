<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "1. PHP is running...<br>";

require_once __DIR__ . '/models/place_about.php';

echo "2. Connected successfully to TiDB!<br>";

$result = mysqli_query($conn, "SHOW TABLES");
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

echo "3. Tables found in gis_mapping:<br>";
while ($row = mysqli_fetch_row($result)) {
    echo "- " . $row[0] . "<br>";
}
?>
