<?php
include '../../models/functions.php';
header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 30;
$offset = ($page - 1) * $limit;

// Build query with search
$where = "WHERE role = 'admin'";
if (!empty($search)) {
    $where .= " AND username LIKE '%" . mysqli_real_escape_string($conn, $search ) . "%'";
}

$admins = getAllRecords('admin', $where . " LIMIT $limit OFFSET $offset");
$total = countAllRecords('admin');

$results = [];
foreach ($admins as $admin) {
    $results[] = [
        'id' => $admin['id'],
        'text' => $admin['username']
    ];
}

echo json_encode([
    'items' => $results,
    'total_count' => $total
]);
?>