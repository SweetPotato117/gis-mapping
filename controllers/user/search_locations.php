<?php
header('Content-Type: application/json');
require_once '../../models/functions.php';

try {
    $searchTerm = is_string($_GET['term'] ?? null) ? mb_substr(trim($_GET['term']), 0, 120) : '';
    
    if (empty($searchTerm)) {
        echo json_encode(['success' => true, 'data' => [], 'count' => 0]);
        exit;
    }
    
    // Use existing function
    $results = searchLocations($searchTerm);
    
    echo json_encode([
        'success' => true,
        'data' => $results,
        'count' => count($results)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
