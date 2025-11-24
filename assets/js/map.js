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
    attribution: 'Â© OpenStreetMap contributors',
    noWrap: true
}).addTo(map);

/*
const cauayanPolygon = [{lat:16.946591,lng:121.724871},{lat:16.948881,lng:121.733632},{lat:16.949005,lng:121.735151},{lat:16.946378,lng:121.742856},{lat:16.946931,lng:121.744048},{lat:16.949773,lng:121.748348},{lat:16.949061,lng:121.750031},{lat:16.948703,lng:121.760071},{lat:16.949295,lng:121.762502},{lat:16.947219,lng:121.768286},{lat:16.948772,lng:121.772931},{lat:16.952169,lng:121.777685},{lat:16.952079,lng:121.777999},{lat:16.951789,lng:121.784751},{lat:16.953005,lng:121.787597},{lat:16.963207,lng:121.792889},{lat:16.964505,lng:121.796179},{lat:16.964693,lng:121.797198},{lat:16.973849,lng:121.806744},{lat:16.973463,lng:121.809077},{lat:16.974541,lng:121.814687},{lat:16.970384,lng:121.824397},{lat:16.969732,lng:121.826622},{lat:16.981298,lng:121.836492},{lat:16.978056,lng:121.843177},{lat:16.977951,lng:121.844897},{lat:16.97671,lng:121.847871},{lat:16.976506,lng:121.848203},{lat:16.964775,lng:121.857333},{lat:16.964636,lng:121.859765},{lat:16.964425,lng:121.860147},{lat:16.962114,lng:121.866549},{lat:16.961832,lng:121.866956},{lat:16.961612,lng:121.867818},{lat:16.96089,lng:121.877554},{lat:16.9614,lng:121.880266},{lat:16.960704,lng:121.883809},{lat:16.960066,lng:121.885802},{lat:16.959426,lng:121.886839},{lat:16.958316,lng:121.890045},{lat:16.958545,lng:121.897857},{lat:16.958721,lng:121.898456},{lat:16.958701,lng:121.899019},{lat:16.966861,lng:121.909959},{lat:16.968865,lng:121.913467},{lat:16.972191,lng:121.921774},{lat:16.968347,lng:121.923894},{lat:16.967033,lng:121.926566},{lat:16.965912,lng:121.931843},{lat:16.966537,lng:121.938857},{lat:16.96834,lng:121.944663},{lat:16.970111,lng:121.947044},{lat:16.969498,lng:121.94927},{lat:16.971311,lng:121.95544},{lat:16.975155,lng:121.958271},{lat:16.97545,lng:121.958615},{lat:16.978303,lng:121.961261},{lat:16.978581,lng:121.962772},{lat:16.978402,lng:121.965023},{lat:16.970737,lng:121.973},{lat:16.969578,lng:121.973429},{lat:16.964415,lng:121.977685},{lat:16.963727,lng:121.978717},{lat:16.955189,lng:121.975111},{lat:16.953909,lng:121.974781},{lat:16.941841,lng:121.97775},{lat:16.936999,lng:121.985883},{lat:16.936682,lng:121.986057},{lat:16.926175,lng:121.976063},{lat:16.925139,lng:121.976114},{lat:16.919276,lng:121.978337},{lat:16.912374,lng:121.977502},{lat:16.911726,lng:121.977715},{lat:16.907627,lng:121.975748},{lat:16.8994,lng:121.976377},{lat:16.899141,lng:121.976072},{lat:16.897244,lng:121.974824},{lat:16.893666,lng:121.971533},{lat:16.893509,lng:121.971446},{lat:16.887832,lng:121.970546},{lat:16.887753,lng:121.970433},{lat:16.880459,lng:121.964837},{lat:16.878514,lng:121.964473},{lat:16.877018,lng:121.962234},{lat:16.875983,lng:121.959608},{lat:16.869548,lng:121.952352},{lat:16.868986,lng:121.950507},{lat:16.868099,lng:121.950224},{lat:16.861203,lng:121.940339},{lat:16.860249,lng:121.936904},{lat:16.862689,lng:121.928275},{lat:16.861879,lng:121.926254},{lat:16.86252,lng:121.922094},{lat:16.862548,lng:121.921027},{lat:16.857484,lng:121.914899},{lat:16.855139,lng:121.9106},{lat:16.854256,lng:121.909511},{lat:16.849867,lng:121.90671},{lat:16.8477,lng:121.90555},{lat:16.846964,lng:121.897562},{lat:16.845793,lng:121.894882},{lat:16.837089,lng:121.891789},{lat:16.834239,lng:121.884822},{lat:16.833829,lng:121.879884},{lat:16.838371,lng:121.876828},{lat:16.841575,lng:121.870825},{lat:16.840799,lng:121.865706},{lat:16.834239,lng:121.857975},{lat:16.829991,lng:121.855001},{lat:16.829861,lng:121.853297},{lat:16.831429,lng:121.849919},{lat:16.832349,lng:121.849187},{lat:16.837588,lng:121.843376},{lat:16.837843,lng:121.8425},{lat:16.838982,lng:121.833119},{lat:16.838495,lng:121.830405},{lat:16.834164,lng:121.820738},{lat:16.835747,lng:121.816148},{lat:16.835846,lng:121.816036},{lat:16.836826,lng:121.80838},{lat:16.837628,lng:121.806782},{lat:16.849591,lng:121.802709},{lat:16.85032,lng:121.8016},{lat:16.853038,lng:121.794993},{lat:16.853063,lng:121.790362},{lat:16.853939,lng:121.789536},{lat:16.855854,lng:121.786754},{lat:16.857259,lng:121.779221},{lat:16.855866,lng:121.773938},{lat:16.851363,lng:121.766851},{lat:16.855148,lng:121.76178},{lat:16.856781,lng:121.756605},{lat:16.857926,lng:121.747226},{lat:16.857936,lng:121.747185},{lat:16.85824,lng:121.746291},{lat:16.858249,lng:121.746181},{lat:16.859112,lng:121.743811},{lat:16.868633,lng:121.740071},{lat:16.873664,lng:121.734141},{lat:16.874847,lng:121.731808},{lat:16.874847,lng:121.73175},{lat:16.874294,lng:121.727374},{lat:16.874432,lng:121.72522},{lat:16.870687,lng:121.716975},{lat:16.875416,lng:121.713971},{lat:16.879369,lng:121.705702},{lat:16.882289,lng:121.70557},{lat:16.885841,lng:121.705389},{lat:16.886708,lng:121.705177},{lat:16.891864,lng:121.708944},{lat:16.897881,lng:121.709154},{lat:16.89974,lng:121.709887},{lat:16.900107,lng:121.709962},{lat:16.902874,lng:121.709966},{lat:16.903186,lng:121.710279},{lat:16.910263,lng:121.715457},{lat:16.910933,lng:121.715502},{lat:16.91567,lng:121.717498},{lat:16.918714,lng:121.720791},{lat:16.921211,lng:121.7216},{lat:16.925144,lng:121.721515},{lat:16.931014,lng:121.726797},{lat:16.933253,lng:121.727412},{lat:16.934015,lng:121.727515},{lat:16.940837,lng:121.722857},{lat:16.946591,lng:121.724871}];

L.polygon(cauayanPolygon, {
    color: '#000000',     
    weight: 3,           
    fill: false,           
    opacity: 1,
    interactive: false
}).addTo(map);
*/

