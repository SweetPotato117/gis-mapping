<?php
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

if (!mysqli_real_connect($conn, '://tidbcloud.com', 'dPiyt2AcV2HpbKP.root', '1Db2TDWDqSfTUY97', 'gis_mapping', 4000, NULL, MYSQLI_CLIENT_SSL)) {
    die('Connection Failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
