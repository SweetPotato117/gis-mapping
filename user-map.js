// Initialize map centered on Cauayan City, Isabela
const map = L.map('map', {
    center: [16.9305, 121.7400],
    zoom: 14,
    maxBoundsViscosity: 1.0
});

const cauayanBounds = L.latLngBounds(
    [16.85, 121.68],
    [17.01, 121.85]
);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    noWrap: true
}).addTo(map);

console.log('Cauayan City boundary loaded from local data');

let markers = [];
let currentMarker = null;
let locations = [];
let currentLocationId = null;

// Live tracking variables
let userLocationMarker = null;
let userAccuracyCircle = null;
let watchPositionId = null;
let isTrackingLocation = false;
let userCurrentPosition = null;
let routingControl = null;
let isNavigating = false;
let navigationUpdateInterval = null;

const iconMap = {
  restaurant: 'assets/icons/restaurant.png',
  hotels: 'assets/icons/hotels.png',
  touristdestination: 'assets/icons/tourist.png',
  default: 'assets/icons/marker.png'
};


function getIconForType(type) {
  let key = type;
  if (typeof key === 'string') key = key.replace(/[-_\s]/g, '').toLowerCase();
  // Normalize 'tourist', 'tourist-destination', etc. to 'touristdestination'
  if (key === 'tourist' || key === 'touristdestination' || key === 'touristdest') key = 'touristdestination';
  if (key === 'hotel') key = 'hotels';
  if (key === 'restaurant') key = 'restaurant';
  const url = iconMap[key] || iconMap.default;
  return L.icon({
    iconUrl: url,
    iconSize: [40, 40],
    iconAnchor: [15, 40],
    popupAnchor: [0, -38],
    shadowSize: [41, 41]
  });
}


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

// User location marker using standard red marker icon
const userLocationIcon = L.icon({
    iconUrl: 'focus.png',
    iconSize: [25, 25],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});



