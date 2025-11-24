<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cauayan City Location Management System</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="index.css?v=1">
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="topnav">
        <div class="nav-brand">
            <h2>Cauayan City Map</h2>
        </div>
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

    <!-- Filter Buttons -->
    <div class="filter-buttons-container">
        <button class="filter-btn active" data-filter="all">
            <i class="fas fa-map-marked-alt"></i>
            <span class="filter-text">All</span>
        </button>
        <button class="filter-btn" data-filter="restaurant">
            <i class="fas fa-utensils"></i>
            <span class="filter-text">Restaurants</span>
        </button>
        <button class="filter-btn" data-filter="hotels">
            <i class="fas fa-hotel"></i>
            <span class="filter-text">Hotels</span>
        </button>
        <button class="filter-btn" data-filter="touristdestination">
            <i class="fas fa-camera"></i>
            <span class="filter-text">Tourist Attractions</span>
        </button>
    </div>

    
        <!-- Recenter on User Location Button 
    <button class="recenter-location-btn" id="recenterBtn" title="Center on my location">
        <i class="fas fa-location-arrow"></i>
    </button> -->


    <!-- Main Content -->
    <div class="main-content">
        <!-- Map Container -->
        <div id="map"></div>
    </div>

    <!-- Location Details Modal - Google Maps Style -->
    <div id="locationModal" class="modal">
        <div class="modal-content google-style">
            <!-- Modal Header with Image -->
            <div class="modal-hero-image" id="modalHeroImage">
                <button class="modal-close-btn" onclick="closeLocationModal()">
                    <i class="fas fa-arrow-left"></i>
                </button>
            </div>
            
            <!-- Location Title Section -->
            <div class="location-header">
                <h2 id="modalTitle" class="location-title">Location Name</h2>
                <div class="location-meta">
                    <span class="location-category" id="locationCategory">Restaurant</span>
                </div>
            </div>
            
            <!-- Quick Actions Bar -->
            <div class="quick-actions-bar">
                <button class="quick-action-btn" onclick="startNavigationFromModal()">
                    <i class="fas fa-directions"></i>
                    <span>Directions</span>
                </button>
            </div>
            
            <!-- Tab Navigation -->
            <div class="google-tabs">
                <button class="google-tab active" data-tab="overview" onclick="switchTab('overview')">
                    Overview
                </button>
                <button class="google-tab" data-tab="services" onclick="switchTab('services')">
                    Services
                </button>
                <button class="google-tab" data-tab="about" onclick="switchTab('about')">
                    About
                </button>
            </div>
            
            <!-- Tab Content -->
            <div class="google-tab-content">
                <!-- Overview Tab -->
                <div id="tab-overview" class="google-tab-pane active">
                    <!-- Address Section -->
                    <div class="info-section">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Address</div>
                            <div class="info-value" id="detailAddress">-</div>
                        </div>
                        <button class="info-action-btn" onclick="copyAddress()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    
                    <!-- Hours Section -->
                    <div class="info-section" id="hoursSection">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Hours</div>
                            <div class="info-value" id="detailHours">-</div>
                        </div>
                    </div>
                    
                    <!-- Phone Section -->
                    <div class="info-section" id="phoneSection">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Phone</div>
                            <div class="info-value" id="detailContact">-</div>
                        </div>
                    </div>
                    
                    <!-- Coordinates Section -->
                    <div class="info-section">
                        <div class="info-icon">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Coordinates</div>
                            <div class="info-value coordinates" id="detailCoords">-</div>
                        </div>
                        <button class="info-action-btn" onclick="copyCoordinates()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                
                <!-- Services Tab -->
                <div id="tab-services" class="google-tab-pane">
                    <!-- Menu/Services (Only for restaurants) -->
                    <div class="services-section" id="menuSection" style="display: none;">
                        <h3 class="section-title">
                            <i class="fas fa-utensils"></i> Menu
                        </h3>
                        <div id="detailMenuContent" class="menu-grid">
                            <!-- Menu items will be inserted here -->
                        </div>
                    </div>
                    
                    <!-- Photo Gallery -->
                    <div class="services-section" id="photosSection">
                        <h3 class="section-title">
                            <i class="fas fa-images"></i> Photos
                        </h3>
                        <div id="detailPhotos" class="photos-grid">
                            <!-- Photos will be inserted here -->
                        </div>
                    </div>
                    
                    <!-- Websites & Social Media -->
                    <div class="services-section" id="websitesSection">
                        <h3 class="section-title">
                            <i class="fas fa-globe"></i> Website & Social Media
                        </h3>
                        <div id="detailWebsites" class="websites-list">
                            <!-- Websites will be inserted here -->
                        </div>
                    </div>
                </div>
                
                <!-- About Tab -->
                <div id="tab-about" class="google-tab-pane">
                    <div class="about-section">
                        <div class="info-item">
                            <div class="info-item-value description" id="detailDescription">No information</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/user-js/script.js?v=1"></script>
    <script src="user-map.js?v=1"></script>

    <!-- Blur Services Logic -->
    <style>
        .service-blurred {
            filter: blur(12px) !important;
            opacity: 0.6 !important;
            pointer-events: none !important;
            user-select: none !important;
        }
    </style>

    <script>
        // Function to apply blur to modal services
        function applyModalServiceBlur(shouldBlur) {
            console.log('applyModalServiceBlur called with shouldBlur:', shouldBlur);
            
            // Blur the header/hero image
            const heroImage = document.getElementById('modalHeroImage');
            if (heroImage) {
                if (shouldBlur) {
                    heroImage.classList.add('service-blurred');
                    console.log('Added blur to hero image');
                } else {
                    heroImage.classList.remove('service-blurred');
                }
            }
            
            // Blur menu section
            const menuSection = document.getElementById('menuSection');
            if (menuSection) {
                if (shouldBlur) {
                    menuSection.classList.add('service-blurred');
                    console.log('Added blur to menu section');
                } else {
                    menuSection.classList.remove('service-blurred');
                }
            }
            
            // Blur menu content
            const menuContent = document.getElementById('detailMenuContent');
            if (menuContent) {
                if (shouldBlur) {
                    menuContent.classList.add('service-blurred');
                    console.log('Added blur to menu content');
                } else {
                    menuContent.classList.remove('service-blurred');
                }
            }
            
            // Blur websites section
            const websitesSection = document.getElementById('websitesSection');
            if (websitesSection) {
                if (shouldBlur) {
                    websitesSection.classList.add('service-blurred');
                    console.log('Added blur to websites section');
                } else {
                    websitesSection.classList.remove('service-blurred');
                }
            }
            
            // Blur websites content
            const websites = document.getElementById('detailWebsites');
            if (websites) {
                if (shouldBlur) {
                    websites.classList.add('service-blurred');
                    console.log('Added blur to websites');
                } else {
                    websites.classList.remove('service-blurred');
                }
            }
            
            // Blur photos section
            const photosSection = document.getElementById('photosSection');
            if (photosSection) {
                if (shouldBlur) {
                    photosSection.classList.add('service-blurred');
                    console.log('Added blur to photos section');
                } else {
                    photosSection.classList.remove('service-blurred');
                }
            }
            
            // Blur photos gallery
            const photos = document.getElementById('detailPhotos');
            if (photos) {
                if (shouldBlur) {
                    photos.classList.add('service-blurred');
                    console.log('Added blur to photos');
                } else {
                    photos.classList.remove('service-blurred');
                }
            }
        }

        // Hook into location display to check blur_services
        // This will be called from user-map.js when displaying location details
        window.onDisplayLocationModal = function(location) {
            console.log('Location blur_services:', location.blur_services);
            applyModalServiceBlur(location.blur_services == 1);
        };
    </script>

    <!-- Footer Disclaimer -->
    <footer style="background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            The information and data on this website are current as of October 2025
        </p>
    </footer>

</body>
</html>
