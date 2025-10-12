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
        throw new Exception('You do not have permission to edit this location');
    }
    
    // Start transaction
    startTransaction();
    
    // Validate required fields
    $requiredFields = ['name', 'type', 'description', 'lat', 'lng'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field $field is required");
        }
    }
    
    // Prepare update data
    $updateData = [
        'name' => mysqli_real_escape_string($conn, $_POST['name']),
        'type' => mysqli_real_escape_string($conn, $_POST['type']),
        'description' => mysqli_real_escape_string($conn, $_POST['description']),
        'address' => mysqli_real_escape_string($conn, $_POST['address'] ?? ''),
        'lat' => floatval($_POST['lat']),
        'lng' => floatval($_POST['lng']),
        'open_hours' => mysqli_real_escape_string($conn, $_POST['open_hours'] ?? ''),
        'contact' => mysqli_real_escape_string($conn, $_POST['contact'] ?? '')
    ];
    
    // Update the place
    $updated = updateRecord('places', $updateData, "id = $locationId");
    
    if (!$updated) {
        throw new Exception('Failed to update location: ' . mysqli_error($conn));
    }
    
    // Delete and re-insert menu items
    mysqli_query($conn, "DELETE FROM menu WHERE place_id = $locationId");
    
    if (!empty($_POST['menu_items']) && is_array($_POST['menu_items'])) {
        foreach ($_POST['menu_items'] as $menuItem) {
            if (!empty($menuItem['name']) && !empty($menuItem['price'])) {
                $menuData = [
                    'place_id' => $locationId,
                    'name' => mysqli_real_escape_string($conn, $menuItem['name']),
                    'type' => 'main',
                    'price' => floatval($menuItem['price'])
                ];
                insertRecord('menu', $menuData);
            }
        }
    }
    
    // Delete and re-insert websites
    mysqli_query($conn, "DELETE FROM website_url WHERE place_id = $locationId");
    
    if (!empty($_POST['hotel_websites']) && is_array($_POST['hotel_websites'])) {
        foreach ($_POST['hotel_websites'] as $websiteUrl) {
            $websiteUrl = trim($websiteUrl);
            if (!empty($websiteUrl)) {
                if (!filter_var($websiteUrl, FILTER_VALIDATE_URL)) {
                    throw new Exception("Invalid URL format: $websiteUrl");
                }
                $websiteData = [
                    'place_id' => $locationId,
                    'link' => mysqli_real_escape_string($conn, $websiteUrl)
                ];
                insertRecord('website_url', $websiteData);
            }
        }
    }
    
    // Commit transaction
    commitTransaction();
    
    echo json_encode([
        'success' => true,
        'message' => 'Location updated successfully!',
        'location_id' => $locationId
    ]);
    
} catch (Exception $e) {
    rollbackTransaction();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
