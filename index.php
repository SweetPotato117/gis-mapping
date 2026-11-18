<?php
require_once __DIR__ . '/models/place_about.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Cauayan City Location Management System</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="assets/css/place-markers.css?v=1">
    <link rel="stylesheet" href="index.css?v=18">
</head>
<body class="public-map">
    <!-- Top Navigation Bar -->
    <div class="topnav">
        <div class="nav-brand">
            <h1><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Cauayan City Map</h1>
        </div>
    </div>

    <!-- Floating Search Bar -->
    <div class="floating-search-container">
        <div class="floating-search-bar">
            <i class="fas fa-search search-icon"></i>
            <input type="search" id="mapLocationSearch" placeholder="Search places, dishes, or amenities" aria-label="Search places, dishes, or About tags" maxlength="120" class="search-input">
            <button class="search-clear" id="mapSearchClear" aria-label="Clear search">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="filter-buttons-container">
        <button class="filter-btn active" data-filter="all" aria-pressed="true">
            <i class="fas fa-map-marked-alt"></i>
            <span class="filter-text">All</span>
        </button>
        <button class="filter-btn" data-filter="restaurant" aria-pressed="false">
            <i class="fas fa-utensils"></i>
            <span class="filter-text">Restaurants</span>
        </button>
        <button class="filter-btn" data-filter="hotels" aria-pressed="false">
            <i class="fas fa-hotel"></i>
            <span class="filter-text">Hotels</span>
        </button>
        <button class="filter-btn" data-filter="touristdestination" aria-pressed="false">
            <i class="fas fa-camera"></i>
            <span class="filter-text">Tourist Attractions</span>
        </button>
    </div>


    <button type="button" class="recenter-location-btn" id="recenterBtn" aria-label="Locate me" title="Locate me">
        <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i>
    </button>
    <button type="button" class="satellite-toggle" id="satelliteToggle" aria-pressed="true">
        <i class="fa-solid fa-layer-group" aria-hidden="true"></i> Satellite
    </button>


    <!-- Main Content -->
    <div class="main-content">
        <!-- Map Container -->
        <div id="map"></div>
    </div>

    <!-- Location details -->
    <div id="locationModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-content google-style">
            <div class="place-sheet-toolbar">
                <button type="button" class="place-sheet-handle" aria-label="Resize place details. Use the up and down arrow keys to change the height."></button>
                <button type="button" class="modal-close-btn" onclick="closeLocationModal()" aria-label="Close place details">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="location-cover">
                <div class="modal-hero-image" id="modalHeroImage"></div>
            </div>

            <!-- Mobile Go Back Button -->
            <div class="mobile-back-button">
                <button onclick="closeLocationModal()" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </button>
            </div>

            <!-- Location Title Section -->
            <div class="location-header">
                <p class="location-category" id="locationCategory">Restaurant</p>
                <h2 id="modalTitle" class="location-title">Location Name</h2>
            </div>

            <!-- Quick Actions Bar -->
            <div class="quick-actions-bar">
                <button class="quick-action-btn" onclick="startNavigationFromModal()">
                    <i class="fa-solid fa-diamond-turn-right" aria-hidden="true"></i>
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
                        <button class="info-action-btn" onclick="copyAddress()" aria-label="Copy address">
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
                        <button class="info-action-btn" onclick="copyCoordinates()" aria-label="Copy coordinates">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>


                <!-- Services Tab -->
                <div id="tab-services" class="google-tab-pane">
                    <!-- Websites & Social Media (moved to top) -->
                    <div class="services-section" id="websitesSection">
                        <h3 class="section-title">
                            <i class="fas fa-globe"></i> Website & Social Media
                        </h3>
                        <div id="detailWebsites" class="websites-list">
                            <!-- Websites will be inserted here -->
                        </div>
                    </div>

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
                </div>

                <!-- About Tab -->
                <div id="tab-about" class="google-tab-pane">
                    <?php foreach (PLACE_ABOUT_GROUPS as $aboutKey => $aboutGroup): ?>
                        <section class="place-about-section" data-about-group="<?= $aboutKey ?>" hidden>
                            <h3><?= $aboutGroup['title'] ?></h3>
                            <ul class="place-about-list">
                                <?php foreach ($aboutGroup['options'] as $optionKey => [$optionLabel, $optionIcon]): ?>
                                    <li class="place-about-item" data-about-option="<?= $optionKey ?>" hidden>
                                        <svg class="place-about-icon" aria-hidden="true" focusable="false">
                                            <use href="assets/icons/place-details.svg?v=3#<?= $optionIcon ?>"></use>
                                        </svg>
                                        <span><?= $optionLabel ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endforeach; ?>
                    <div class="about-section" id="placeDescriptionSection">
                        <div class="info-item">
                            <div class="info-item-value description" id="detailDescription">No information</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <dialog id="placePhotoViewer" class="place-photo-viewer" aria-label="Place photo">
        <img id="placeViewerImage" alt="Place photo">
        <form method="dialog">
            <button type="submit" class="place-photo-close" aria-label="Close photo" autofocus>
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </form>
    </dialog>

    <!-- Footer Disclaimer -->
    <?php $disclaimer = 'Disclaimer: Are you a business owner? Help customers find you! Submit your details to be featured on our tourism map. For inquiries, contact us at (admin@cauayan-gis-mapping.site).'; ?>
    <footer class="footer-marquee">
        <div class="footer-marquee-track">
            <span><?= htmlspecialchars($disclaimer, ENT_QUOTES, 'UTF-8') ?></span>
            <span aria-hidden="true"><?= htmlspecialchars($disclaimer, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </footer>



    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <!-- Custom JS -->
    <script src="assets/user-js/script.js?v=2"></script>
    <script src="assets/js/place-markers.js?v=1"></script>
    <script src="user-map.js?v=24"></script>

    <script>
        function applyModalServiceBlur(shouldBlur) {
            document.querySelector('.mobile-back-button').style.display = shouldBlur ? 'block' : 'none';
            document.getElementById('modalHeroImage').classList.toggle('service-blurred', shouldBlur);

            for (const id of ['detailMenuContent', 'detailWebsites', 'detailPhotos']) {
                const content = document.getElementById(id);
                const section = content.closest('.services-section');
                content.classList.toggle('service-blurred', shouldBlur);
                content.inert = shouldBlur;
                section.querySelector('.blur-message')?.remove();
                if (shouldBlur) {
                    const message = document.createElement('p');
                    message.className = 'blur-message';
                    message.textContent = 'For further information, kindly refer to their official website.';
                    section.append(message);
                }
            }
        }

        window.onDisplayLocationModal = function(location) {
            applyModalServiceBlur(location.blur_services == 1);
        };
    </script>
</body>
</html>
