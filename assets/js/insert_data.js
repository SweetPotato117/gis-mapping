window.submitNewLocation = async function(locationData) {

    const formData = new FormData();

    formData.append('name', locationData.name);
    formData.append('type', locationData.type);
    formData.append('description', locationData.description);
    formData.append('address', locationData.address);
    formData.append('lat', locationData.lat);
    formData.append('lng', locationData.lng);
    formData.append('open_hours', locationData.open_hours);
    formData.append('contact', locationData.contact);

    // --- NEW: Capture Blur Setting ---
    // Check the DOM element directly to ensure we get the current state
    const blurToggle = document.getElementById('blurServicesToggle');
    const isBlurred = blurToggle && blurToggle.checked ? 1 : 0;
    formData.append('blur_services', isBlurred);
    // ---------------------------------

    // Menu items
    if (locationData.menu && locationData.menu.length > 0) {
        locationData.menu.forEach((m, i) => {
            formData.append(`menu_items[${i}][name]`, m.name);
            formData.append(`menu_items[${i}][price]`, m.price);
        });
    }

    // Websites
    if (locationData.hotel_websites && locationData.hotel_websites.length > 0) {
        locationData.hotel_websites.forEach(website => {
            if (website && website.trim() !== '') {
                formData.append('hotel_websites[]', website.trim());
            }
        });
    }

    // Handle Photos
    let photoInputId = null;
    switch(locationData.type) {
        case 'restaurant': photoInputId = 'foodPhotos'; break;
        case 'hotels': photoInputId = 'hotelPhotos'; break;
        case 'tourist_destination': photoInputId = 'touristPhotos'; break;
    }

    if (photoInputId) {
        const photoInput = document.getElementById(photoInputId);
        if (photoInput && photoInput.files && photoInput.files.length > 0) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            Array.from(photoInput.files).forEach((file) => {
                if (!validTypes.includes(file.type)) throw new Error(`Invalid file type: ${file.name}`);
                if (file.size > maxSize) throw new Error(`File ${file.name} exceeds 5MB limit.`);
                formData.append('highlights[]', file);
            });
        }
    }

    const response = await fetch('../controllers/admin/insert_map_data.php', {
        method: 'POST',
        body: formData
    });

    const json = await response.json();
    return json;
};