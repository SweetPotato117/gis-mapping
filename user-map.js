// Initialize map centered on Cauayan City, Isabela
const map = L.map('map', {
    center: [16.9305, 121.7400],
    zoom: 14,
    zoomControl: false,
    touchZoom: true,
    maxBoundsViscosity: 1.0
});

const streetTiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
    className: 'public-street-tiles',
    noWrap: true
});

const satelliteTiles = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Imagery: <a href="https://www.arcgis.com/home/item.html?id=10df2279f9684e4a9f6a7f08febac2a9">Esri</a>, Vantor, Earthstar Geographics, and the GIS User Community',
    maxNativeZoom: 19,
    maxZoom: 19,
    noWrap: true
}).addTo(map);

const satelliteToggle = document.getElementById('satelliteToggle');
satelliteToggle.addEventListener('click', () => {
    const showSatellite = !map.hasLayer(satelliteTiles);
    const nextLayer = showSatellite ? satelliteTiles : streetTiles;
    const previousLayer = showSatellite ? streetTiles : satelliteTiles;
    nextLayer.addTo(map);
    map.removeLayer(previousLayer);
    satelliteToggle.setAttribute('aria-pressed', String(showSatellite));
});

console.log('Cauayan City boundary loaded from local data');

let markers = [];
let currentMarker = null;
let locations = [];
let currentLocationId = null;
let finishPlaceSelection = null;

// Live tracking variables
let userLocationMarker = null;
let watchPositionId = null;
let isTrackingLocation = false;
let userCurrentPosition = null;
let navigationDestination = null;
let navigationRouteLayer = null;
let navigationRouteRequest = null;
let navigationRequestTimer = null;
let lastRouteRequestTime = -Infinity;
let lastRouteOrigin = null;
let navigationMode = 'drive';
let isNavigating = false;
let pendingDestination = null;
const routeProfiles = {
    drive: { server: 'car', profile: 'driving', minimumMovement: 55 },
    walk: { server: 'foot', profile: 'foot', minimumMovement: 25 }
};
const routingCredit = '<a href="https://routing.openstreetmap.de/about.html">FOSSGIS routing</a> · © <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> · <a href="https://www.openstreetmap.org/fixthemap">Fix the map</a>';

const getIconForType = placeMapMarkers.icon;

function formatType(type) {
    const typeMap = {
        'restaurant': 'Restaurant',
        'hotels': 'Hotel',
        'touristdestination': 'Tourist Destination',
        'cafe': 'Cafe',
        'carinderia': 'Carinderia',
        'malls': 'Shopping Mall'
    };
    return typeMap[type] || type;
}

const userLocationIcon = L.divIcon({
    className: 'public-location-dot',
    iconSize: [18, 18],
    iconAnchor: [9, 9],
    popupAnchor: [0, -12]
});

// Public map location markers
function addLocationMarker(location, index) {
    const marker = L.marker([parseFloat(location.lat), parseFloat(location.lng)], {
        icon: getIconForType(location.type),
        title: location.name
    }).addTo(map);

    placeMapMarkers.bindLabel(marker, location.name, location.type);

    markers[index] = { marker, type: location.type, location };
    marker.on('click', () => navigateToLocation(location, markers[index]));
}

