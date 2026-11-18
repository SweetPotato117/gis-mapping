<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sign in is required.']);
    exit;
}

$isSuperadmin = ($_SESSION['admin_role'] ?? '') === 'superadmin';
$currentAdminId = (int)$_SESSION['admin_id'];
$placeId = filter_input(INPUT_GET, 'place_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$byPlace = array_key_exists('place_id', $_GET);
if (!$byPlace && !$isSuperadmin) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Superadmin access is required.']);
    exit;
}

require_once '../../models/functions.php';
require_once '../../models/place_about.php';

$adminId = filter_var(
    $_GET['admin_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (($byPlace && !$placeId) || (!$byPlace && !$adminId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid place or store admin is required.']);
    exit;
}

try {
    if (!$byPlace) {
        $account = $conn->prepare("SELECT id FROM admin WHERE id = ? AND role = 'admin'");
        $account->bind_param('i', $adminId);
        $account->execute();
        if (!$account->get_result()->fetch_assoc()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Store admin not found.']);
            exit;
        }
    }

    $filter = $byPlace ? 'p.id = ? AND (? = 1 OR p.admin_id = ?)' : 'p.admin_id = ?';
    $placesQuery = $conn->prepare(
        "SELECT p.*, a.username AS owner_name
         FROM places p LEFT JOIN admin a ON a.id = p.admin_id
         WHERE $filter
         ORDER BY p.name, p.id"
    );
    if (!$placesQuery) {
        throw new RuntimeException($conn->error);
    }
    if ($byPlace) {
        $allowAnyOwner = (int)$isSuperadmin;
        $placesQuery->bind_param('iii', $placeId, $allowAnyOwner, $currentAdminId);
    } else {
        $placesQuery->bind_param('i', $adminId);
    }
    $placesQuery->execute();
    $placesResult = $placesQuery->get_result();

    $places = [];
    while ($place = $placesResult->fetch_assoc()) {
        $placeId = (int)$place['id'];
        $highlights = [];
        foreach (getPlaceHighlights($placeId) ?: [] as $highlight) {
            $filename = basename(str_replace('\\', '/', (string)($highlight['image_url'] ?? '')));
            if ($filename !== '' && $filename !== '.' && $filename !== '..') {
                $highlights[] = ['id' => (int)$highlight['id'], 'image_url' => '../uploads/' . rawurlencode($filename)];
            }
        }

        $places[] = [
            'id' => $placeId,
            'admin_id' => (int)($place['admin_id'] ?? 0),
            'owner_name' => $place['owner_name'] ?? '',
            'name' => $place['name'] ?? '',
            'type' => $place['type'] ?? '',
            'description' => stripslashes((string)($place['description'] ?? '')),
            'address' => $place['address'] ?? '',
            'lat' => isset($place['lat']) ? (float)$place['lat'] : null,
            'lng' => isset($place['lng']) ? (float)$place['lng'] : null,
            'open_hours' => $place['open_hours'] ?? '',
            'contact' => $place['contact'] ?? '',
            'blur_services' => (int)($place['blur_services'] ?? 0),
            'place_about' => decodePlaceAbout($place['place_about'] ?? null),
            'menu' => getPlaceMenu($placeId) ?: [],
            'hotel_websites' => array_values(array_filter(array_map(
                static fn ($website) => $website['link'] ?? '',
                getPlaceWebsites($placeId) ?: []
            ))),
            'highlights' => $highlights
        ];
    }

    if ($byPlace && !$places) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Place not found or unavailable to your account.']);
        exit;
    }
    echo json_encode(['success' => true, 'items' => $places], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $error) {
    error_log('[get_admin_places] ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load places for this store admin.']);
}