// Marker management with directions button
function addLocationMarker(location, index) {
    const marker = L.marker([parseFloat(location.lat), parseFloat(location.lng)], {
        icon: getIconForType(location.type)
    }).addTo(map);

    // Determine label class based on location type
    let labelClass = 'location-label';
    if (location.type === 'restaurant') {
        labelClass = 'location-label restaurant-label';
    } else if (location.type === 'hotels') {
        labelClass = 'location-label hotels-label';
    } else if (location.type === 'touristdestination') {
        labelClass = 'location-label tourist-label';
    }

    // Add permanent tooltip label beside the marker
    marker.bindTooltip(location.name, {
        permanent: true,
        direction: 'right',
        className: labelClass,
        offset: [10, -5]
    });

    const popup = `
        <div class="popup-title location-${location.type}">${location.name}</div>
        <div class="popup-info">${formatType(location.type)}</div>
        <div class="popup-info">${location.address || 'No address'}</div>
        <button class="popup-btn" onclick="viewLocationDetails(${index})">View Details</button>
        <button class="directions-btn" onclick="startNavigation(${location.lat}, ${location.lng}, '${escapeHtml(location.name)}')">
            <i class="fas fa-directions"></i> Get Directions
        </button>
    `;
    
    marker.bindPopup(popup);
    markers[index] = {marker: marker, type: location.type, location: location};
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

function saveLocations() {
    return;
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;');
}

// ====================================
// LIVE LOCATION TRACKING
// ====================================

// Create navigation panel with simple show/hide functionality
const navPanel = document.createElement('div');
navPanel.className = 'navigation-panel';
navPanel.id = 'navigationPanel';
navPanel.innerHTML = `
    <div class="nav-panel-header">
        <h4><i class="fas fa-route"></i> Navigation</h4>
        <button class="nav-close-btn" onclick="stopNavigation()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="nav-panel-content">
        <div class="nav-summary">
            <div class="nav-summary-item">
                <span class="nav-summary-value" id="navDistance">--</span>
                <span class="nav-summary-label">Distance</span>
            </div>
            <div class="nav-summary-item">
                <span class="nav-summary-value" id="navDuration">--</span>
                <span class="nav-summary-label">Time</span>
            </div>
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
    
    // FIXED: Only update button if it exists
    const locationBtn = document.querySelector('.my-location-btn');
    if (locationBtn) {
        locationBtn.classList.add('active');
    }
    
    console.log('Location tracking started...');
}

function stopLocationTracking() {
    if (watchPositionId) {
        navigator.geolocation.clearWatch(watchPositionId);
        watchPositionId = null;
    }

    if (userLocationMarker) {
        map.removeLayer(userLocationMarker);
        userLocationMarker = null;
    }

    isTrackingLocation = false;
    
    // FIXED: Only update button if it exists
    const locationBtn = document.querySelector('.my-location-btn');
    if (locationBtn) {
        locationBtn.classList.remove('active');
    }
    
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
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    const accuracy = position.coords.accuracy;

    console.log('Location updated:', lat, lng, 'Accuracy:', accuracy);

    // Update GPS accuracy indicator
    updateGPSIndicator(accuracy);

    // Update or create user location marker (NO ACCURACY CIRCLE)
    if (!userLocationMarker) {
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

        // Center map on user location (first time)
        map.setView([lat, lng], 16);
    } else {
        userLocationMarker.setLatLng([lat, lng]);
        userLocationMarker.setPopupContent(`
            <div style="text-align: center;">
                <strong>Your Location</strong><br>
                Accuracy: ${accuracy.toFixed(0)} meters
            </div>
        `);
    }

    // Update navigation if active
    if (isNavigating && routingControl) {
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

// ====================================
// NAVIGATION WITH ROUTING
// ====================================

// Start navigation to destination
function startNavigation(destLat, destLng, destName) {
    // Check if location tracking is active
    if (!isTrackingLocation || !userCurrentPosition) {
        alert('Please enable location tracking first by clicking the location button');
        startLocationTracking();
        return;
    }

    const userLat = userCurrentPosition.coords.latitude;
    const userLng = userCurrentPosition.coords.longitude;

    // Remove existing route
    if (routingControl) {
        map.removeControl(routingControl);
    }

    // Create routing control
    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(userLat, userLng),
            L.latLng(destLat, destLng)
        ],
        routeWhileDragging: false,
        addWaypoints: false,
        draggableWaypoints: false,
        fitSelectedRoutes: true,
        showAlternatives: false,
        lineOptions: {
            styles: [{color: '#4285F4', opacity: 0.8, weight: 6}]
        },
        createMarker: function(i, wp) {
            if (i === 0) {
                return null; // Don't create marker for start point
            }
            return L.marker(wp.latLng, {
                draggable: false,
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            });
        },
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1'
        })
    }).addTo(map);

    // Listen for route found
    routingControl.on('routesfound', function(e) {
        const route = e.routes[0];
        displayNavigationInfo(route);
    });

    isNavigating = true;
    document.getElementById('navigationPanel').classList.add('active');

    // Update route every 1 second
    if (navigationUpdateInterval) {
        clearInterval(navigationUpdateInterval);
    }
    navigationUpdateInterval = setInterval(() => {
        if (isNavigating && userCurrentPosition) {
            updateNavigationRoute();
        }
    }, 1000);

    console.log('Navigation started to:', destName);
}

// Update navigation route with current position
function updateNavigationRoute() {
    if (!routingControl || !userCurrentPosition) return;

    const userLat = userCurrentPosition.coords.latitude;
    const userLng = userCurrentPosition.coords.longitude;

    const waypoints = routingControl.getWaypoints();
    if (waypoints.length === 2) {
        routingControl.setWaypoints([
            L.latLng(userLat, userLng),
            waypoints[waypoints.length - 1].latLng
        ]);
    }
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
    const hours = Math.floor(duration / 3600);
    const minutes = Math.floor((duration % 3600) / 60);
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

    route.instructions.slice(0, 10).forEach((instruction, index) => {
        const instDiv = document.createElement('div');
        instDiv.className = 'nav-instruction';

        let icon = 'fa-arrow-up';
        if (instruction.type.includes('right')) {
            icon = 'fa-arrow-right';
        } else if (instruction.type.includes('left')) {
            icon = 'fa-arrow-left';
        } else if (instruction.type.includes('straight')) {
            icon = 'fa-arrow-up';
        }

        instDiv.innerHTML = `
            <div class="nav-instruction-icon">
                <i class="fas ${icon}"></i>
            </div>
            <div class="nav-instruction-text">
                <strong>${instruction.text}</strong>
                <span>${instruction.distance >= 1000 ? 
                    `${(instruction.distance / 1000).toFixed(1)} km` : 
                    `${Math.round(instruction.distance)} m`}</span>
            </div>
        `;

        instructionsContainer.appendChild(instDiv);
    });

    // Check if arrived
    if (distance < 20) {
        arrivedAtDestination();
    }
}

// Stop navigation
function stopNavigation() {
    isNavigating = false;

    if (navigationUpdateInterval) {
        clearInterval(navigationUpdateInterval);
        navigationUpdateInterval = null;
    }

    if (routingControl) {
        map.removeControl(routingControl);
        routingControl = null;
    }

    document.getElementById('navigationPanel').classList.remove('active');
    console.log('Navigation stopped');
}

// Arrived at destination
function arrivedAtDestination() {
    alert('You have arrived at your destination!');
    stopNavigation();
}

// ====================================
// SEARCH FUNCTIONALITY
// ====================================

const searchInput = document.getElementById('mapLocationSearch');
const searchClearBtn = document.getElementById('mapSearchClear');
let suggestionsList = null;
let currentFocus = -1;
let searchTimeout = null;

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

        searchTimeout = setTimeout(() => performSearch(searchTerm), 300);
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
            if (currentFocus > -1 && items[currentFocus]) {
                items[currentFocus].click();
            }
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
    try {
        const response = await fetch(`controllers/user/search_locations.php?term=${encodeURIComponent(searchTerm)}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Search failed');
        }

        displaySearchResults(data.data, searchTerm);
    } catch (error) {
        console.error('Search error:', error);
        suggestionsList.innerHTML = `
            <div class="no-results">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="no-results-text">Error performing search</div>
            </div>
        `;
    }
}

