        <!-- Admin Panel -->
        <div class="admin-panel">
            <div class="panel-header">
                <h2>Add a location</h2>
                <p>Manage the details visitors see on the public map.</p>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <button type="button" class="tab-btn active" data-tab="overview" aria-pressed="true" aria-controls="overview">
                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    Overview
                </button>
                <button type="button" class="tab-btn" data-tab="services" aria-pressed="false" aria-controls="services">
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                    Services
                </button>
                <button type="button" class="tab-btn" data-tab="about" aria-pressed="false" aria-controls="about">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    About
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Overview Tab -->
                <div id="overview" class="tab-pane active">
                    <form id="locationForm">
                        <input type="hidden" id="placeId" name="id">

                        <section class="editor-card" aria-labelledby="listingHeading">
                            <h3 id="listingHeading"><i class="fa-regular fa-rectangle-list" aria-hidden="true"></i> Listing details</h3>
                            <div class="form-group">
                                <label for="locationName">Location name <span class="required">*</span></label>
                                <input type="text" id="locationName" name="name" placeholder="e.g., Mang Juan's Restaurant" required>
                            </div>
                            <div class="form-group">
                                <label for="locationType">Category <span class="required">*</span></label>
                                <select id="locationType" name="type" required>
                                    <option value="">Choose a category</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="hotels">Hotel</option>
                                    <option value="tourist_destination">Tourist attraction</option>
                                </select>
                            </div>
                            <?php if ($is_superadmin): ?>
                                <div class="form-group owner-picker">
                                    <label for="adminSearch">Store admin</label>
                                    <input type="search" id="adminSearch" placeholder="Search by username" autocomplete="off" aria-describedby="ownerHelp">
                                    <input type="hidden" name="admin" id="admin">
                                    <div id="searchResults" class="search-results" style="display: none;"></div>
                                    <p class="editor-hint" id="ownerHelp">Choose a store admin to load their existing places, or leave blank to keep ownership under your account.</p>
                                    <div class="owner-place-picker" id="ownerPlacesPanel" hidden>
                                        <label for="ownerPlaceSelect">Existing places</label>
                                        <select id="ownerPlaceSelect" disabled>
                                            <option value="">Choose a place to load</option>
                                        </select>
                                        <p class="editor-hint" id="ownerPlaceStatus" role="status" aria-live="polite"></p>
                                        <button type="button" class="btn btn-secondary" id="newOwnedPlaceButton">
                                            <i class="fa-solid fa-plus" aria-hidden="true"></i> Create a new place for this admin
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </section>

                        <section class="editor-card" aria-labelledby="positionHeading">
                            <h3 id="positionHeading"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Map position</h3>
                            <p class="editor-hint" id="mapPositionHelp">Tap the map to place a pin, drag it to adjust, or enter coordinates below.</p>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="latitude">Latitude <span class="required">*</span></label>
                                    <input type="number" step="any" min="-90" max="90" id="latitude" name="lat" placeholder="16.930544" aria-describedby="mapPositionHelp" required>
                                </div>
                                <div class="form-group">
                                    <label for="longitude">Longitude <span class="required">*</span></label>
                                    <input type="number" step="any" min="-180" max="180" id="longitude" name="lng" placeholder="121.739977" aria-describedby="mapPositionHelp" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="address">Street address</label>
                                <input type="text" id="address" name="address" placeholder="Street or barangay, Cauayan City">
                            </div>
                        </section>

                        <section class="editor-card" aria-labelledby="contactHeading">
                            <h3 id="contactHeading"><i class="fa-regular fa-address-card" aria-hidden="true"></i> Hours and contact</h3>
                            <div class="form-group">
                                <label for="openHours">Opening hours</label>
                                <input type="text" id="openHours" name="open_hours" placeholder="e.g., Mon–Sat, 8 AM–6 PM">
                            </div>
                            <div class="form-group">
                                <label for="contact">Phone number</label>
                                <input type="tel" id="contact" name="contact" placeholder="e.g., 0917 123 4567">
                            </div>
                        </section>
                    </form>
                </div>
                <!-- Services Tab -->
                <div id="services" class="tab-pane">
                    <?php if ($is_superadmin): ?>
                    <label class="editor-card service-visibility" for="blurServicesToggle">
                        <span>
                            <strong><i class="fa-solid fa-eye-slash" aria-hidden="true"></i> Blur services on the public map</strong>
                            <span class="editor-hint">Visitors will see a notice directing them to the official website.</span>
                        </span>
                        <input type="checkbox" id="blurServicesToggle" form="locationForm">
                    </label>
                    <?php endif; ?>

                    <div class="services-notice editor-card">
                        <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                        Choose a category in Overview to see the available service fields.
                    </div>

                    <?php foreach (['foodServices' => 'foodPhotos', 'hotelServices' => 'hotelPhotos', 'touristServices' => 'touristPhotos'] as $sectionId => $photoId): ?>
                        <div id="<?= $sectionId ?>" class="service-section" style="display:none;">
                            <section class="editor-card" aria-labelledby="<?= $sectionId ?>Links">
                                <h3 id="<?= $sectionId ?>Links"><i class="fa-solid fa-link" aria-hidden="true"></i> Websites and social media</h3>
                                <div data-website-list>
                                    <div class="website-item">
                                        <input type="url" class="website-input" name="websites[]" aria-label="Website or social media URL" placeholder="https://example.com">
                                        <button type="button" class="btn-remove-website" onclick="removeWebsite(this)" aria-label="Remove website">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-secondary add-website-btn">
                                    <i class="fas fa-plus" aria-hidden="true"></i> Add website
                                </button>
                            </section>

                            <?php if ($sectionId === 'foodServices'): ?>
                                <section class="editor-card" aria-labelledby="menuHeading">
                                    <h3 id="menuHeading"><i class="fa-solid fa-utensils" aria-hidden="true"></i> Menu items</h3>
                                    <div id="menuList">
                                        <div class="menu-item">
                                            <input type="text" placeholder="Item name" aria-label="Menu item name" class="menu-name" name="menu_name[]">
                                            <input type="number" step="0.01" placeholder="Price" aria-label="Menu item price" class="menu-price" name="menu_price[]">
                                            <button type="button" class="btn-remove" onclick="removeMenuItem(this)" aria-label="Remove menu item">
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-secondary" id="addMenuItem">
                                        <i class="fas fa-plus" aria-hidden="true"></i> Add menu item
                                    </button>
                                </section>
                            <?php endif; ?>

                            <section class="editor-card" aria-labelledby="<?= $photoId ?>Heading">
                                <h3 id="<?= $photoId ?>Heading"><label for="<?= $photoId ?>"><i class="fa-regular fa-images" aria-hidden="true"></i> Place photos</label></h3>
                                <p class="editor-hint">Choose one or more images. The first photo is used as the cover.</p>
                                <input class="editor-file-input" type="file" id="<?= $photoId ?>" accept="image/*" multiple>
                                <div id="<?= $photoId ?>Preview" class="photo-preview"></div>
                            </section>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- About Tab -->
                <div id="about" class="tab-pane">
                    <section class="editor-card" aria-labelledby="descriptionLabel">
                        <h3><label id="descriptionLabel" for="description"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> Public description <span class="required">*</span></label></h3>
                        <textarea id="description" name="description" form="locationForm" rows="5" placeholder="Describe what visitors can expect at this location." required></textarea>
                    </section>
                    <p class="editor-hint about-help">Select the amenities and house rules that apply to this location.</p>
                    <?php require __DIR__ . '/place_about_fields.php'; ?>
                </div>
            </div>

            <p id="locationSaveStatus" class="editor-status" role="status" aria-live="polite"></p>
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