console.log('Cauayan City boundary loaded from local data');

let markers = [];
let currentMarker = null;
let locations = [];
let currentLocationId = null;

const iconMap = {
    restaurant: '../assets/icons/restaurant.png',
    hotels: '../assets/icons/hotels.png',
    tourist_destination: '../assets/icons/tourist.png',
    default: '../assets/icons/marker.png'
};

function getIconForType(type) {
    const url = iconMap[type] || iconMap.default;
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
        'hotels': 'Hotels',
        'tourist_destination': 'Tourist Destination',
    };
    return typeMap[type] || type;
}

async function fetchAndDisplayLocations() {
    try {
        const response = await fetch('../controllers/admin/get_map_data.php', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));

        const text = await response.text();
        console.log('Raw response:', text);
        console.log('Response length:', text.length);

        if (!text || text.trim().length === 0) {
            console.error('Empty response from server');
            return [];
        }

        let json;
        try {
            json = JSON.parse(text);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.error('Response text:', text);
            return [];
        }

        if (!json || !json.success) {
            console.error('Error fetching locations:', json?.error || 'Unknown error');
            return [];
        }

        return json.data || [];

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
        
        // Initialize toggle button
        initializeViewAllToggle();
        
        // Apply initial filter (show only admin's locations)
        applyViewFilter();
        
        // Check if superadmin and show indicator
        checkSuperadminStatus();
    }
}

