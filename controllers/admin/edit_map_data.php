<?php
session_start();
require_once '../../models/functions.php';
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$uploadedPaths = [];
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
    startTransaction();
    $query = "SELECT admin_id, blur_services FROM places WHERE id = ? FOR UPDATE";
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
        'contact' => mysqli_real_escape_string($conn, $_POST['contact'] ?? ''),
        'blur_services' => isset($_POST['blur_services']) ? (int)$_POST['blur_services'] : (int)$location['blur_services']
    ];
    
    // Update the place
    require_once __DIR__ . '/../../models/place_about.php';
    $updateData += placeAboutWriteFields($_POST);
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
                if (!insertRecord('menu', $menuData)) {
                    throw new RuntimeException('Could not save a menu item.');
                }
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
                if (!insertRecord('website_url', $websiteData)) {
                    throw new RuntimeException('Could not save a website.');
                }
            }
        }
    }
    
    $removedHighlights = $_POST['removed_highlights'] ?? [];
    if ($removedHighlights) {
        $remove = $conn->prepare('DELETE FROM highlights WHERE id = ? AND place_id = ?');
        foreach ($removedHighlights as $highlightId) {
            $highlightId = (int)$highlightId;
            $remove->bind_param('ii', $highlightId, $locationId);
            $remove->execute();
        }
    }

    $uploads = $_FILES['highlights'] ?? [];
    $imageTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    foreach ($uploads['error'] ?? [] as $index => $error) {
        if ($error === UPLOAD_ERR_NO_FILE) continue;
        if ($error !== UPLOAD_ERR_OK || $uploads['size'][$index] > 5 * 1024 * 1024) {
            throw new RuntimeException('Could not upload a photo. The maximum size is 5 MB.');
        }
        $source = $uploads['tmp_name'][$index];
        $extension = $imageTypes[(new finfo(FILEINFO_MIME_TYPE))->file($source)] ?? null;
        if (!$extension) {
            throw new RuntimeException('Choose a JPG, PNG, GIF, or WebP photo.');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $path = __DIR__ . '/../../uploads/' . $filename;
        if (!move_uploaded_file($source, $path)) {
            throw new RuntimeException('Could not store the uploaded photo.');
        }
        $uploadedPaths[] = $path;
        if (!insertRecord('highlights', ['place_id' => $locationId, 'image_url' => $filename])) {
            throw new RuntimeException('Could not save the uploaded photo.');
        }
    }

    commitTransaction();
    
    echo json_encode([
        'success' => true,
        'message' => 'Location updated successfully!',
        'location_id' => $locationId
    ]);
    
} catch (Throwable $e) {
    rollbackTransaction();
    foreach ($uploadedPaths as $path) {
        unlink($path);
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
