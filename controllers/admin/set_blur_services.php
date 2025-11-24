<?php
session_start();
require_once '../../models/functions.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    if (!isset($_POST['id'])) {
        throw new Exception('Missing id');
    }

    $id = (int)$_POST['id'];
    $blur = isset($_POST['blur_services']) ? (int)$_POST['blur_services'] : 0;

    global $conn;
    if (!$conn) throw new Exception('DB connection failed');

    // Update the single field
    $updated = updateRecord('places', ['blur_services' => $blur], "id = $id");

    if ($updated === false) {
        error_log("[set_blur_services] updateRecord failed: " . mysqli_error($conn));
        throw new Exception('Failed to update blur_services: ' . mysqli_error($conn));
    }

    // Check affected rows to see if change happened
    $affected = mysqli_affected_rows($conn);
    error_log("[set_blur_services] Updated place id=$id set blur_services=$blur; affected_rows=$affected");

    echo json_encode(['success' => true, 'blur_services' => $blur, 'affected_rows' => $affected]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