async function fetchAndDisplayLocations() {
    try {
        const response = await fetch('controllers/user/get_user_map_data.php', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        if (!text || text.trim().length === 0) {
            console.error('Empty response from server');
            return [];
        }
        
        let json;
        try {
            json = JSON.parse(text);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            return [];
        }
        
        if (!json || !json.success) {
            console.error('Error fetching locations:', json?.error || 'Unknown error');
            return [];
        }
        
        return json.data;
    } catch (error) {
        console.error('Failed to fetch locations:', error);
        return [];
    }
}

async function loadLocations() {
    locations = await fetchAndDisplayLocations();
    if (Array.isArray(locations)) {
        locations.forEach((loc, index) => {
            addLocationMarker(loc, index);
        });
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;');
}

// Create navigation panel with simple show/hide functionality
const navPanel = document.createElement('div');
navPanel.className = 'navigation-panel';
navPanel.id = 'navigationPanel';
navPanel.innerHTML = `
    <button type="button" class="place-sheet-handle" aria-label="Resize directions"></button>
    <div class="nav-panel-header">
        <h4><i class="fas fa-route" aria-hidden="true"></i> <span id="navDestination">Directions</span></h4>
        <button class="nav-close-btn" onclick="stopNavigation()" aria-label="Stop navigation">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="nav-panel-content">
        <div class="nav-mode-picker" role="group" aria-label="Travel mode">
            <button type="button" data-mode="drive" aria-pressed="true"><i class="fa-solid fa-car" aria-hidden="true"></i> Drive</button>
            <button type="button" data-mode="walk" aria-pressed="false"><i class="fa-solid fa-person-walking" aria-hidden="true"></i> Walk</button>
        </div>
        <div class="nav-route-status">
            <span role="status"></span>
            <button type="button" class="nav-retry-btn" hidden>Retry</button>
        </div>
        <div class="nav-summary" hidden>
            <div class="nav-summary-item">
                <span class="nav-summary-value" id="navDistance">--</span>
                <span class="nav-summary-label">Distance</span>
            </div>
            <div class="nav-summary-item">
                <span class="nav-summary-value" id="navDuration">--</span>
                <span class="nav-summary-label">Time</span>
            </div>
            <button type="button" class="nav-steps-btn" aria-controls="navInstructions" aria-expanded="false">
                Steps <i class="fas fa-chevron-up" aria-hidden="true"></i>
            </button>
        </div>
        <div id="navInstructions"></div>
    </div>
    <div class="nav-panel-footer">
        <button class="nav-stop-btn" onclick="stopNavigation()">
            <i class="fas fa-stop-circle"></i> Stop Navigation
        </button>
    </div>
`;
document.body.appendChild(navPanel);
const navStepsButton = navPanel.querySelector('.nav-steps-btn');
const navModeButtons = navPanel.querySelectorAll('[data-mode]');
const navRetryButton = navPanel.querySelector('.nav-retry-btn');
navModeButtons.forEach(button => button.addEventListener('click', () => {
    if (navigationMode === button.dataset.mode) return;
    navigationMode = button.dataset.mode;
    navModeButtons.forEach(option => option.setAttribute('aria-pressed', String(option === button)));
    clearNavigationRoute();
    setRouteStatus('Finding route…');
    requestNavigationRoute();
}));
navRetryButton.addEventListener('click', () => {
    setRouteStatus('Finding route…');
    requestNavigationRoute();
});

// Toggle location tracking
function toggleLocationTracking() {
    if (isTrackingLocation) {
        stopLocationTracking();
    } else {
        startLocationTracking();
    }
}

// Start tracking user location
function startLocationTracking() {
    if (!navigator.geolocation) {
        pendingDestination = null;
        alert('Geolocation is not supported by your browser');
        return;
    }

    const options = {
        enableHighAccuracy: true,
        timeout: 30000, // Increased from 5000 to 30000 (30 seconds)
        maximumAge: 0
    };

    watchPositionId = navigator.geolocation.watchPosition(
        onLocationUpdate,
        onLocationError,
        options
    );

    isTrackingLocation = true;
    
    console.log('Location tracking started...');
}

function stopLocationTracking() {
    if (watchPositionId !== null) {
        navigator.geolocation.clearWatch(watchPositionId);
        watchPositionId = null;
    }

    if (userLocationMarker) {
        map.removeLayer(userLocationMarker);
        userLocationMarker = null;
    }

    isTrackingLocation = false;
    userCurrentPosition = null;
    pendingDestination = null;
    
    document.getElementById('recenterBtn').classList.remove('active');
    
    // FIXED: Only update GPS indicator if it exists
    const gpsIndicator = document.getElementById('gpsIndicator');
    if (gpsIndicator) {
        gpsIndicator.classList.remove('active');
    }
    
    // Stop navigation if active
    if (isNavigating) {
        stopNavigation();
    }
    
    console.log('Location tracking stopped');
}


// Handle location updates WITHOUT accuracy circle
function onLocationUpdate(position) {
    userCurrentPosition = position;
    document.getElementById('recenterBtn').classList.add('active');
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    const accuracy = position.coords.accuracy;

    console.log('Location updated:', lat, lng, 'Accuracy:', accuracy);

    // Update GPS accuracy indicator
    updateGPSIndicator(accuracy);

    // Update or create user location marker (NO ACCURACY CIRCLE)
    const firstFix = !userLocationMarker;
    if (firstFix) {
        userLocationMarker = L.marker([lat, lng], {
            icon: userLocationIcon,
            zIndexOffset: 1000
        }).addTo(map);

        // Add popup to marker
        userLocationMarker.bindPopup(`
            <div style="text-align: center;">
                <strong>Your Location</strong><br>
                Accuracy: ${accuracy.toFixed(0)} meters
            </div>
        `);
    } else {
        userLocationMarker.setLatLng([lat, lng]);
        userLocationMarker.setPopupContent(`
            <div style="text-align: center;">
                <strong>Your Location</strong><br>
                Accuracy: ${accuracy.toFixed(0)} meters
            </div>
        `);
    }

    if (pendingDestination) {
        const { lat, lng, name } = pendingDestination;
        pendingDestination = null;
        startNavigation(lat, lng, name);
        return;
    }

    if (firstFix) centerMapOnUser();

    // Update navigation if active
    if (isNavigating) {
        updateNavigationRoute();
    }
}

// Handle location errors
function onLocationError(error) {
    console.error('Location error:', error);
    
    let message = 'Unable to get your location';
    switch(error.code) {
        case error.PERMISSION_DENIED:
            message = 'Location access denied. Please enable location services.';
            break;
        case error.POSITION_UNAVAILABLE:
            message = 'Location information unavailable.';
            break;
        case error.TIMEOUT:
            message = 'Location request timed out.';
            break;
    }
    
    alert(message);
    stopLocationTracking();
}

// Update GPS indicator
function updateGPSIndicator(accuracy) {
    const indicator = document.getElementById('gpsIndicator');
    
    // FIXED: Only update if element exists
    if (!indicator) return;
    
    indicator.classList.add('active');
    
    let statusClass, statusText;
    if (accuracy < 10) {
        statusClass = 'good';
        statusText = 'Excellent';
    } else if (accuracy < 50) {
        statusClass = 'medium';
        statusText = 'Good';
    } else {
        statusClass = 'poor';
        statusText = 'Poor';
    }
    
    indicator.className = `gps-indicator active ${statusClass}`;
    indicator.innerHTML = `<i class="fas fa-satellite-dish"></i> GPS: ${statusText} (${Math.round(accuracy)}m)`;
}
// Start navigation to destination
function startNavigation(destLat, destLng, destName) {
    if (!isTrackingLocation || !userCurrentPosition) {
        pendingDestination = { lat: destLat, lng: destLng, name: destName };
        if (!isTrackingLocation) startLocationTracking();
        if (isTrackingLocation) showToast('Waiting for your location…');
        return;
    }

    if (isNavigating) stopNavigation();

    navigationDestination = L.latLng(destLat, destLng);
    isNavigating = true;
    map.attributionControl.addAttribution(routingCredit);
    navPanel.querySelector('#navDestination').textContent = destName || 'Directions';
    setRouteStatus('Finding route…');
    setNavigationSheetLevel('preview');
    navPanel.querySelector('.nav-panel-content').scrollTop = 0;
    navPanel.classList.add('active');
    centerMapOnUser({ zoomIn: true });
    requestNavigationRoute();
}

function setRouteStatus(message, canRetry = false) {
    const status = navPanel.querySelector('.nav-route-status');
    status.hidden = !message;
    status.querySelector('[role="status"]').textContent = message;
    navRetryButton.hidden = !canRetry;
    navPanel.querySelector('.nav-summary').hidden = Boolean(message);
    if (message) document.getElementById('navInstructions').replaceChildren();
}

function clearNavigationRoute() {
    clearTimeout(navigationRequestTimer);
    navigationRequestTimer = null;
    const request = navigationRouteRequest;
    navigationRouteRequest = null;
    request?.xhr?.abort();
    if (navigationRouteLayer) map.removeLayer(navigationRouteLayer);
    navigationRouteLayer = null;
    lastRouteOrigin = null;
}

function requestNavigationRoute() {
    if (!isNavigating || !userCurrentPosition || navigationRouteRequest || navigationRequestTimer !== null) return;

    const wait = 1100 - (performance.now() - lastRouteRequestTime);
    if (wait > 0) {
        navigationRequestTimer = setTimeout(() => {
            navigationRequestTimer = null;
            requestNavigationRoute();
        }, wait);
        return;
    }

    const { latitude, longitude } = userCurrentPosition.coords;
    const origin = L.latLng(latitude, longitude);
    const mode = navigationMode;
    const { server, profile } = routeProfiles[mode];
    const router = L.Routing.osrmv1({
        serviceUrl: `https://routing.openstreetmap.de/routed-${server}/route/v1`,
        profile,
        useHints: false
    });
    const request = {};
    navigationRouteRequest = request;
    lastRouteOrigin = origin;
    lastRouteRequestTime = performance.now();

    request.xhr = router.route([
        L.Routing.waypoint(origin),
        L.Routing.waypoint(navigationDestination)
    ], (error, routes) => {
        if (navigationRouteRequest !== request) return;
        navigationRouteRequest = null;
        const route = routes?.[0];
        if (error || !route?.coordinates?.length) {
            if (!navigationRouteLayer) setRouteStatus('Route unavailable.', true);
            return;
        }
        if (route.summary.totalDistance < 20 && origin.distanceTo(navigationDestination) < 30) {
            arrivedAtDestination();
            return;
        }

        if (navigationRouteLayer) map.removeLayer(navigationRouteLayer);
        navigationRouteLayer = L.layerGroup([
            L.polyline(route.coordinates, { color: '#fff', opacity: 0.95, weight: 9, interactive: false }),
            L.polyline(route.coordinates, {
                color: mode === 'walk' ? '#087b8c' : '#235fc8',
                dashArray: mode === 'walk' ? '1 10' : null,
                weight: 5,
                interactive: false
            }),
            L.marker(navigationDestination)
        ]).addTo(map);
        displayNavigationInfo(route);
        setRouteStatus('');
    }, null, { simplifyGeometry: false });
}

function updateNavigationRoute() {
    if (!lastRouteOrigin || performance.now() - lastRouteRequestTime < 12000) return;
    const { latitude, longitude, accuracy } = userCurrentPosition.coords;
    const origin = L.latLng(latitude, longitude);
    const minimumMovement = Math.max(routeProfiles[navigationMode].minimumMovement, accuracy || 0);
    if (lastRouteOrigin.distanceTo(origin) >= minimumMovement) requestNavigationRoute();
}

// Display navigation information
function displayNavigationInfo(route) {
    const distance = route.summary.totalDistance;
    const duration = route.summary.totalTime;

    // Format distance
    let distanceText;
    if (distance >= 1000) {
        distanceText = `${(distance / 1000).toFixed(1)} km`;
    } else {
        distanceText = `${Math.round(distance)} m`;
    }

    // Format duration
    const totalMinutes = Math.ceil(duration / 60);
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    let durationText;
    if (hours > 0) {
        durationText = `${hours}h ${minutes}m`;
    } else {
        durationText = `${minutes} min`;
    }

    // Update summary
    document.getElementById('navDistance').textContent = distanceText;
    document.getElementById('navDuration').textContent = durationText;

    // Display instructions
    const instructionsContainer = document.getElementById('navInstructions');
    instructionsContainer.innerHTML = '';

    for (const instruction of route.instructions) {
        const instDiv = document.createElement('div');
        instDiv.className = 'nav-instruction';

        const turn = (instruction.modifier || instruction.type || '').toLowerCase();
        const icon = turn.includes('right') ? 'fa-arrow-right' : turn.includes('left') ? 'fa-arrow-left' : 'fa-arrow-up';

        instDiv.innerHTML = `
            <div class="nav-instruction-icon">
                <i class="fas ${icon}" aria-hidden="true"></i>
            </div>
            <div class="nav-instruction-text">
                <strong></strong>
                <span>${instruction.distance >= 1000 ? 
                    `${(instruction.distance / 1000).toFixed(1)} km` : 
                    `${Math.round(instruction.distance)} m`}</span>
            </div>
        `;
        instDiv.querySelector('strong').textContent = instruction.text;

        instructionsContainer.appendChild(instDiv);
    }
}

// Stop navigation
function stopNavigation() {
    isNavigating = false;

    clearNavigationRoute();
    navigationDestination = null;
    map.attributionControl.removeAttribution(routingCredit);

    document.getElementById('navigationPanel').classList.remove('active');
    console.log('Navigation stopped');
}

// Arrived at destination
function arrivedAtDestination() {
    alert('You have arrived at your destination!');
    stopNavigation();
}


// SEARCH FUNCTIONALITY
const searchInput = document.getElementById('mapLocationSearch');
const searchClearBtn = document.getElementById('mapSearchClear');
let suggestionsList = null;
let currentFocus = -1;
let searchTimeout = null;
let searchController = null;

if (searchInput) {
    const searchContainer = searchInput.closest('.floating-search-bar') || searchInput.parentElement;
    const outerContainer = document.querySelector('.floating-search-container');

    suggestionsList = document.createElement('div');
    suggestionsList.className = 'autocomplete-suggestions';
    suggestionsList.id = 'autocomplete-list';

    if (outerContainer) {
        outerContainer.style.position = 'relative';
        outerContainer.appendChild(suggestionsList);
    } else {
        searchContainer.style.position = 'relative';
        searchContainer.appendChild(suggestionsList);
    }

    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.trim();

        if (searchClearBtn) {
            searchClearBtn.style.display = searchTerm.length > 0 ? 'block' : 'none';
        }

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        if (searchController) {
            searchController.abort();
            searchController = null;
        }

        if (searchTerm.length < 2) {
            closeSuggestions();
            return;
        }

        suggestionsList.innerHTML = `
            <div class="search-loading">
                <i class="fas fa-spinner"></i>
                <div>Searching...</div>
            </div>
        `;

        searchTimeout = setTimeout(() => {
            searchTimeout = null;
            performSearch(searchTerm);
        }, 300);
    });

    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', () => {
            searchInput.value = '';
            closeSuggestions();
            searchClearBtn.style.display = 'none';
            searchInput.focus();
            filterMarkers('all');
        });
    }

    searchInput.addEventListener('keydown', (e) => {
        const items = suggestionsList.getElementsByClassName('autocomplete-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus++;
            addActive(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus--;
            addActive(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            (items[currentFocus] || items[0])?.click();
        } else if (e.key === 'Escape') {
            closeSuggestions();
        }
    });

    document.addEventListener('click', (e) => {
        const containers = [outerContainer, searchContainer].filter(c => c);
        const clickedInside = containers.some(container => container && container.contains(e.target));
        if (!clickedInside) {
            closeSuggestions();
        }
    });
}

async function performSearch(searchTerm) {
    const controller = new AbortController();
    searchController = controller;

    try {
        const response = await fetch(
            `controllers/user/search_locations.php?term=${encodeURIComponent(searchTerm)}`,
            { signal: controller.signal }
        );
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Search failed');
        }

        await locationsReady;
        if (!controller.signal.aborted) displaySearchResults(data.data, searchTerm);
    } catch (error) {
        if (error.name === 'AbortError') return;
        console.error('Search error:', error);
        suggestionsList.innerHTML = `
            <div class="no-results">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="no-results-text">Error performing search</div>
            </div>
        `;
    } finally {
        if (searchController === controller) {
            searchController = null;
        }
    }
}

