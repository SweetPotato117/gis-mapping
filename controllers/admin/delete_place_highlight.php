<?php
session_start();
header('Content-Type: application/json');

// Include database connection and functions
require_once '../../models/functions.php';


if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (isset($_GET['highlight_id']) && isset($_GET['place_id'])) {
    $place_id = intval($_GET['place_id']);
    $highlight_id = intval($_GET['highlight_id']);

    $get_photo = getRecord("highlights", "id = $highlight_id AND place_id = $place_id");

    //delete in folder
    if ($get_photo && isset($get_photo['image_url'])) {
        $image_path = '../../uploads/' . $get_photo['image_url'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $delete_photo = deleteRecord("highlights", "id = $highlight_id AND place_id = $place_id");

    header('Location: ../../admin/edit_location.php?id=' . $place_id);
    exit;



  
} else {

    header('Location: ../admin/admin.php');
    exit;
}