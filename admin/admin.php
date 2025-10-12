<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Optional: Check session timeout (2 hours)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: index.php?timeout=1');
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
    <link rel="stylesheet" href="../assets/css/styles.css?v=1">
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="topnav">
        <div class="nav-brand">
            <h2>Cauayan City Map</h2>
        </div>
        
        <div class="nav-right">
            <!-- Hamburger Menu Button -->
            <button class="hamburger-menu" id="hamburgerBtn" aria-label="Menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
        
        <!-- Dropdown Menu -->
        <div class="dropdown-menu" id="dropdownMenu">
            <a href="account.php" class="dropdown-item">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </a>
            <a href="admin.php" class="dropdown-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Map Management</span>
            </a>
            <a href="add_account.php" class="dropdown-item">
                <i class="fas fa-user-cog"></i>
                <span>Account Manager</span>
            </a>
            <a href="../controllers/admin/admin_logout.php" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="filter-buttons-container">
        <button class="filter-btn" data-filter="view-all" id="viewAllToggle">
            <i class="fas fa-eye"></i>
            View all locations
        </button>
    </div>

    <!-- Floating Search Bar -->
    <div class="floating-search-container">
        <div class="floating-search-bar">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="mapLocationSearch" placeholder="Search locations on map..." class="search-input">
            <button class="search-clear" id="mapSearchClear" aria-label="Clear search">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Admin Panel -->
        <div class="admin-panel">
            <div class="panel-header">
                <h3><?php echo htmlspecialchars($admin_username); ?></h3>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <button class="tab-btn active" data-tab="overview">
                    <span class="tab-icon"></span>
                    Overview
                </button>
                <button class="tab-btn" data-tab="services">
                    <span class="tab-icon"></span>
                    Services
                </button>
                <button class="tab-btn" data-tab="about">
                    <span class="tab-icon"></span>
                    About
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Overview Tab -->
                <div id="overview" class="tab-pane active">
                    <form id="locationForm">
                        <input type="hidden" id="placeId" name="id">
                        
                        <div class="form-group">
                            <label for="locationName">Name of Location <span class="required">*</span></label>
                            <input type="text" id="locationName" name="name" placeholder="e.g., Mang Juan's Restaurant" required>
                        </div>

                        <div class="form-group">
                            <label for="locationType">Location Type <span class="required">*</span></label>
                            <select id="locationType" name="type" required>
                                <option value="">Select Type</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="hotels">Hotels</option>
                                <option value="tourist_destination">Tourist Destination</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="admin">Store Admin</label>
                            <input type="text" id="adminSearch" placeholder="Start typing to search admins..." class="search-input">
                            <select name="admin" id="admin" size="8" style="display: none; width: 100%;">
                                <option value="">Select Admin</option>
                            </select>
                            <div id="searchResults" class="search-results" style="display: none;"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="latitude">Latitude <span class="required">*</span></label>
                                <input type="text" id="latitude" name="lat" placeholder="16.930544" required>
                            </div>
                            <div class="form-group">
                                <label for="longitude">Longitude <span class="required">*</span></label>
                                <input type="text" id="longitude" name="lng" placeholder="121.739977" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" placeholder="e.g., 123 Main St, Cauayan City">
                        </div>

                        <div class="form-group">
                            <label for="openHours">Open Hours</label>
                            <input type="text" id="openHours" name="open_hours" placeholder="e.g., 8:00 AM - 10:00 PM">
                        </div>

                        <div class="form-group">
                            <label for="contact">Contact</label>
                            <input type="text" id="contact" name="contact" placeholder="Phone number">
                        </div>

                    </form>
                </div>

                <!-- Services Tab -->
                <div id="services" class="tab-pane">
                    <div class="services-notice">
                        <p>Please select a location type in the Overview tab to see available service options.</p>
                    </div>

                    <!-- RESTAURANT SERVICES SECTION -->
                    <div id="foodServices" class="service-section" style="display:none;">
                        <h4>Menu & Highlights</h4>
                        <label>Websites</label>
                        <div id="websitesList">
                            <div class="website-item">
                                <input type="url" class="website-input" name="websites[]" placeholder="https://website.com">
                                <button type="button" class="btn-remove-website" onclick="removeWebsite(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary add-website-btn">
                            <i class="fas fa-plus"></i> Add Website
                        </button>
                        <br><br>
                        
                        <div class="form-group">
                            <label>Menu Items</label>
                            <div id="menuList">
                                <div class="menu-item">
                                    <input type="text" placeholder="Item name" class="menu-name" name="menu_name[]">
                                    <input type="number" step="0.01" placeholder="Price" class="menu-price" name="menu_price[]">
                                    <button type="button" class="btn-remove" onclick="removeMenuItem(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary" id="addMenuItem">
                                <i class="fas fa-plus"></i> Add Menu Item
                            </button>
                        </div>

                        <div class="form-group">
                            <label>Highlights (Photos)</label>
                            <input type="file" id="foodPhotos" accept="image/*" multiple>
                            <div id="foodPhotosPreview" class="photo-preview"></div>
                        </div>
                    </div>

                    <!-- HOTEL SERVICES SECTION -->
                    <div id="hotelServices" class="service-section" style="display:none;">
                        <h4>Hotel Highlights</h4>
                        <div class="form-group">
                            <label>Websites</label>
                            <div id="websitesList">
                                <div class="website-item">
                                    <input type="url" class="website-input" name="websites[]" placeholder="https://hotelwebsite.com">
                                    <button type="button" class="btn-remove-website" onclick="removeWebsite(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary add-website-btn">
                                <i class="fas fa-plus"></i> Add Website
                            </button>
                            <br><br>
                            
                            <label>Highlights (Photos)</label>
                            <input type="file" id="hotelPhotos" accept="image/*" multiple>
                            <div id="hotelPhotosPreview" class="photo-preview"></div>
                        </div>
                    </div>

                    <!-- TOURIST DESTINATION SERVICES SECTION -->
                    <div id="touristServices" class="service-section" style="display:none;">
                        <h4>Tourist Destination Highlights</h4>
                        
                        <div class="form-group">
                            <label>Websites</label>
                            <div id="websitesList">
                                <div class="website-item">
                                    <input type="url" class="website-input" name="websites[]" placeholder="https://website.com">
                                    <button type="button" class="btn-remove-website" onclick="removeWebsite(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary add-website-btn">
                                <i class="fas fa-plus"></i> Add Website
                            </button>
                            <br><br>
                            
                            <label>Highlights (Photos)</label>
                            <input type="file" id="touristPhotos" accept="image/*" multiple>
                            <div id="touristPhotosPreview" class="photo-preview"></div>
                        </div>
                    </div>
                </div>

                <!-- About Tab -->
                <div id="about" class="tab-pane">
                    <div class="form-group">
                        <label for="description">Description <span class="required">*</span></label>
                        <textarea id="description" name="description" rows="8" placeholder="Enter a detailed description of the location..." required></textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn btn-success" id="saveLocation">
                    <i class="fas fa-save"></i> Save Location
                </button>
                <button type="button" class="btn btn-secondary" id="clearForm">
                    <i class="fas fa-eraser"></i> Clear Form
                </button>
            </div>
        </div>

        <!-- Map Container -->
        <div id="map"></div>
    </div>

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
    <script src="../assets/js/get_admin_data.js?v=1"></script>
    <script src="../assets/js/insert_data.js?v=1"></script>
    <script src="../assets/js/edit_data.js?v=1"></script>
    <script src="../assets/js/delete_data.js?v=1"></script>
    <script src="../assets/js/script.js?v=1"></script>
    <script src="../assets/js/hamburger_menu.js?v=1"></script>
    <script src="../assets/js/map.js?v=1"></script>
</body>
</html>