function displaySearchResults(results, searchTerm) {
    suggestionsList.innerHTML = '';
    currentFocus = -1;

    if (results.length === 0) {
        suggestionsList.innerHTML = `
            <div class="no-results">
                <i class="fas fa-search"></i>
                <div class="no-results-text"></div>
            </div>
        `;
        suggestionsList.querySelector('.no-results-text').textContent = `No results found for “${searchTerm}”`;
        return;
    }

    const header = document.createElement('div');
    header.className = 'search-results-header';
    const hasTagMatches = results.some(location => location.matched_tags?.length);
    header.textContent = hasTagMatches ? 'Recommended places' : `${results.length} ${results.length === 1 ? 'result' : 'results'}`;
    suggestionsList.appendChild(header);

    results.forEach((location) => {
        const resultItem = document.createElement('div');
        resultItem.className = 'autocomplete-item';

        const iconClass = getIconClass(location.type);

        resultItem.innerHTML = `
            <div class="result-item-content">
                <div class="result-left">
                    <div class="result-icon">
                        <i class="${iconClass}" aria-hidden="true"></i>
                    </div>
                    <div class="result-details">
                        <div class="result-name"></div>
                        <div class="result-meta">
                            <span class="result-type"></span>
                            ${location.address ? '<span class="result-separator">•</span> <span class="result-address"></span>' : ''}
                        </div>
                        ${location.matched_menu ? '<div class="result-menu-match"><i class="fas fa-utensils" aria-hidden="true"></i><span>Has <strong></strong></span></div>' : ''}
                    </div>
                </div>
                <div class="result-actions">
                    <div class="directions-icon">
                        <i class="fas fa-directions"></i>
                    </div>
                </div>
            </div>
        `;
        appendSearchHighlight(resultItem.querySelector('.result-name'), location.name, searchTerm);
        resultItem.querySelector('.result-type').textContent = formatType(location.type);
        if (location.type) resultItem.querySelector('.result-icon').classList.add(location.type);
        if (location.address) resultItem.querySelector('.result-address').textContent = location.address;
        if (location.matched_menu) resultItem.querySelector('.result-menu-match strong').textContent = location.matched_menu;
        if (location.matched_tags?.length) {
            const tags = document.createElement('div');
            tags.className = 'result-tags';
            for (const { label, icon } of location.matched_tags) {
                const tag = document.createElement('span');
                tag.className = 'result-tag';
                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                const use = document.createElementNS(svg.namespaceURI, 'use');
                svg.setAttribute('aria-hidden', 'true');
                use.setAttribute('href', `assets/icons/place-details.svg?v=3#${icon}`);
                svg.append(use);
                tag.append(svg, label);
                tags.append(tag);
            }
            resultItem.querySelector('.result-details').append(tags);
        }

        resultItem.addEventListener('click', () => {
            navigateToLocation(location);
            searchInput.value = location.name;
            closeSuggestions();
        });

        suggestionsList.appendChild(resultItem);
    });
}