async function checkSuperadminStatus() {
    try {
        const response = await fetch('../controllers/admin/get_map_data.php');
        const json = await response.json();
        
        if (json.success && json.admin_role === 'superadmin') {
            // Add superadmin badge to navbar
            const navRight = document.querySelector('.nav-right');
            if (navRight && !document.getElementById('superadminBadge')) {
                const badge = document.createElement('span');
                badge.id = 'superadminBadge';
                badge.innerHTML = '<i class="fas fa-crown"></i> Superadmin';
                badge.style.cssText = `
                    background: linear-gradient(135deg, #ffd700, #ffed4e);
                    color: #1e1e2d;
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    margin-right: 10px;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                `;
                navRight.insertBefore(badge, navRight.firstChild);
            }
            
            console.log('Superadmin access enabled - can edit/delete all locations');
        }
    } catch (error) {
        console.error('Failed to check admin status:', error);
    }
}


function saveLocations() {
    return;
}


function addLocationMarker(location, index) {
    const marker = L.marker([parseFloat(location.lat), parseFloat(location.lng)], {
        icon: getIconForType(location.type)
    }).addTo(map);
    
    // Determine label class based on location type
    let labelClass = 'location-label';
    if (location.type === 'restaurant') {
        labelClass += ' restaurant-label';
    } else if (location.type === 'hotels') {
        labelClass += ' hotels-label';
    } else if (location.type === 'tourist_destination') {
        labelClass += ' tourist-label';
    }
    
    // Add permanent tooltip (label) - USE RAW TEXT, NO ESCAPING
    marker.bindTooltip(location.name, {
        permanent: true,
        direction: 'right',
        className: labelClass,
        offset: [10, -5]
    });
    
    // Popup content - USE ESCAPED HTML
    const popup = `
        <div class="popup-title">${escapeHtml(location.name)}</div>
        <div class="popup-info">${formatType(location.type)}</div>
        <div class="popup-info">${escapeHtml(location.address) || 'No address'}</div>
        <button class="popup-btn" onclick="viewLocationDetails(${index})">View Details</button>
    `;
    
    marker.bindPopup(popup);
    
    markers[index] = {
        marker: marker,
        type: location.type,
        location: location
    };
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}



function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// SEARCH FUNCTIONALITY WITH DROPDOWN SUGGESTIONS
const searchInput = document.getElementById('mapLocationSearch');
const searchClearBtn = document.getElementById('mapSearchClear');
let suggestionsList = null;
let currentFocus = -1;

