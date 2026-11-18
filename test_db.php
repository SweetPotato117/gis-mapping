<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'dbconn.php';

echo "<h3>Connected successfully to TiDB!</h3>";

$result = mysqli_query($conn, "SHOW TABLES");
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

echo "Tables found in gis_mapping:<br>";
while ($row = mysqli_fetch_row($result)) {
    echo "- " . $row[0] . "<br>";
}
?>