function cancelPlaceSelection() {
    if (!finishPlaceSelection) return;
    map.off('moveend', finishPlaceSelection);
    finishPlaceSelection = null;
}

map.on('dragstart', cancelPlaceSelection);

function navigateToLocation(location, entry = markers.find(item => String(item?.location.id) === String(location.id))) {
    cancelPlaceSelection();
    map.closePopup();
    map.stop();

    const selectedPlace = entry?.location ?? location;
    const point = entry?.marker.getLatLng() ?? L.latLng(Number(selectedPlace.lat), Number(selectedPlace.lng));
    const zoom = Math.min(map.getMaxZoom(), Math.max(18, map.getZoom()));
    let center = point;
    const mobile = window.matchMedia('(max-width: 767px)').matches;
    if (mobile) {
        // Keep the pin centered in the map area above the preview sheet.
        const offset = Math.min(222, map.getSize().y * 0.4) / 2;
        center = map.unproject(map.project(point, zoom).add([0, offset]), zoom);
    }

    const trigger = document.activeElement;
    const openDetails = () => showLocationDetails(selectedPlace, mobile ? 'preview' : 'half', trigger);
    if (map.getZoom() === zoom && map.getCenter().equals(center)) {
        openDetails();
        return;
    }

    finishPlaceSelection = () => {
        cancelPlaceSelection();
        if (map.getZoom() === zoom && map.project(map.getCenter(), zoom).distanceTo(map.project(center, zoom)) <= 2) {
            openDetails();
        }
    };
    map.on('moveend', finishPlaceSelection);
    map.flyTo(center, zoom, { duration: 2 });
}