if (searchInput) {
    // Create suggestions container
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

    // Input event - show suggestions
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase().trim();
        closeSuggestions();
        currentFocus = -1;

        // Show/hide clear button
        if (searchClearBtn) {
            searchClearBtn.style.display = searchTerm.length > 0 ? 'block' : 'none';
        }

        if (searchTerm.length < 2) {
            return;
        }

        // Find matching locations
        const matches = locations.filter(loc => 
            loc.name.toLowerCase().includes(searchTerm) ||
            (loc.address && loc.address.toLowerCase().includes(searchTerm)) ||
            loc.type.toLowerCase().includes(searchTerm)
        );

        if (matches.length === 0) {
            return;
        }

        // Display suggestions
        matches.slice(0, 8).forEach((location, index) => {
            const suggestionItem = document.createElement('div');
            suggestionItem.className = 'autocomplete-item';
            
            // Highlight matching text
            const nameIndex = location.name.toLowerCase().indexOf(searchTerm);
            let displayName = location.name;
            
            if (nameIndex !== -1) {
                displayName = location.name.substring(0, nameIndex) + 
                    '<strong>' + location.name.substring(nameIndex, nameIndex + searchTerm.length) + '</strong>' +
                    location.name.substring(nameIndex + searchTerm.length);
            }
            
            suggestionItem.innerHTML = `
                <div class="suggestion-name">${displayName}</div>
                <div class="suggestion-details">${formatType(location.type)} â€¢ ${location.address || 'No address'}</div>
            `;
            
            // Click event on suggestion
            suggestionItem.addEventListener('click', () => {
                navigateToLocation(location);
                searchInput.value = location.name;
                closeSuggestions();
            });
            
            suggestionsList.appendChild(suggestionItem);
        });

        // Show count if more results
        if (matches.length > 8) {
            const moreItem = document.createElement('div');
            moreItem.className = 'autocomplete-more';
            moreItem.textContent = `+${matches.length - 8} more results`;
            suggestionsList.appendChild(moreItem);
        }
    });

    // Clear button functionality
    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', () => {
            searchInput.value = '';
            closeSuggestions();
            searchClearBtn.style.display = 'none';
            searchInput.focus();
        });
    }

    // Keyboard navigation
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
                // Select highlighted suggestion
                items[currentFocus].click();
            } else {
                // Search for exact match
                const searchTerm = searchInput.value.toLowerCase().trim();
                const exactMatch = locations.find(loc => 
                    loc.name.toLowerCase() === searchTerm
                );
                
                const firstMatch = exactMatch || locations.find(loc => 
                    loc.name.toLowerCase().includes(searchTerm) ||
                    (loc.address && loc.address.toLowerCase().includes(searchTerm))
                );
                
                if (firstMatch) {
                    navigateToLocation(firstMatch);
                    searchInput.value = firstMatch.name;
                    closeSuggestions();
                }
            }
        } else if (e.key === 'Escape') {
            closeSuggestions();
        }
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!outerContainer.contains(e.target) && !searchContainer.contains(e.target)) {
            closeSuggestions();
        }
    });
}

// Navigate to location with animation
function navigateToLocation(location) {
    const markerObj = markers.find(m => m.location.id === location.id);
    
    if (markerObj && markerObj.marker) {
        map.flyTo([location.lat, location.lng], 17, {
            duration: 1.5,
            easeLinearity: 0.25
        });
        
        setTimeout(() => {
            markerObj.marker.openPopup();
        }, 1500);
    }
}

