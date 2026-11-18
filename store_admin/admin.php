<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/index.php');
    exit;
}

// Optional: Check session timeout (2 hours)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: ../admin/index.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

// Get admin role and info
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$is_superadmin = ($admin_role === 'superadmin');

include '../models/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cauayan City Location Management System</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/styles.css?v=5">
    <link rel="stylesheet" href="../assets/css/place-markers.css?v=1">
    <link rel="stylesheet" href="../assets/css/map-admin.css?v=4">
    <link rel="stylesheet" href="../assets/css/admin-shell.css?v=1">
</head>
<body class="admin-shell map-admin">
    <?php require __DIR__ . '/../views/admin_header.php'; ?>
    <main class="main-content">
        <?php require __DIR__ . '/../views/admin_location_editor.php'; ?>
        <?php require __DIR__ . '/../views/admin_map_workspace.php'; ?>
    </main>

    <!-- Location Details Modal -->
    <div id="locationModal" class="modal">
        <div class="modal-content google-style">
            <!-- Header with Cover Image -->
            <div class="modal-header-cover">
                <button class="back-btn" onclick="closeModal()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <img id="modalCoverImage" src="" alt="Location cover" class="cover-image">
            </div>
            
            <!-- Location Title Section -->
            <div class="modal-title-section">
                <h2 id="modalTitle">Location Name</h2>
                <p class="location-type-badge" id="modalTypeBadge">Hotel</p>
            </div>
            
            <!-- Tab Navigation -->
            <div class="google-tabs">
                <button class="google-tab active" data-detail="overview">OVERVIEW</button>
                <button class="google-tab" data-detail="services">SERVICES</button>
                <button class="google-tab" data-detail="about">ABOUT</button>
            </div>
            
            <!-- Tab Content -->
            <div class="modal-body-google">
                <!-- OVERVIEW TAB -->
                <div id="detailOverview" class="google-pane active">
                    <div class="info-section">
                        <div class="info-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="info-content">
                                <span class="info-label">ADDRESS</span>
                                <p id="detailAddress" class="info-value">-</p>
                                <button class="copy-btn" onclick="copyToClipboard('address')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <i class="fas fa-clock"></i>
                            <div class="info-content">
                                <span class="info-label">HOURS</span>
                                <p id="detailHours" class="info-value">-</p>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <i class="fas fa-phone"></i>
                            <div class="info-content">
                                <span class="info-label">PHONE</span>
                                <p id="detailContact" class="info-value">-</p>
                                <button class="copy-btn" onclick="copyToClipboard('phone')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <i class="fas fa-location-dot"></i>
                            <div class="info-content">
                                <span class="info-label">COORDINATES</span>
                                <p id="detailCoords" class="info-value">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons (Only for owner) -->
                    <div class="owner-actions" id="ownerActions" style="display: none;">
                        <button class="action-btn edit-btn" id="editLocation" data-place-id="">
                            <i class="fas fa-edit"></i> Edit Location
                        </button>

                        <button class="action-btn delete-btn" id="deleteLocation">
                            <i class="fas fa-trash"></i>
                            Delete Location
                        </button>
                    </div>
                </div>
                
                <!-- SERVICES TAB -->
                <div id="detailServices" class="google-pane">
                    <div id="detailMenuContent"></div>
                    <div id="detailPhotos" class="photos-grid"></div>
                </div>

                <!-- ABOUT TAB -->
                <div id="detailAbout" class="google-pane">
                    <div class="about-section">
                        <h4>DESCRIPTION</h4>
                        <p id="detailDescription">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/get_admin_data.js?v=5"></script>
    <script src="../assets/js/insert_data.js?v=2"></script>
    <script src="../assets/js/edit_data.js?v=3"></script>
    <script src="../assets/js/delete_data.js?v=1"></script>
    <script src="../assets/js/script.js?v=3"></script>
    <script src="../assets/js/place-markers.js?v=1"></script>
    <script src="../assets/js/map.js?v=5"></script>
</body>
</html>