function appendSearchHighlight(element, text, searchTerm) {
    const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
    (text || '').split(regex).forEach((part, index) => {
        if (index % 2 === 0) {
            element.append(part);
            return;
        }
        const match = document.createElement('strong');
        match.textContent = part;
        element.append(match);
    });
}

function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function getIconClass(type) {
    const icons = {
        'restaurant': 'fas fa-utensils',
        'hotels': 'fas fa-hotel',
        'touristdestination': 'fas fa-camera',
        'cafe': 'fas fa-coffee',
        'carinderia': 'fas fa-hamburger',
        'malls': 'fas fa-shopping-bag'
    };
    return icons[type] || 'fas fa-map-marker-alt';
}

function addActive(items) {
    if (!items || items.length === 0) return;

    removeActive(items);

    if (currentFocus >= items.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = items.length - 1;

    items[currentFocus].classList.add('autocomplete-active');
    items[currentFocus].scrollIntoView({block: 'nearest', behavior: 'smooth'});
}

function removeActive(items) {
    for (let i = 0; i < items.length; i++) {
        items[i].classList.remove('autocomplete-active');
    }
}

function closeSuggestions() {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
        searchTimeout = null;
    }
    if (searchController) {
        searchController.abort();
        searchController = null;
    }
    if (suggestionsList) {
        suggestionsList.innerHTML = '';
    }
    currentFocus = -1;
}

const locationsReady = loadLocations();
startLocationTracking(); // Auto-start location tracking on page load

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const targetTab = btn.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        const pane = document.getElementById(targetTab);
        if (pane) pane.classList.add('active');
    });
});

// Location type change handler
document.getElementById('locationType')?.addEventListener('change', (e) => {
    const type = e.target.value;
    document.querySelectorAll('.service-section').forEach(s => s.style.display = 'none');

    const notice = document.querySelector('.services-notice');

    if (type === 'restaurant') {
        const foodServices = document.getElementById('foodServices');
        if (foodServices) foodServices.style.display = 'block';
        if (notice) notice.style.display = 'none';
    } else if (type === 'hotels') {
        const hotelServices = document.getElementById('hotelServices');
        if (hotelServices) hotelServices.style.display = 'block';
        if (notice) notice.style.display = 'none';
    } else if (type === 'touristdestination') {
        const touristServices = document.getElementById('touristServices');
        if (touristServices) touristServices.style.display = 'block';
        if (notice) notice.style.display = 'none';
    } else {
        if (notice) notice.style.display = 'block';
    }

    if (currentMarker) {
        const newIcon = getIconForType(type);
        currentMarker.setIcon(newIcon);
    }
});

// Map click
map.on('click', async (e) => {
    cancelPlaceSelection();
    const lat = e.latlng.lat.toFixed(8);
    const lng = e.latlng.lng.toFixed(8);

    const latEl = document.getElementById('latitude');
    const lngEl = document.getElementById('longitude');

    if (latEl) latEl.value = lat;
    if (lngEl) lngEl.value = lng;

    if (currentMarker) {
        map.removeLayer(currentMarker);
    }

    const selectedType = document.getElementById('locationType')?.value || 'default';
    currentMarker = L.marker([lat, lng], {
        draggable: true,
        icon: getIconForType(selectedType)
    }).addTo(map);

    currentMarker.on('dragend', async (ev) => {
        const pos = ev.target.getLatLng();
        if (latEl) latEl.value = pos.lat.toFixed(8);
        if (lngEl) lngEl.value = pos.lng.toFixed(8);

        if (typeof reverseGeocode === 'function') {
            await reverseGeocode(pos.lat, pos.lng);
        }
    });
});

// Detail tabs
document.querySelectorAll('.detail-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const target = tab.dataset.detail;
        document.querySelectorAll('.detail-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        document.querySelectorAll('.detail-pane').forEach(p => p.classList.remove('active'));
        const pane = document.getElementById(`detail${target.charAt(0).toUpperCase()}${target.slice(1)}`);
        if (pane) pane.classList.add('active');
    });
});

window.GIS = {
    locations: locations,
    markers: markers,
    map: map,
    getCurrentLocationIndex: () => currentLocationId,
    setCurrentLocationIndex: (i) => { currentLocationId = i; },
    getCurrentMarker: () => currentMarker,
    setCurrentMarker: (m) => { currentMarker = m; },
    addLocationMarker: addLocationMarker,
    clearForm: (typeof clearForm !== 'undefined') ? clearForm : null
};

window.getIconForType = getIconForType;
window.startNavigation = startNavigation;
window.stopNavigation = stopNavigation;

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');

            filterButtons.forEach(btn => {
                const selected = btn === this;
                btn.classList.toggle('active', selected);
                btn.setAttribute('aria-pressed', String(selected));
            });

            filterMarkers(filter);
        });
    });
});