// Highlight active suggestion
function addActive(items) {
    if (!items || items.length === 0) return;
    
    removeActive(items);
    
    if (currentFocus >= items.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = items.length - 1;
    
    items[currentFocus].classList.add('autocomplete-active');
}

// Remove highlight from all suggestions
function removeActive(items) {
    for (let i = 0; i < items.length; i++) {
        items[i].classList.remove('autocomplete-active');
    }
}

// Close suggestions dropdown
function closeSuggestions() {
    if (suggestionsList) {
        suggestionsList.innerHTML = '';
    }
    currentFocus = -1;
}

// Load locations on page load
loadLocations();

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => btn.addEventListener('click', () => {
    const targetTab = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    const pane = document.getElementById(targetTab);
    if (pane) pane.classList.add('active');
}));

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
    } else if (type === 'tourist_destination') {
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

// Map click: add/edit marker
map.on('click', async (e) => {
    const lat = e.latlng.lat.toFixed(8);
    const lng = e.latlng.lng.toFixed(8);
    const latEl = document.getElementById('latitude');
    const lngEl = document.getElementById('longitude');
    if (latEl) latEl.value = lat;
    if (lngEl) lngEl.value = lng;

    if (currentMarker) map.removeLayer(currentMarker);
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
document.querySelectorAll('.detail-tab').forEach(tab => tab.addEventListener('click', () => {
    const target = tab.dataset.detail;
    document.querySelectorAll('.detail-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    document.querySelectorAll('.detail-pane').forEach(p => p.classList.remove('active'));
    const pane = document.getElementById('detail' + target.charAt(0).toUpperCase() + target.slice(1));
    if (pane) pane.classList.add('active');
}));

window.GIS = {
    locations: locations,
    markers: markers,
    map: map,
    getCurrentLocationIndex: () => currentLocationId,
    setCurrentLocationIndex: (i) => { currentLocationId = i; },
    getCurrentMarker: () => currentMarker,
    setCurrentMarker: (m) => { currentMarker = m; },
    addLocationMarker: addLocationMarker,
    clearForm: typeof clearForm !== 'undefined' ? clearForm : null
};

window.getIconForType = getIconForType;

// VIEW ALL LOCATIONS TOGGLE
let viewAllActive = false; 

// Initialize toggle button
function initializeViewAllToggle() {
    const toggleBtn = document.getElementById('viewAllToggle');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            viewAllActive = !viewAllActive;
            this.classList.toggle('active', viewAllActive);
            
            // Update button text and icon
            const textNode = this.childNodes[this.childNodes.length - 1];
            const icon = this.querySelector('i');
            
            if (viewAllActive) {
                textNode.textContent = ' View all locations';
                icon.className = 'fas fa-eye';
            } else {
                textNode.textContent = ' View all locations';
                icon.className = 'fas fa-eye';
            }
            
            // Reapply filter
            applyViewFilter();
        });
    }
}

// Apply filter based on viewAll state
function applyViewFilter() {
    // Clear all markers
    markers.forEach(marker => {
        map.removeLayer(marker.marker);
    });
    
    // Filter locations based on ownership
    const filteredLocations = locations.filter(loc => {
        if (viewAllActive) {
            return true; // Show all locations
        } else {
            return loc.is_owner === true || loc.is_owner === 1; // Show only admin's locations
        }
    });
    
    // Re-add filtered markers
    filteredLocations.forEach((loc, index) => {
        const matchingMarker = markers.find(m => m.location.id === loc.id);
        if (matchingMarker) {
            matchingMarker.marker.addTo(map);
        }
    });
    
    console.log(`Showing ${filteredLocations.length} of ${locations.length} locations (View All: ${viewAllActive})`);
}

// Update loadLocations to initialize toggle
async function loadLocations() {
    locations = await fetchAndDisplayLocations();
    
    if (Array.isArray(locations)) {
        locations.forEach((loc, index) => {
            addLocationMarker(loc, index);
        });
        
        // Initialize toggle button
        initializeViewAllToggle();
        
        // Apply initial filter (show only admin's locations)
        applyViewFilter();
    }
}
// Initialize Google-style tab switching
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.google-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('data-detail');
            
            // Remove active from all tabs
            document.querySelectorAll('.google-tab').forEach(t => t.classList.remove('active'));
            
            // Add active to clicked tab
            this.classList.add('active');
            
            // Hide all panes
            document.querySelectorAll('.google-pane').forEach(p => p.classList.remove('active'));
            
            // Show target pane
            const targetPane = document.getElementById('detail' + target.charAt(0).toUpperCase() + target.slice(1));
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });
});

