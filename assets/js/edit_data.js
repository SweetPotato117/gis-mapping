// Function to submit location update to server
window.submitUpdateLocation = async function(locationData) {
    const formData = new FormData();
    
    // Append basic fields
    formData.append('id', locationData.id);
    formData.append('name', locationData.name);
    formData.append('type', locationData.type);
    formData.append('description', locationData.description);
    const inputs = new FormData(document.getElementById('locationForm'));
    formData.append('place_about', JSON.stringify({
        amenities: inputs.getAll('amenities[]'),
        house_rules: inputs.getAll('house_rules[]')
    }));
    formData.append('address', locationData.address);
    formData.append('lat', locationData.lat);
    formData.append('lng', locationData.lng);
    formData.append('open_hours', locationData.open_hours);
    formData.append('contact', locationData.contact);
    formData.append('url', locationData.url || '');

    // --- NEW: Capture Blur Setting ---
    // This grabs the checkbox state and sends it as '1' or '0'
    const blurToggle = document.getElementById('blurServicesToggle');
    if (blurToggle) formData.append('blur_services', blurToggle.checked ? 1 : 0);
    inputs.getAll('removed_highlights[]').forEach(id => formData.append('removed_highlights[]', id));
    // ---------------------------------
    
    // Append menu items
    if (locationData.menu && locationData.menu.length > 0) {
        locationData.menu.forEach((m, i) => {
            formData.append(`menu_items[${i}][name]`, m.name);
            formData.append(`menu_items[${i}][price]`, m.price);
        });
    }
    
    // Append hotel websites
    if (locationData.hotel_websites && locationData.hotel_websites.length > 0) {
        locationData.hotel_websites.forEach(h => formData.append('hotel_websites[]', h));
    }
    
    // Append highlight files (optional)
    const photoInputId = {
        'restaurant': 'foodPhotos',
        'hotels': 'hotelPhotos',
        'tourist_destination': 'touristPhotos',
    }[locationData.type];
    
    if (photoInputId) {
        const photoInput = document.getElementById(photoInputId);
        if (photoInput && photoInput.files && photoInput.files.length > 0) {
            Array.from(photoInput.files).forEach(f => formData.append('highlights[]', f));
        }
    }
    
    const response = await fetch('../controllers/admin/edit_map_data.php', {
        method: 'POST',
        body: formData
    });
    
    const json = await response.json();
    return json;
};
