<?php
session_start();
require_once '../../models/functions.php';

ob_start();
header('Content-Type: application/json');

try {
    global $conn;
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Get all places using existing function
    $places = getAllPlaces();
    
    if (empty($places)) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'No places in database',
            'current_admin_id' => $_SESSION['admin_id'] ?? null
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
        
        // Check if current admin owns this location
        $adminId = isset($place['admin_id']) ? (int)$place['admin_id'] : null;
        $currentAdminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
        $currentAdminRole = $_SESSION['admin_role'] ?? 'admin';
        
        // Superadmin can access everything, regular admin only their locations
        $isOwner = false;
        if ($currentAdminRole === 'superadmin') {
            $isOwner = true;
        } else {
            $isOwner = ($adminId !== null && $currentAdminId !== null && $adminId === $currentAdminId);
        }
        

        // Process highlights with correct path
        $processedHighlights = [];
        if (!empty($highlights)) {
            foreach ($highlights as $h) {
                if (!empty($h['image_url'])) {
                    // Remove any leading slashes or '../uploads/' prefix
                    $cleanPath = ltrim($h['image_url'], '/');
                    $cleanPath = str_replace('../../uploads/', '', $cleanPath);
                    $cleanPath = str_replace('uploads/', '', $cleanPath);
                    
                    // Build correct path relative to where map.js is loaded
                    $processedHighlights[] = [
                        'imageurl' => '../uploads/' . $cleanPath
                    ];
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
            'blur_services' => (int)($place['blur_services'] ?? 0),
            'hotel_websites' => $websiteLinks,
            'admin_id' => $adminId,
            'is_owner' => $isOwner,
            'menu' => array_map(function($m) {
                return [
                    'name' => $m['name'] ?? '',
                    'price' => isset($m['price']) ? (float)$m['price'] : null
                ];
            }, $menu ?: []),
            'highlights' => $processedHighlights
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result,
        'current_admin_id' => $currentAdminId,
        'admin_role' => $currentAdminRole
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
    if (empty($text)) {
        return '';
    }
    
    // Remove all slashes first
    $text = stripslashes($text);
    
    // Replace ALL instances of \r\n with newlines
    $text = str_replace('\r\n', "\n", $text);
    
    return $text;
}

ob_end_flush();
exit;