// Close modal function
function closeModal() {
    const modal = document.getElementById('locationModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Copy to clipboard function
function copyToClipboard(type) {
    let text = '';
    if (type === 'address') {
        text = document.getElementById('detailAddress').textContent;
    } else if (type === 'phone') {
        text = document.getElementById('detailContact').textContent;
    }
    
    navigator.clipboard.writeText(text).then(() => {
        // Show a brief toast notification
        const toast = document.createElement('div');
        toast.textContent = 'Copied to clipboard!';
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #323232;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            z-index: 10000;
            animation: fadeIn 0.3s;
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 2000);
    });
}

// Function to apply blur to modal services
function applyModalServiceBlur(shouldBlur) {
    console.log('applyModalServiceBlur called with shouldBlur:', shouldBlur);
    
    // Blur the header/cover image
    const headerCover = document.getElementById('modalCoverImage');
    console.log('headerCover element:', headerCover);
    if (headerCover) {
        if (shouldBlur) {
            headerCover.classList.add('service-blurred');
            console.log('Added blur to header');
        } else {
            headerCover.classList.remove('service-blurred');
        }
    }
    
    // Blur menu content (which contains both menu and websites)
    const menuContent = document.getElementById('detailMenuContent');
    console.log('menuContent element:', menuContent);
    if (menuContent) {
        if (shouldBlur) {
            menuContent.classList.add('service-blurred');
            console.log('Added blur to menuContent');
        } else {
            menuContent.classList.remove('service-blurred');
        }
    }
    
    // Blur photos/highlights
    const photosGallery = document.getElementById('detailPhotos');
    console.log('photosGallery element:', photosGallery);
    if (photosGallery) {
        if (shouldBlur) {
            photosGallery.classList.add('service-blurred');
            console.log('Added blur to photosGallery');
        } else {
            photosGallery.classList.remove('service-blurred');
        }
    }
}

// Update viewLocationDetails function with proper image and website handling
function viewLocationDetails(index) {
    const location = locations[index];
    if (!location) return;
    
        // ADD THESE DEBUG LINES:
    console.log('=== DEBUG LOCATION DATA ===');
    console.log('Full location object:', location);
    console.log('Highlights:', location.highlights);
    if (location.highlights && location.highlights.length > 0) {
        location.highlights.forEach((h, i) => {
            console.log(`Highlight ${i}:`, h);
            console.log(`  - imageurl: ${h.imageurl}`);
        });
    }
    console.log('===========================');
    
    const modal = document.getElementById('locationModal');
    
// Set cover image from highlights array
const coverImage = document.getElementById('modalCoverImage');
if (location.highlights && location.highlights.length > 0 && location.highlights[0].imageurl) {
    coverImage.src = location.highlights[0].imageurl;
    coverImage.style.display = 'block';
    coverImage.alt = location.name + ' - Main Image';
    
    // Add error handling for cover image
    coverImage.onerror = function() {
        console.error('Failed to load cover image:', location.highlights[0].imageurl);
        this.src = 'https://via.placeholder.com/680x220/2d7a3e/ffffff?text=No+Image';
    };
    
    coverImage.onload = function() {
        console.log('Cover image loaded successfully:', location.highlights[0].imageurl);
    };
} else {
    coverImage.src = 'https://via.placeholder.com/680x220/2d7a3e/ffffff?text=No+Image';
    coverImage.style.display = 'block';
}

    
    // Set title and type
    document.getElementById('modalTitle').textContent = location.name || 'Unnamed Location';
    document.getElementById('modalTypeBadge').textContent = formatType(location.type);
    
    // Set overview data
    document.getElementById('detailAddress').textContent = location.address || 'No address provided';
    document.getElementById('detailHours').textContent = location.open_hours || 'Not available';
    document.getElementById('detailContact').textContent = location.contact || 'Not available';
    document.getElementById('detailCoords').textContent = `${parseFloat(location.lat).toFixed(6)}, ${parseFloat(location.lng).toFixed(6)}`;
    
    // Set description
   const descriptionEl = document.getElementById('detailDescription');
if (descriptionEl) {
    const description = location.description || 'No description provided';
    descriptionEl.textContent = description;
}

    
    // Show/hide owner actions
    const ownerActions = document.getElementById('ownerActions');
    if (location.is_owner) {
        ownerActions.style.display = 'flex';
        document.getElementById('editLocation').setAttribute('data-location-id', location.id);
        document.getElementById('deleteLocation').setAttribute('data-location-id', location.id);
    } else {
        ownerActions.style.display = 'none';
    }
    
    // Load services content (photos, menu, websites)
    loadServicesContent(location);
    
    // Apply blur to services if blur_services = 1
    console.log('Location object:', location);
    console.log('blur_services value:', location.blur_services);
    applyModalServiceBlur(location.blur_services == 1);
    
    // Show modal
    modal.style.display = 'block';
    
    // Reset to first tab
    document.querySelectorAll('.google-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.google-pane').forEach(p => p.classList.remove('active'));
    document.querySelector('.google-tab[data-detail="overview"]').classList.add('active');
    document.getElementById('detailOverview').classList.add('active');
}

// Load services content with proper website and image handling
function loadServicesContent(location) {
    const menuContent = document.getElementById('detailMenuContent');
    const photosGrid = document.getElementById('detailPhotos');
    
    // Clear previous content
    menuContent.innerHTML = '';
    photosGrid.innerHTML = '';
    
    let hasContent = false;
    
    // Load menu if available
    if (location.menu && location.menu.length > 0) {
        hasContent = true;
        let menuHTML = '<h4>MENU</h4>';
        location.menu.forEach(item => {
            menuHTML += `
                <div class="menu-item-display">
                    <span class="menu-item-name">${item.name || 'Item'}</span>
                    <span class="menu-item-price">₱${parseFloat(item.price || 0).toFixed(2)}</span>
                </div>
            `;
        });
        menuContent.innerHTML = menuHTML;
    }
    
    // Load websites if available
    if (location.hotel_websites && location.hotel_websites.length > 0) {
        hasContent = true;
        let websiteHTML = '<h4>WEBSITES</h4>';
        websiteHTML += '<div style="display: flex; flex-direction: column; gap: 8px;">';
        
        location.hotel_websites.forEach(link => {
            websiteHTML += `
                <a href="${link}" target="_blank" rel="noopener noreferrer" 
                   style="color: var(--primary-color); text-decoration: none; display: flex; 
                          align-items: center; gap: 8px; padding: 8px; background: white; 
                          border-radius: 6px; transition: all 0.2s ease;">
                    <i class="fas fa-external-link-alt" style="font-size: 14px;"></i>
                    <span style="font-size: 14px;">${link}</span>
                </a>
            `;
        });
        websiteHTML += '</div>';
        
        if (menuContent.innerHTML) {
            menuContent.innerHTML += '<div style="margin: 20px 0;"></div>' + websiteHTML;
        } else {
            menuContent.innerHTML = websiteHTML;
        }
    }
    
// Load images from highlights array with better error handling
if (location.highlights && location.highlights.length > 0) {
    hasContent = true;
    console.log('Loading highlights:', location.highlights); // Debug
    
    location.highlights.forEach((highlight, index) => {
        if (highlight.imageurl) {  // <-- This is correct now
            const imgElement = document.createElement('img');
            imgElement.src = highlight.imageurl;  // <-- Access the imageurl property
            imgElement.alt = `${location.name} - Photo ${index + 1}`;
            
            // Add loading indicator
            imgElement.style.opacity = '0';
            imgElement.style.transition = 'opacity 0.3s ease';
            
            imgElement.onload = function() {
                console.log('Image loaded successfully:', highlight.imageurl);
                this.style.opacity = '1';
            };
            
            imgElement.onerror = function() {
                console.error('Failed to load image:', highlight.imageurl);
                
                // Try alternative paths
                const originalSrc = highlight.imageurl;
                const alternatives = [
                    originalSrc,
                    originalSrc.replace('../uploads/', '../../uploads/'),
                    originalSrc.replace('../uploads/', 'uploads/'),
                    '../' + originalSrc,
                    '../../uploads/' + originalSrc.split('/').pop(),
                    '../uploads/highlights/' + originalSrc.split('/').pop()
                ];
                
                let tried = 1;
                const tryNext = () => {
                    if (tried < alternatives.length) {
                        console.log(`Trying alternative path ${tried}:`, alternatives[tried]);
                        imgElement.src = alternatives[tried];
                        tried++;
                    } else {
                        // Show placeholder if all attempts fail
                        this.src = 'https://via.placeholder.com/300x200/cccccc/666666?text=Image+Not+Found';
                        this.style.opacity = '0.5';
                        console.error('All image load attempts failed for:', originalSrc);
                    }
                };
                
                imgElement.onerror = tryNext;
                tryNext();
            };
            
            photosGrid.appendChild(imgElement);
        }
    });
}

    
    // Show message if no content
    if (!hasContent) {
        menuContent.innerHTML = '<p style="color: var(--text-secondary); font-style: italic;">No additional information available.</p>';
    }
}


// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('locationModal');
    if (event.target === modal) {
        closeModal();
    }
};


