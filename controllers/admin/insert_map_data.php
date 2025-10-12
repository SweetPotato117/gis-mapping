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
    // Start transaction
    startTransaction();
    
    // Validate required fields
    $required_fields = ['name', 'type', 'description', 'lat', 'lng'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Prepare place data with proper escaping
    global $conn;
    $placeData = [
        'name' => mysqli_real_escape_string($conn, $_POST['name']),
        'type' => mysqli_real_escape_string($conn, $_POST['type']),
        'description' => mysqli_real_escape_string($conn, $_POST['description']),
        'address' => mysqli_real_escape_string($conn, $_POST['address'] ?? ''),
        'lat' => floatval($_POST['lat']),
        'lng' => floatval($_POST['lng']),
        'open_hours' => mysqli_real_escape_string($conn, $_POST['open_hours'] ?? ''),
        'contact' => mysqli_real_escape_string($conn, $_POST['contact'] ?? ''),
        'admin_id' => intval($_SESSION['admin_id'])  // ADD THIS LINE
    ];
    
    // Insert place into database
    $placeInserted = insertRecord('places', $placeData);
    if (!$placeInserted) {
        throw new Exception('Failed to insert place data: ' . mysqli_error($conn));
    }
    
    // Get the inserted place ID
    $place_id = mysqli_insert_id($conn);
    
    // Insert menu items if provided (for restaurants only)
    if (!empty($_POST['menu_items']) && is_array($_POST['menu_items'])) {
        foreach ($_POST['menu_items'] as $menu_item) {
            if (!empty($menu_item['name']) && !empty($menu_item['price'])) {
                $menuData = [
                    'place_id' => $place_id,
                    'name' => mysqli_real_escape_string($conn, $menu_item['name']),
                    'type' => 'main',
                    'price' => floatval($menu_item['price'])
                ];
                insertRecord('menu', $menuData);
            }
        }
    }
    
    // Insert website URLs into website_url table
    if (!empty($_POST['hotel_websites']) && is_array($_POST['hotel_websites'])) {
        foreach ($_POST['hotel_websites'] as $website_url) {
            $website_url = trim($website_url);
            if (!empty($website_url)) {
                // Validate URL format
                if (!filter_var($website_url, FILTER_VALIDATE_URL)) {
                    throw new Exception('Invalid URL format: ' . $website_url);
                }
                
                $websiteData = [
                    'place_id' => $place_id,
                    'link' => mysqli_real_escape_string($conn, $website_url)
                ];
                insertRecord('website_url', $websiteData);
            }
        }
    }
    
    // Handle highlight image uploads (OPTIONAL)
    if (isset($_FILES['highlights']) && is_array($_FILES['highlights']['name']) && !empty($_FILES['highlights']['name'][0])) {
        // Use absolute path
        $upload_dir = __DIR__ . '/../../uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Validate and process each uploaded file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        foreach ($_FILES['highlights']['tmp_name'] as $key => $tmp_name) {
            // Skip if no file uploaded at this index
            if (empty($tmp_name) || $_FILES['highlights']['error'][$key] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            
            // Check for upload errors
            if ($_FILES['highlights']['error'][$key] !== UPLOAD_ERR_OK) {
                throw new Exception('Upload error for file: ' . $_FILES['highlights']['name'][$key]);
            }
            
            // Validate file size
            if ($_FILES['highlights']['size'][$key] > $max_file_size) {
                throw new Exception('File size exceeds 5MB limit: ' . $_FILES['highlights']['name'][$key]);
            }
            
            // Validate file extension
            $file_extension = strtolower(pathinfo($_FILES['highlights']['name'][$key], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Invalid file type. Only images are allowed: ' . $_FILES['highlights']['name'][$key]);
            }
            
            // Validate mime type for security
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mime_type, $allowed_mimes)) {
                throw new Exception('Invalid file type detected: ' . $_FILES['highlights']['name'][$key]);
            }
            
            // Generate unique filename
            $unique_filename = uniqid('img_', true) . '.' . $file_extension;
            $upload_path = $upload_dir . $unique_filename;
            
            // Move uploaded file
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $highlightData = [
                    'place_id' => $place_id,
                    'image_url' => mysqli_real_escape_string($conn, $unique_filename)
                ];
                $inserted = insertRecord('highlights', $highlightData);
                
                // Clean up file if database insert fails
                if (!$inserted) {
                    unlink($upload_path);
                    throw new Exception('Failed to save image record to database');
                }
            } else {
                throw new Exception('Failed to upload file: ' . $_FILES['highlights']['name'][$key]);
            }
        }
    }
    
    // Commit transaction
    commitTransaction();
    
    echo json_encode([
        'success' => true,
        'message' => 'Location added successfully!',
        'place_id' => $place_id
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    rollbackTransaction();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>