function displaySearchResults(results, searchTerm) {
    suggestionsList.innerHTML = '';
    currentFocus = -1;

    if (results.length === 0) {
        suggestionsList.innerHTML = `
            <div class="no-results">
                <i class="fas fa-search"></i>
                <div class="no-results-text">No results found for "${searchTerm}"</div>
            </div>
        `;
        return;
    }

    const header = document.createElement('div');
    header.className = 'search-results-header';
    header.innerHTML = `<i class="fas fa-info-circle"></i> ${results.length} Results`;
    suggestionsList.appendChild(header);

    results.forEach((location) => {
        const resultItem = document.createElement('div');
        resultItem.className = 'autocomplete-item';

        const nameHighlighted = highlightMatch(location.name, searchTerm);
        const hasMenuMatch = location.menu_items && location.menu_items.toLowerCase().includes(searchTerm.toLowerCase());
        const iconClass = getIconClass(location.type);

        resultItem.innerHTML = `
            <div class="result-item-content">
                <div class="result-left">
                    <div class="result-icon ${location.type}">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="result-details">
                        <div class="result-name">${nameHighlighted}</div>
                        <div class="result-meta">
                            <span class="result-type">${formatType(location.type)}</span>
                            ${location.address ? `<span class="result-separator">•</span> <span class="result-address">${location.address}</span>` : ''}
                        </div>
                        ${hasMenuMatch ? `<div class="result-menu-match"><i class="fas fa-utensils"></i> <span>Has <strong>${highlightMatch(location.menu_items.split(',')[0], searchTerm)}</strong></span></div>` : ''}
                    </div>
                </div>
                <div class="result-actions">
                    <div class="directions-icon">
                        <i class="fas fa-directions"></i>
                    </div>
                </div>
            </div>
        `;

        resultItem.addEventListener('click', () => {
            navigateToLocation(location);
            searchInput.value = location.name;
            closeSuggestions();
        });

        suggestionsList.appendChild(resultItem);
    });
}