// Connect Edit and Delete buttons in modal
document.addEventListener('DOMContentLoaded', function() {
    // EDIT BUTTON
    const editButton = document.getElementById('editLocation');
    if (editButton) {
        editButton.addEventListener('click', function() {
            const placeId = this.getAttribute('data-location-id');
            
            if (!placeId) {
                console.error('No place ID found for editing');
                alert('Error: No location selected');
                return;
            }
            
            console.log('Editing location with ID:', placeId);
            
            // Check if loadEditForm function exists
            if (typeof loadEditForm === 'function') {
                loadEditForm();
            } else {
                // Direct redirect to edit page
                window.location.href = `edit_location.php?id=${placeId}`;
            }
        });
    }
    
    // DELETE BUTTON
    const deleteButton = document.getElementById('deleteLocation');
    if (deleteButton) {
        deleteButton.addEventListener('click', function() {
            const placeId = this.getAttribute('data-location-id');
            
            if (!placeId) {
                console.error('No place ID found for deletion');
                alert('Error: No location selected');
                return;
            }
            
            if (confirm('Are you sure you want to delete this location? This action cannot be undone.')) {
                console.log('Deleting location with ID:', placeId);
                
                // Call the delete function
                if (typeof window.submitDeleteLocation === 'function') {
                    window.submitDeleteLocation(placeId).then(response => {
                        if (response.success) {
                            alert('Location deleted successfully');
                            closeModal();
                            location.reload();
                        } else {
                            alert('Error deleting location: ' + response.message);
                        }
                    }).catch(error => {
                        console.error('Error during deletion request:', error);
                        alert('An unexpected error occurred. Please try again.');
                    });
                } else {
                    console.error('submitDeleteLocation function not found');
                    alert('Error: Delete functionality not loaded');
                }
            }
        });
    }
});

// SERVICES TAB BLUR TOGGLE - FIXED VERSION
document.addEventListener("DOMContentLoaded", function() {
  const blurToggle = document.getElementById("modalBlurToggle");
  const modal = document.querySelector(".modal-content.google-style");
  if (blurToggle && modal) {
    blurToggle.addEventListener("change", function() {
      modal.classList.toggle("blurred", this.checked);
    });
  }
});
    



