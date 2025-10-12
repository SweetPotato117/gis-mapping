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
    // Check if file exists
    if (!file_exists('../../models/functions.php')) {
        throw new Exception('functions.php not found at: ' . realpath('../../models/'));
    }
    
    require_once '../../models/functions.php';
    
    // Check database connection
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Test if functions exist
    if (!function_exists('getAllPlaces')) {
        throw new Exception('getAllPlaces function does not exist');
    }
    
    if (!function_exists('getPlaceMenu')) {
        throw new Exception('getPlaceMenu function does not exist');
    }
    
    if (!function_exists('getPlaceHighlights')) {
        throw new Exception('getPlaceHighlights function does not exist');
    }
    
    if (!function_exists('getPlaceWebsites')) {
        throw new Exception('getPlaceWebsites function does not exist');
    }
    
    // Get all places
    $places = getAllPlaces();
    
    if ($places === false) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }
    
    if (empty($places)) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'No places in database'
        ]);
        ob_end_flush();
        exit;
    }
    
    $result = [];
    
    foreach ($places as $placeId => $place) {
        $menu = getPlaceMenu($placeId);
        $highlights = getPlaceHighlights($placeId);
        $websites = getPlaceWebsites($placeId);
        
        $rawType = $place['type'] ?? 'unknown';
        
        // Get website URLs as array
        $websiteLinks = [];
        if (!empty($websites)) {
            foreach ($websites as $site) {
                if (!empty($site['link'])) {
                    $websiteLinks[] = $site['link'];
                }
            }
        }
       


        $result[] = [
            'id' => (int)$placeId,
            'name' => $place['name'] ?? '',
            'type' => $rawType,
            'description' => formatDescription($place['description'] ?? ''),
            'address' => $place['address'] ?? '',
            'lat' => isset($place['lat']) ? (float)$place['lat'] : 0.0,
            'lng' => isset($place['lng']) ? (float)$place['lng'] : 0.0,
            'open_hours' => $place['open_hours'] ?? '',
            'contact' => $place['contact'] ?? '',
            'hotel_websites' => $websiteLinks,
            'menu' => array_map(function($m) {
                return [
                    'name' => $m['name'] ?? '',
                    'price' => isset($m['price']) ? (float)$m['price'] : null
                ];
            }, $menu ?: []),
            'highlights' => array_map(function($h) {
                if (empty($h['image_url'])) return null;
                return [
                    'image_url' => 'uploads/' . ltrim($h['image_url'], '/')
                ];
            }, $highlights ?: [])
        ];
        
        // Remove null highlights
        $result[count($result) - 1]['highlights'] = array_filter($result[count($result) - 1]['highlights']);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result
    ], JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}


function formatDescription($text) {
    if (empty($text)) return '';
    
    // Remove all slashes first
    $text = stripslashes($text);
    
    // Replace ALL instances of "rn" with newlines
    // This handles both the line breaks and the problematic "Selected rnRooms" case
    $text = str_replace('rn', "\n", $text);
    
    return $text;
}


ob_end_flush();
exit;