function navigateToLocation(location) {
    const markerIndex = markers.findIndex(m => m.location && m.location.id === location.id);

    if (markerIndex !== -1) {
        const markerObj = markers[markerIndex];
        map.flyTo([parseFloat(location.lat), parseFloat(location.lng)], 17, {
            duration: 1.5,
            easeLinearity: 0.25
        });

        setTimeout(() => {
            markerObj.marker.openPopup();
        }, 1600);
    } else {
        map.flyTo([parseFloat(location.lat), parseFloat(location.lng)], 17, {
            duration: 1.5,
            easeLinearity: 0.25
        });
    }
}

function highlightMatch(text, searchTerm) {
    if (!text) return '';
    const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
    return text.replace(regex, '<strong>$1</strong>');
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
    if (suggestionsList) {
        suggestionsList.innerHTML = '';
    }
    currentFocus = -1;
}

// ====================================
// INITIALIZATION
// ====================================

loadLocations();
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
let activeFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');

            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            activeFilter = filter;
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


// ====================================
// ZOOM-BASED VISIBILITY CONTROL (Google Maps Style)
// ====================================

// Update map container class based on zoom level
function updateZoomClass() {
    const currentZoom = map.getZoom();
    const mapContainer = map.getContainer();

    // Remove all zoom level classes
    for (let i = 5; i <= 18; i++) {
        mapContainer.classList.remove(`zoom-level-${i}`);
    }

    // Add current zoom level class
    mapContainer.classList.add(`zoom-level-${currentZoom}`);
    console.log('Current zoom level:', currentZoom);
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
window.closeLocationModal = function() {
    document.getElementById('locationModal').style.display = 'none';
};

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

window.openImageViewer = function(imageUrl) {
    window.open(imageUrl, '_blank');
};

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

// Update viewLocationDetails function to populate the new modal
window.viewLocationDetails = async function(index) {
    const location = locations[index];

    if (!location) {
        console.error('Location not found:', index);
        return;
    }

    console.log('Opening location details:', location);

    // Set hero image - Use first highlight/photo as header
    const heroImage = document.getElementById('modalHeroImage');
    if (location.highlights && location.highlights.length > 0) {
        heroImage.style.backgroundImage = `url(${location.highlights[0].image_url})`;
    } else {
        // Use green gradient as fallback
        heroImage.style.backgroundImage = 'linear-gradient(135deg, #2d7a3e 0%, #1f5b2d 100%)';
    }

    // Set location details
    document.getElementById('modalTitle').textContent = location.name || 'Unknown Location';
    document.getElementById('locationCategory').textContent = formatType(location.type);
    document.getElementById('detailType').textContent = formatType(location.type);
    document.getElementById('detailAddress').textContent = location.address || '-';
    document.getElementById('detailHours').textContent = location.open_hours || '-';
    document.getElementById('detailContact').textContent = location.contact || '-';
    document.getElementById('detailCoords').textContent = `${location.lat}, ${location.lng}`;
    document.getElementById('detailDescription').textContent = location.description || 'No description available.';

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
        location.highlights.forEach(photo => {
            const photoItem = document.createElement('div');
            photoItem.className = 'photo-item';
            photoItem.innerHTML = `<img src="${photo.image_url}" alt="Photo" onclick="openImageViewer('${photo.image_url}')">`;
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
    document.getElementById('locationModal').style.display = 'block';
};
