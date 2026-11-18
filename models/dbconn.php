<?php
//$conn = mysqli_connect('localhost', 'u552485550_miggy', 'Miggy1520!', 'u552485550_gis_mapping') or die('Connection Failed');
$conn = mysqli_connect('gateway01.ap-northeast-1.prod.aws.tidbcloud.com', 'dPiyt2AcV2HpbKP.root', '1Db2TDWDqSfTUY97', 'gis_mapping', 4000) or die('Connection Failed');
mysqli_set_charset($conn, "utf8mb4");
