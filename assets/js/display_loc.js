window.fetchAndDisplayLocations = async function() {
    try {
        const response = await fetch('../controllers/admin/get_map_data.php', {
            method: 'GET'
        });

        const json = await response.json();

        if (!json || !json.success) {
            console.error('Error fetching locations:', json?.error || 'Unknown error');
            return null;
        }

        // Return the locations data array
        return json.data || [];

    } catch (error) {
        console.error('Failed to fetch locations:', error);
        return null;
    }
};

// Optional: Initialize and load locations on page load
(async function initializeLocations() {
    const locationsData = await window.fetchAndDisplayLocations();

    if (locationsData && window.GIS) {
        // Clear existing markers if any
        window.GIS.markers.forEach(marker => {
            try {
                window.GIS.map.removeLayer(marker);
            } catch (e) {}
        });

        // Reset arrays
        window.GIS.markers = [];
        window.GIS.locations = [];

        // Add each location to the map
        locationsData.forEach(location => {
            window.GIS.locations.push(location);

            const marker = L.marker([location.lat, location.lng], {
                icon: window.GIS.getIconForType ? window.GIS.getIconForType(location.type) : undefined
            }).addTo(window.GIS.map);

            // Create popup content
            let popupContent = `<strong>${location.name}</strong><br>${location.type}`;

            marker.bindPopup(popupContent);
            window.GIS.markers.push(marker);

            // Add click handler to open location modal
            marker.on('click', () => {
                const index = window.GIS.locations.indexOf(location);
                if (window.GIS.openLocationModal) {
                    window.GIS.openLocationModal(index);
                }
            });
        });
    }
})();