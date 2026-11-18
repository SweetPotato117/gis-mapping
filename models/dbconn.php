<?php
$host = getenv('TIDB_HOST') ?: '://tidbcloud.com';
$port = 4000;
$user = getenv('TIDB_USER') ?: 'dPiyt2AcV2HpbKP.root';
$pass = getenv('TIDB_PASSWORD'); '1Db2TDWDqSfTUY97'
$db   = getenv('TIDB_DATABASE') ?: 'gis_mapping';

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die('Connection Failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
