<?php
//$conn = mysqli_connect('localhost', 'u552485550_miggy', 'Miggy1520!', 'u552485550_gis_mapping') or die('Connection Failed');
$conn = mysqli_connect('localhost', 'root', '', 'gis_mapping') or die('Connection Failed');
mysqli_set_charset($conn, "utf8mb4");