function filterMarkers(type) {
  markers.forEach(markerObj => {
    if (!markerObj || !markerObj.marker || !markerObj.location) return;
    
    const location = markerObj.location;
    
    // Normalize both the filter type and location type for comparison
    const normalizedType = location.type.toLowerCase().replace(/[-_\s]/g, '');
    const normalizedFilter = type.toLowerCase().replace(/[-_\s]/g, '');
    
    // Show marker if filter is 'all' or normalized types match
    if (type === 'all' || normalizedType === normalizedFilter) {
      if (!map.hasLayer(markerObj.marker)) {
        markerObj.marker.addTo(map);
      }
    } else {
      if (map.hasLayer(markerObj.marker)) {
        map.removeLayer(markerObj.marker);
      }
    }
  });
}

// Update map container class based on zoom level
let currentZoomClass = null;

function updateZoomClass() {
    const currentZoom = map.getZoom();
    const mapContainer = map.getContainer();

    if (currentZoomClass) mapContainer.classList.remove(currentZoomClass);

    currentZoomClass = `zoom-level-${currentZoom}`;
    mapContainer.classList.add(currentZoomClass);
}

// Initialize zoom class
updateZoomClass();

// Update on zoom
map.on('zoomend', updateZoomClass);

// Optional: Show a message when zoomed out too far
let zoomNotification = null;

map.on('zoomend', function() {
    const currentZoom = map.getZoom();

    // Show notification when markers are hidden
    if (currentZoom < 11) {
        if (!zoomNotification) {
            zoomNotification = L.control({position: 'bottomleft'});
            zoomNotification.onAdd = function() {
                const div = L.DomUtil.create('div', 'zoom-notification');
                div.innerHTML = '<i class="fas fa-search-plus"></i> Zoom in to see locations';
                return div;
            };
            zoomNotification.addTo(map);
        }
    } else {
        if (zoomNotification) {
            map.removeControl(zoomNotification);
            zoomNotification = null;
        }
    }
});

