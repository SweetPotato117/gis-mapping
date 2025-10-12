<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');

// Start output buffering
ob_start();

// Start session
session_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

try {
    // Database connection
    $connect = mysqli_connect("localhost", "root", "", "gis_mapping");
    
    if (!$connect) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    // Check if user is logged in
    if (!isset($_SESSION["user_id"])) {
        throw new Exception('User not logged in');
    }
    
    // Get data from POST request
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception('Invalid request method');
    }
    
    $user_id = (int)$_SESSION["user_id"];
    
    if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
        throw new Exception('Missing latitude or longitude');
    }
    
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    
    // Validate coordinates
    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        throw new Exception('Invalid coordinates format');
    }
    
    // Sanitize inputs
    $latitude = mysqli_real_escape_string($connect, $latitude);
    $longitude = mysqli_real_escape_string($connect, $longitude);
    
    // Check if user already exists in user table
    $check_query = "SELECT userid FROM user WHERE userid = '$user_id'";
    $check_result = mysqli_query($connect, $check_query);
    
    if (!$check_result) {
        throw new Exception('Database query failed: ' . mysqli_error($connect));
    }
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing record
        $update_query = "UPDATE user SET lat = '$latitude', lng = '$longitude', lastupdated = CURRENT_TIMESTAMP WHERE userid = '$user_id'";
        
        if (!mysqli_query($connect, $update_query)) {
            throw new Exception('Failed to update location: ' . mysqli_error($connect));
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Location updated',
            'user_id' => $user_id,
            'lat' => (float)$latitude,
            'lng' => (float)$longitude
        ], JSON_UNESCAPED_SLASHES);
        
    } else {
        // Insert new record
        $insert_query = "INSERT INTO user (userid, lat, lng) VALUES ('$user_id', '$latitude', '$longitude')";
        
        if (!mysqli_query($connect, $insert_query)) {
            throw new Exception('Failed to save location: ' . mysqli_error($connect));
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Location saved',
            'user_id' => $user_id,
            'lat' => (float)$latitude,
            'lng' => (float)$longitude
        ], JSON_UNESCAPED_SLASHES);
    }
    
    mysqli_close($connect);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_SLASHES);
}

ob_end_flush();
exit;
