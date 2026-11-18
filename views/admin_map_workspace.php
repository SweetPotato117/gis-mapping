<!-- Map Container -->
        <section class="admin-map-workspace" aria-label="Location map">
            <div id="map"></div>
            <div class="admin-map-toolbar">
                <div class="floating-search-container">
                    <div class="floating-search-bar">
                        <i class="fas fa-search search-icon" aria-hidden="true"></i>
                        <input type="text" id="mapLocationSearch" placeholder="Search locations" aria-label="Search map locations" class="search-input">
                        <button type="button" class="search-clear" id="mapSearchClear" aria-label="Clear search">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="filter-buttons-container">
                    <button type="button" class="filter-btn" data-filter="view-all" id="viewAllToggle" aria-pressed="false">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                        View all locations
                    </button>
                </div>
            </div>
        </section>