// Make switchTab globally accessible
window.switchTab = function(tabName) {
    const sheet = document.querySelector('#locationModal .modal-content');
    if (sheet.dataset.sheetLevel === 'preview' && sheet.getClientRects().length) {
        setPlaceSheetLevel('half');
    }

    // Update tab buttons
    document.querySelectorAll('.google-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    const activeTab = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }

    // Update tab content
    document.querySelectorAll('.google-tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    const activePane = document.getElementById(`tab-${tabName}`);
    if (activePane) {
        activePane.classList.add('active');
    }
};

// Make other modal functions globally accessible
let detailsTrigger = null;
function setupResizableSheet(placeSheet, label, { scrollContainer = placeSheet, onLevelChange } = {}) {
    const placeSheetHandle = placeSheet.querySelector('.place-sheet-handle');
    const placeSheetLevels = ['preview', 'half', 'full'];
    let placeSheetDrag = null;
    let ignoreSheetClick = false;

    function setLevel(level) {
        placeSheet.dataset.sheetLevel = level;
        placeSheetHandle.setAttribute('aria-label',
            `Resize ${label}. Current view: ${level}. Use the up and down arrow keys to change the height.`);
        if (level === 'preview') scrollContainer.scrollTop = 0;
        onLevelChange?.(level);
    }

    function movePlaceSheetLevel(direction) {
        const current = placeSheetLevels.indexOf(placeSheet.dataset.sheetLevel);
        const next = Math.max(0, Math.min(placeSheetLevels.length - 1, current + direction));
        setLevel(placeSheetLevels[next]);
    }

    placeSheetHandle.addEventListener('click', event => {
        if (ignoreSheetClick && event.detail > 0) return;
        const current = placeSheetLevels.indexOf(placeSheet.dataset.sheetLevel);
        setLevel(placeSheetLevels[(current + 1) % placeSheetLevels.length]);
    });

    placeSheetHandle.addEventListener('keydown', event => {
        if (event.key !== 'ArrowUp' && event.key !== 'ArrowDown') return;
        event.preventDefault();
        movePlaceSheetLevel(event.key === 'ArrowUp' ? 1 : -1);
    });

    placeSheetHandle.addEventListener('pointerdown', event => {
        if (!event.isPrimary || event.button !== 0) return;
        ignoreSheetClick = false;
        const viewport = document.documentElement.clientHeight;
        const style = getComputedStyle(placeSheet);
        const safeTop = parseFloat(style.getPropertyValue('--safe-top')) || 0;
        const previewLimit = parseFloat(style.getPropertyValue('--sheet-preview-limit')) || 222;
        placeSheetDrag = {
            pointerId: event.pointerId,
            startY: event.clientY,
            startHeight: placeSheet.getBoundingClientRect().height,
            level: placeSheetLevels.indexOf(placeSheet.dataset.sheetLevel),
            heights: [Math.min(previewLimit, viewport * .4), viewport * .56, Math.min(viewport * .945, viewport - safeTop - 40)]
        };
        placeSheetHandle.setPointerCapture(event.pointerId);
    });

    function dragPlaceSheet(event) {
        const drag = placeSheetDrag;
        if (!drag || drag.pointerId !== event.pointerId) return;
        const delta = drag.startY - event.clientY;
        if (Math.abs(delta) < 6 && !ignoreSheetClick) return;
        ignoreSheetClick = true;
        const height = Math.max(drag.heights[0], Math.min(drag.heights[2], drag.startHeight + delta));
        placeSheet.classList.add('is-resizing');
        placeSheet.style.setProperty('--place-sheet-drag-size', `${height}px`);
    }

    function finishPlaceSheetDrag(event) {
        const drag = placeSheetDrag;
        if (!drag || drag.pointerId !== event.pointerId) return;
        placeSheetDrag = null;
        placeSheet.classList.remove('is-resizing');
        placeSheet.style.removeProperty('--place-sheet-drag-size');
        if (event.type !== 'pointerup' || !ignoreSheetClick) return;

        const delta = drag.startY - event.clientY;
        const height = drag.startHeight + delta;
        let closest = drag.heights.reduce((best, stop, index) =>
            Math.abs(height - stop) < Math.abs(height - drag.heights[best]) ? index : best, drag.level);
        if (closest === drag.level && Math.abs(delta) >= 28) {
            closest = Math.max(0, Math.min(placeSheetLevels.length - 1, closest + Math.sign(delta)));
        }
        setLevel(placeSheetLevels[closest]);
    }

    placeSheetHandle.addEventListener('pointermove', dragPlaceSheet);
    placeSheetHandle.addEventListener('pointerup', finishPlaceSheetDrag);
    placeSheetHandle.addEventListener('pointercancel', finishPlaceSheetDrag);
    placeSheetHandle.addEventListener('lostpointercapture', finishPlaceSheetDrag);

    return setLevel;
}

const setPlaceSheetLevel = setupResizableSheet(
    document.querySelector('#locationModal .modal-content'), 'place details'
);
const setNavigationSheetLevel = setupResizableSheet(navPanel, 'directions', {
    scrollContainer: navPanel.querySelector('.nav-panel-content'),
    onLevelChange: level => navStepsButton.setAttribute('aria-expanded', String(level !== 'preview'))
});
navStepsButton.addEventListener('click', () => {
    setNavigationSheetLevel(navPanel.dataset.sheetLevel === 'preview' ? 'half' : 'preview');
});

window.closeLocationModal = function() {
    const modal = document.getElementById('locationModal');
    if (modal) modal.style.display = 'none';

    // Clean up any blur messages and remove blur class from modal sections
    const modalContent = document.querySelector('.modal-content.google-style') || document.querySelector('.modal-content');
    if (modalContent) {
        modalContent.querySelectorAll('.blur-message').forEach(m => m.remove());
        // remove blur from any elements inside the modal
        modalContent.querySelectorAll('.service-blurred').forEach(el => el.classList.remove('service-blurred'));
    }
    if (detailsTrigger instanceof HTMLElement && detailsTrigger.isConnected) {
        detailsTrigger.focus({ preventScroll: true });
    }
    detailsTrigger = null;
};

document.getElementById('locationModal').addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        event.preventDefault();
        closeLocationModal();
    }
    if (event.key !== 'Tab') return;
    const controls = [...event.currentTarget.querySelectorAll('button, a[href]')]
        .filter(control => control.getClientRects().length && !control.closest('.service-blurred'));
    const first = controls[0];
    const last = controls.at(-1);
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

window.copyAddress = function() {
    const address = document.getElementById('detailAddress').textContent;
    navigator.clipboard.writeText(address).then(() => {
        showToast('Address copied to clipboard');
    });
};

window.copyCoordinates = function() {
    const coords = document.getElementById('detailCoords').textContent;
    navigator.clipboard.writeText(coords).then(() => {
        showToast('Coordinates copied to clipboard');
    });
};

window.callLocation = function() {
    const phone = document.getElementById('detailContact').textContent;
    if (phone && phone !== '-') {
        window.location.href = `tel:${phone}`;
    }
};

window.shareLocation = function() {
    const name = document.getElementById('modalTitle').textContent;
    const address = document.getElementById('detailAddress').textContent;
    const coords = document.getElementById('detailCoords').textContent;

    if (navigator.share) {
        navigator.share({
            title: name,
            text: `${name}\n${address}\n${coords}`,
            url: window.location.href
        });
    } else {
        showToast('Sharing not supported on this device');
    }
};

window.saveLocation = function() {
    showToast('Location saved');
};

window.startNavigationFromModal = function() {
    const coords = document.getElementById('detailCoords').textContent;
    const name = document.getElementById('modalTitle').textContent;

    if (coords && coords !== '-') {
        const [lat, lng] = coords.split(',').map(c => parseFloat(c.trim()));
        closeLocationModal();
        startNavigation(lat, lng, name);
    }
};

const placePhotoViewer = document.getElementById('placePhotoViewer');
const placeViewerImage = document.getElementById('placeViewerImage');

window.openImageViewer = function(imageUrl, description = 'Place photo') {
    placeViewerImage.alt = description;
    placeViewerImage.src = imageUrl;
    if (!placePhotoViewer.open) placePhotoViewer.showModal();
};

placePhotoViewer.addEventListener('click', event => {
    if (event.target === placePhotoViewer) placePhotoViewer.close();
});

placePhotoViewer.addEventListener('close', () => {
    if (!placePhotoViewer.open) placeViewerImage.removeAttribute('src');
});

// Toast notification helper
function showToast(message) {
    // Remove existing toast if any
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 100);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 2000);
}

window.viewLocationDetails = function(index) {
    showLocationDetails(locations[index]);
};

