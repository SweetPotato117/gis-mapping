document.addEventListener('DOMContentLoaded', function() {
    // Photo deletion modal
    const deletePhotoModal = document.getElementById('deletePhotoModal');
    const confirmDeleteBtn = document.getElementById('confirmDeletePhoto');
    
    if (deletePhotoModal && confirmDeleteBtn) {
        deletePhotoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const photoId = button.getAttribute('data-photo-id');
            const placeId = button.getAttribute('data-place-id');

            console.log('Preparing to delete photo ID:', photoId, 'for place ID:', placeId);
            
            const deleteUrl = `../controllers/admin/delete_place_highlight.php?highlight_id=${photoId}&place_id=${placeId}`;
            confirmDeleteBtn.setAttribute('href', deleteUrl);
        });
    }

    // Website deletion modal
    const deleteWebsiteModal = document.getElementById('deleteWebsiteModal');
    const confirmDeleteWebsiteBtn = document.getElementById('confirmDeleteWebsite');
    
    if (deleteWebsiteModal && confirmDeleteWebsiteBtn) {
        deleteWebsiteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const websiteId = button.getAttribute('data-website-id');
            const placeId = button.getAttribute('data-place-id');
            
            console.log('Preparing to delete website ID:', websiteId, 'for place ID:', placeId);
            
            const deleteUrl = `../controllers/admin/delete_place_website.php?website_id=${websiteId}&place_id=${placeId}`;
            confirmDeleteWebsiteBtn.setAttribute('href', deleteUrl);
        });
    }
});

// Get id of location from URL if editing
const urlParams = new URLSearchParams(window.location.search);
const locationId = urlParams.get('id');

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
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
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
        const response = await fetch(`../controllers/admin/get_edit_map_data.php?id=${locationId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));

        // Get raw text first to see what's being returned
        const text = await response.text();
        console.log('Raw response:', text);
        console.log('Response length:', text.length);

        // Check if response is empty
        if (!text || text.trim().length === 0) {
            console.error('Empty response from server');
            return [];
        }

        // Try to parse JSON
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

// Load locations - now an async function
async function loadLocations() {
    // Note: In edit mode, fetchAndDisplayLocations returns an ARRAY containing the single location being edited
    const fetchedLocations = await fetchAndDisplayLocations(); 
    
    if (Array.isArray(fetchedLocations) && fetchedLocations.length > 0) {
        locations = fetchedLocations;
        
        // --- NEW: Set Blur Checkbox State on Page Load ---
        const currentLocation = locations[0]; // Since we are editing specific ID, it's the first item
        const blurToggle = document.getElementById('blurServicesToggle');
        
        if (blurToggle && currentLocation) {
            // Check if DB has it as 1 or "1"
            const isBlurred = currentLocation.blur_services == 1;
            blurToggle.checked = isBlurred;

            // Trigger the change event manually so the visual blur effect (CSS) applies immediately
            const event = new Event('change');
            blurToggle.dispatchEvent(event);
            
            console.log('Loaded Blur Setting:', isBlurred);
        }
        // -------------------------------------------------

        locations.forEach((loc, index) => {
            addLocationMarker(loc, index);
        });
        
        // Center map on the location being edited
        if (locations[0].lat && locations[0].lng) {
            map.setView([locations[0].lat, locations[0].lng], 16);
        }
    }
}


/*
    // Demo sample locations matching database structure
    function getSampleLocations() {
        return [
            {
                id: 1,
                name: 'Admin Place',
                type: 'restaurant',
                description: 'This is a demo restaurant',
                address: 'Bustamante, Isabela, Cagayan Valley, 3305, Philippines',
                lat: 16.92999900,
                lng: 121.73769200,
                open_hours: '8:00 AM - 10:00 PM',
                contact: '+63 912 345 6789',
                url: 'https://facebook.com/adminplace',
                menu: [
                    { id: 1, place_id: 1, name: 'Chicken Adobo', type: 'main', price: 499.00 },
                    { id: 2, place_id: 1, name: 'Sinigang', type: 'main', price: 350.00 }
                ],
                highlights: [],

            }
    
        ];    
    } */

    function saveLocations() {
        return;
    }

    // Marker management
    function addLocationMarker(location, index) {
        const marker = L.marker([parseFloat(location.lat), parseFloat(location.lng)], {
            icon: getIconForType(location.type)
        }).addTo(map);
        
        const popup = `
            <div class="popup-title">${location.name}</div>
            <div class="popup-info">${formatType(location.type)}</div>
            <div class="popup-info">${location.address || 'No address'}</div>
            
        `;
        marker.bindPopup(popup);
        markers.push(marker);
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
        
        // Hide all service sections
        document.querySelectorAll('.service-section').forEach(s => s.style.display = 'none');
        const notice = document.querySelector('.services-notice');
        
        // Show appropriate section based on type
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
        
        // Update current marker icon if exists
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
            await reverseGeocode(pos.lat, pos.lng);
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
        clearForm: clearForm
    };

    // Expose helper functions used by external handlers
    window.getIconForType = getIconForType;