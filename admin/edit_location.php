<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../models/functions.php';

function formatDescription($text) {
    if (empty($text)) return '';
    
    // Remove all slashes first
    $text = stripslashes($text);
    
    // Replace ALL instances of "rn" with newlines
    $text = str_replace('rn', "\n", $text);
    
    return $text;
}

$place = getRecord('places', "id = {$_GET['id']}");

// Get admin role and info
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$is_superadmin = ($admin_role === 'superadmin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cauayan City Location Management System</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css?v=1">
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="topnav">
        <div class="nav-brand">
            <h2>Cauayan City Map (Edit Page)</h2>
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
            <a href="admin.php" class="dropdown-item active">
                <i class="fas fa-clipboard-list"></i>
                <span>Manage</span>
            </a>
            <a href="../controllers/admin/admin_logout.php" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
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
                        <input type="hidden" id="placeId" name="id" value="<?php echo isset($_GET['id']) ? (int)$_GET['id'] : ''; ?>">
                        
                        <div class="form-group">
                            <label for="locationName">Name of Location <span class="required">*</span></label>
                            <input type="text" id="locationName" name="name" value="<?php echo $place['name']; ?>" placeholder="e.g., Mang Juan's Restaurant" required>
                        </div>

                        <div class="form-group">
                            <label for="locationType">Location Type <span class="required">*</span></label>
                            <select id="locationType" name="type" required readonly>
                                <?php if ($place['type'] === 'restaurant'): ?>
                                <option value="restaurant" selected>Restaurant</option>
                                <?php elseif ($place['type'] === 'hotels'): ?>
                                <option value="hotels" selected>Hotels</option>
                                <?php elseif ($place['type'] === 'tourist_destination'): ?>
                                <option value="tourist_destination" selected>Tourist Destination</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="latitude">Latitude <span class="required">*</span></label>
                                <input type="text" id="latitude" name="lat" value="<?php echo $place['lat']; ?>" placeholder="16.930544" required>
                            </div>
                            <div class="form-group">
                                <label for="longitude">Longitude <span class="required">*</span></label>
                                <input type="text" id="longitude" name="lng" value="<?php echo $place['lng']; ?>" placeholder="121.739977" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?php echo $place['address']; ?>" placeholder="e.g., 123 Main St, Cauayan City">
                        </div>

                        <div class="form-group">
                            <label for="openHours">Open Hours</label>
                            <input type="text" id="openHours" name="open_hours" value="<?php echo $place['open_hours']; ?>" placeholder="e.g., 8:00 AM - 10:00 PM">
                        </div>

                        <div class="form-group">
                            <label for="contact">Contact</label>
                            <input type="text" id="contact" name="contact" value="<?php echo $place['contact']; ?>" placeholder="Phone number">
                        </div>
                    </form>
                </div>

                <!-- Services Tab -->
                <div id="services" class="tab-pane">
                    <label style="margin-bottom:10px;display:block;">
                        <input type="checkbox" id="blurServicesToggle" <?php echo ($place['blur_services'] == 1) ? 'checked' : ''; ?> />
                        Blur all services content
                    </label>
                    <div id="servicesBlurOverlay" style="
                        display:none;
                        position:absolute;
                        z-index:1000;
                        background:rgba(255,255,255,0.89);
                        width:100%;
                        height:100%;
                        top:0;
                        left:0;
                        align-items:center;
                        justify-content:center;
                        text-align:center;">
                        <div style="margin-top:150px;font-size:1.35em;color:#2a2a2a;width:90%;max-width:400px;">
                            Go to the official website to view services
                        </div>
                    </div>

                <?php 
                if ($place['type'] === 'restaurant') :
                    $menu = getPlaceMenu($place['id']);
                    $highlights = getPlaceHighlights($place['id']);
                    $websites = getPlaceWebsites($place['id']);
                ?>

                <!-- RESTAURANT SERVICES SECTION -->
                    <div id="foodServices" class="service-section">
                        <h4>Menu & Highlights</h4>
                        <label>Websites</label>
                        <?php foreach ($websites as $site) : ?>
                            <div id="websitesList">
                                <div class="website-item">
                                    <input type="url" class="website-input" name="websites[]" value="<?php echo htmlspecialchars($site['link']); ?>" placeholder="https://website.com">
                                    <button type="button" class="btn-remove-website" onclick="removeWebsite(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
                            <?php foreach ($menu as $item) : ?>
                                <div id="menuList">
                                    <div class="menu-item">
                                        <input type="text" placeholder="Item name" class="menu-name" name="menu_name[]" value="<?php echo $item['name']; ?>">
                                        <input type="number" step="0.01" placeholder="Price" class="menu-price" name="menu_price[]" value="<?php echo $item['price']; ?>">
                                        <button type="button" class="btn-remove" onclick="removeMenuItem(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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

                            <?php foreach ($highlights as $photo) : ?>
                                <div class="preview-img-wrapper" style="width: 120px; height: 80px;">
                                    <img src="../uploads/<?php echo htmlspecialchars($photo['image_url']); ?>" alt="Highlight Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#deletePhotoModal" 
                                            data-photo-id="<?php echo $photo['id']; ?>" 
                                            data-place-id="<?php echo $place['id']; ?>" 
                                            class="preview-remove-btn delete-photo-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php 
                elseif ($place['type'] === 'hotels') :
                    $highlights = getPlaceHighlights($place['id']);
                    $websites = getPlaceWebsites($place['id']);
                ?>
                    <!-- HOTEL SERVICES SECTION -->
                    <div id="hotelServices" class="service-section">
                        <h4>Hotel Highlights</h4>
                        
                        <div class="form-group">
                            <label>Websites</label>
                            <?php foreach ($websites as $site) : ?>
                                <div id="websitesList">
                                    <div class="website-item">
                                        <input type="url" class="website-input" name="websites[]" value="<?php echo htmlspecialchars($site['link']); ?>" placeholder="https://website.com">
                                        <button type="button" class="btn-remove-website" onclick="removeWebsite(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
                            <?php foreach ($highlights as $photo) : ?>
                                <div class="preview-img-wrapper" style="width: 120px; height: 80px;">
                                    <img src="../uploads/<?php echo htmlspecialchars($photo['image_url']); ?>" alt="Highlight Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#deletePhotoModal" 
                                            data-photo-id="<?php echo $photo['id']; ?>" 
                                            data-place-id="<?php echo $place['id']; ?>" 
                                            class="preview-remove-btn delete-photo-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php 
                elseif ($place['type'] === 'tourist_destination') :
                    $highlights = getPlaceHighlights($place['id']);
                    $websites = getPlaceWebsites($place['id']);
                ?>
                    <!-- TOURIST DESTINATION SERVICES SECTION -->
                    <div id="touristServices" class="service-section">
                        <h4>Tourist Destination Highlights</h4>
                        
                        <div class="form-group">
                            <label>Websites</label>
                            <?php foreach ($websites as $site) : ?>
                                <div id="websitesList">
                                    <div class="website-item">
                                        <input type="url" class="website-input" name="websites[]" value="<?php echo htmlspecialchars($site['link']); ?>" placeholder="https://website.com">
                                        <button type="button" class="btn-remove-website" onclick="removeWebsite(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
                            <?php foreach ($highlights as $photo) : ?>
                                <div class="preview-img-wrapper" style="width: 120px; height: 80px;">
                                    <img src="../uploads/<?php echo htmlspecialchars($photo['image_url']); ?>" alt="Highlight Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#deletePhotoModal" 
                                            data-photo-id="<?php echo $photo['id']; ?>" 
                                            data-place-id="<?php echo $place['id']; ?>" 
                                            class="preview-remove-btn delete-photo-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                </div>

                <!-- About Tab -->
                <div id="about" class="tab-pane">
                    <div class="form-group">
                        <label for="description">Description <span class="required">*</span></label>
                        <textarea id="description" name="description" rows="8" placeholder="Enter a detailed description of the location..." required><?php echo formatDescription($place['description']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn btn-success" id="saveLocation">
                    <i class="fas fa-save"></i> Save Location
                </button>
            </div>
        </div>

        <!-- Map Container -->
        <div id="map"></div>
    </div>

    <!-- Location Details Modal -->
    <div id="locationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Location Details</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="detail-tabs">
                    <button class="detail-tab active" data-detail="overview">Overview</button>
                    <button class="detail-tab" data-detail="menu">Services</button>
                    <button class="detail-tab" data-detail="about">About</button>
                </div>
                
                <div class="detail-content">
                    <div id="detailOverview" class="detail-pane active">
                        <p><strong>Type:</strong> <span id="detailType"></span></p>
                        <p><strong>Coordinates:</strong> <span id="detailCoords"></span></p>
                        <p><strong>Address:</strong> <span id="detailAddress"></span></p>
                        <p><strong>Open Hours:</strong> <span id="detailHours"></span></p>
                        <p><strong>Contact:</strong> <span id="detailContact"></span></p>
                        <br>
                        <button class="btn btn-secondary" id="editLocation" data-location-id="" onclick='loadEditForm()'>
                            <i class="fas fa-edit"></i> Edit Location
                        </button>
                        <button class="btn btn-danger" id="deleteLocation" data-location-id="" onclick='deleteLocation()'>
                            <i class="fas fa-trash"></i> Delete Location
                        </button>
                    </div>
                    
                    <div id="detailMenu" class="detail-pane">
                        <div id="detailMenuContent"></div>
                        <p><strong>Websites:</strong> <span id="detailWebsites"></span></p>
                        <div id="detailPhotos" class="photo-gallery"></div>
                    </div>
                    
                    <div id="detailAbout" class="detail-pane">
                        <p id="detailDescription"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Photo Modal -->
    <div class="modal fade" id="deletePhotoModal" tabindex="-1" role="dialog" aria-labelledby="deletePhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePhotoModalLabel">Delete Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this photo?</p>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-danger" id="confirmDeletePhoto">Delete</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/update_data.js?v=1"></script>
    <script src="../assets/js/edit_script.js?v=1"></script>
    <script src="../assets/js/hamburger_menu.js?v=1"></script>
    <script src="../assets/js/edit_map.js?v=1"></script>
    
    <!-- Verify blur checkbox state -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const blurToggle = document.getElementById('blurServicesToggle');
            if (blurToggle) {
                console.log('Blur Services Toggle found');
                console.log('Is checked:', blurToggle.checked);
                console.log('Current blur_services value:', blurToggle.checked ? 1 : 0);
                
                // Listen for changes
                    blurToggle.addEventListener('change', function() {
                        console.log('Blur toggle changed to:', this.checked);
                        console.log('Will be saved as:', this.checked ? 1 : 0);

                        // Send immediate update to server to persist blur_services
                        const placeIdEl = document.getElementById('placeId');
                        const placeId = placeIdEl ? placeIdEl.value : null;
                        if (!placeId) {
                            console.error('No placeId found; cannot save blur setting');
                            return;
                        }

                        const fd = new FormData();
                        fd.append('id', placeId);
                        fd.append('blur_services', this.checked ? 1 : 0);

                        fetch('../controllers/admin/set_blur_services.php', {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin'
                        }).then(r => r.json()).then(json => {
                            if (json && json.success) {
                                console.log('Blur persisted on server:', json.blur_services, 'affected_rows:', json.affected_rows);
                                // If affected_rows === 0, the value was identical or DB didn't change; still reflect the requested state
                                blurToggle.checked = json.blur_services == 1;
                            } else {
                                console.error('Failed to persist blur:', json);
                                alert('Failed to save blur setting');
                                // revert checkbox to previous state if failure
                                blurToggle.checked = !blurToggle.checked;
                            }
                        }).catch(err => {
                            console.error('Error updating blur setting:', err);
                            alert('Error saving blur setting');
                            blurToggle.checked = !blurToggle.checked;
                        });
                    });
            } else {
                console.error('Blur Services Toggle NOT found!');
            }
        });
    </script>
</body>
</html>