function showLocationDetails(location, level = 'half', trigger = document.activeElement) {
    if (!location) {
        console.error('Location not found');
        return;
    }

    console.log('Opening location details:', location);

    // Set hero image - Use first highlight/photo as header
    const heroImage = document.getElementById('modalHeroImage');
    if (location.highlights && location.highlights.length > 0) {
        heroImage.style.backgroundImage = `url(${location.highlights[0].image_url})`;
    } else {
        // Use green gradient as fallback
        heroImage.style.removeProperty('background-image');
    }

    // Set location details
    document.getElementById('modalTitle').textContent = location.name || 'Unknown Location';
    document.getElementById('locationCategory').textContent = formatType(location.type);
    document.getElementById('detailAddress').textContent = location.address || '-';
    document.getElementById('detailHours').textContent = location.open_hours || '-';
    document.getElementById('detailContact').textContent = location.contact || '-';
    document.getElementById('detailCoords').textContent = `${location.lat}, ${location.lng}`;
    const aboutPane = document.getElementById('tab-about');
    let hasAboutOptions = false;
    for (const section of aboutPane.querySelectorAll('[data-about-group]')) {
        const selected = location.place_about?.[section.dataset.aboutGroup] ?? [];
        section.hidden = selected.length === 0;
        hasAboutOptions ||= !section.hidden;
        for (const item of section.querySelectorAll('[data-about-option]')) {
            item.hidden = !selected.includes(item.dataset.aboutOption);
        }
    }
    const description = location.description?.trim();
    document.getElementById('detailDescription').textContent = description || 'No information';
    document.getElementById('placeDescriptionSection').hidden = !description && hasAboutOptions;

    // Hide sections if empty
    document.getElementById('hoursSection').style.display = location.open_hours ? 'flex' : 'none';
    document.getElementById('phoneSection').style.display = location.contact ? 'flex' : 'none';

    // Populate menu - ONLY for restaurants
    const menuSection = document.getElementById('menuSection');
    const menuContent = document.getElementById('detailMenuContent');
    menuContent.innerHTML = '';

    if (location.type === 'restaurant' && location.menu && location.menu.length > 0) {
        location.menu.forEach(item => {
            const menuItem = document.createElement('div');
            menuItem.className = 'menu-item';
            menuItem.innerHTML = `
                <div>
                    <div class="menu-item-name">${item.name}</div>
                    <div class="menu-item-type">${item.type || 'Food'}</div>
                </div>
                <div class="menu-item-price">₱${parseFloat(item.price).toFixed(2)}</div>
            `;
            menuContent.appendChild(menuItem);
        });
        menuSection.style.display = 'block';
    } else {
        menuSection.style.display = 'none';
    }

    // Populate photos
    const photosContent = document.getElementById('detailPhotos');
    photosContent.innerHTML = '';

    if (location.highlights && location.highlights.length > 0) {
        location.highlights.forEach((photo, index) => {
            const description = `photo ${index + 1} of ${location.name}`;
            const photoItem = document.createElement('button');
            photoItem.type = 'button';
            photoItem.className = 'photo-item';
            photoItem.setAttribute('aria-label', `View ${description}`);
            const thumbnail = document.createElement('img');
            thumbnail.src = photo.image_url;
            thumbnail.alt = '';
            photoItem.append(thumbnail);
            photoItem.addEventListener('click', () => openImageViewer(photo.image_url, description));
            photosContent.appendChild(photoItem);
        });
        document.getElementById('photosSection').style.display = 'block';
    } else {
        photosContent.innerHTML = '<p style="color: #70757a; font-size: 14px; padding: 20px; text-align: center;">No photos available</p>';
        document.getElementById('photosSection').style.display = 'block';
    }

    // Populate websites - FIXED to match PHP output
    const websitesContent = document.getElementById('detailWebsites');
    websitesContent.innerHTML = '';

    // PHP returns 'hotel_websites' as an array of URL strings
    if (location.hotel_websites && Array.isArray(location.hotel_websites) && location.hotel_websites.length > 0) {
        location.hotel_websites.forEach(websiteUrl => {
            if (websiteUrl && websiteUrl.trim() !== '') {
                const websiteItem = document.createElement('a');
                websiteItem.className = 'website-item';
                websiteItem.href = websiteUrl;
                websiteItem.target = '_blank';

                let icon = 'fa-globe';
                let label = 'Website';
                const lowerUrl = websiteUrl.toLowerCase();

                if (lowerUrl.includes('facebook')) {
                    icon = 'fa-facebook';
                    label = 'Facebook';
                } else if (lowerUrl.includes('instagram')) {
                    icon = 'fa-instagram';
                    label = 'Instagram';
                } else if (lowerUrl.includes('twitter') || lowerUrl.includes('x.com')) {
                    icon = 'fa-twitter';
                    label = 'Twitter';
                } else if (lowerUrl.includes('youtube')) {
                    icon = 'fa-youtube';
                    label = 'YouTube';
                } else if (lowerUrl.includes('tiktok')) {
                    icon = 'fa-tiktok';
                    label = 'TikTok';
                }

                websiteItem.innerHTML = `
                    <div class="website-icon">
                        <i class="fab ${icon}"></i>
                    </div>
                    <div class="website-info">
                        <div class="website-label">${label}</div>
                        <div class="website-url">${websiteUrl}</div>
                    </div>
                    <i class="fas fa-arrow-up-right-from-square website-arrow"></i>
                `;

                websitesContent.appendChild(websiteItem);
            }
        });
        document.getElementById('websitesSection').style.display = 'block';
    } else {
        websitesContent.innerHTML = '<p style="color: #70757a; font-size: 14px; padding: 20px; text-align: center;">No websites or social media links available</p>';
        document.getElementById('websitesSection').style.display = 'block';
    }

    // Switch to overview tab and show modal
    window.switchTab('overview');
    
    window.onDisplayLocationModal?.(location);
    detailsTrigger = trigger;
    const modal = document.getElementById('locationModal');
    setPlaceSheetLevel(level);
    modal.style.display = 'block';
    modal.querySelector('.modal-content').scrollTop = 0;
    modal.querySelector('.modal-close-btn').focus({ preventScroll: true });
}

function centerMapOnUser({ zoomIn = false } = {}) {
    cancelPlaceSelection();
    const { latitude, longitude } = userCurrentPosition.coords;
    const currentZoom = map.getZoom();
    const targetZoom = zoomIn ? Math.max(18, currentZoom + 1) : Math.max(16, currentZoom);
    const zoom = Math.min(map.getMaxZoom(), targetZoom);
    let center = L.latLng(latitude, longitude);

    if (isNavigating && window.matchMedia('(max-width: 767px)').matches) {
        // Move the camera down so the location sits in the visible part of the map.
        center = map.unproject(map.project(center, zoom).add([0, navPanel.offsetHeight / 2]), zoom);
    }

    map.closePopup();
    map.flyTo(center, zoom, { duration: 2.6 });
}

function recenterOnUser() {
    if (!userCurrentPosition) {
        if (!isTrackingLocation) {
            startLocationTracking();
        }
        if (isTrackingLocation) showToast('Waiting for your location…');
        return;
    }

    centerMapOnUser();
}

// Add event listener for recenter button
document.addEventListener('DOMContentLoaded', function() {
    const recenterBtn = document.getElementById('recenterBtn');
    if (recenterBtn) {
        recenterBtn.addEventListener('click', recenterOnUser);
    }
});
