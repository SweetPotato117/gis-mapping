<?php
session_start();
require_once '../../models/functions.php';
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $locationId = isset($_POST['id']) ? (int)$_POST['id'] : null;
    
    if (!$locationId) {
        throw new Exception('Location ID is required');
    }
    
    // Get current admin info
    $currentAdminId = (int)$_SESSION['admin_id'];
    $currentAdminRole = $_SESSION['admin_role'] ?? 'admin';
    
    // Get the location to check ownership
    global $conn;
    $query = "SELECT admin_id FROM places WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $locationId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $location = mysqli_fetch_assoc($result);
    
    if (!$location) {
        throw new Exception('Location not found');
    }
    
    $locationOwnerId = (int)$location['admin_id'];
    
    // Check if user has permission (owner or superadmin)
    $hasPermission = ($currentAdminRole === 'superadmin') || ($currentAdminId === $locationOwnerId);
    
    if (!$hasPermission) {
        throw new Exception('You do not have permission to delete this location');
    }
    
    // Start transaction
    startTransaction();
    
    // Get all highlight images to delete files
    $highlights = getPlaceHighlights($locationId);
    
    // Delete related records first (foreign key constraints)
    mysqli_query($conn, "DELETE FROM menu WHERE place_id = $locationId");
    mysqli_query($conn, "DELETE FROM website_url WHERE place_id = $locationId");
    mysqli_query($conn, "DELETE FROM highlights WHERE place_id = $locationId");
    
    // Delete the place itself
    $deleted = deleteRecord('places', "id = $locationId");
    
    if (!$deleted) {
        throw new Exception('Failed to delete location: ' . mysqli_error($conn));
    }
    
    // Delete image files from server
    if (!empty($highlights)) {
        $uploadDir = __DIR__ . '/../../uploads/';
        foreach ($highlights as $highlight) {
            if (!empty($highlight['image_url'])) {
                $filePath = $uploadDir . ltrim($highlight['image_url'], '/');
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }
    
    // Commit transaction
    commitTransaction();
    
    echo json_encode([
        'success' => true,
        'message' => 'Location deleted successfully!'
    ]);
    
} catch (Exception $e) {
    rollbackTransaction();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
