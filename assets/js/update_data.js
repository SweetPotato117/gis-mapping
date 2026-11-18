window.submitUpdateLocation = async function(locationData) {

const formData = new FormData();

console.log('Preparing to update location:', locationData);
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

// Capture Blur Setting
const blurToggle = document.getElementById('blurServicesToggle');
const isBlurred = blurToggle && blurToggle.checked ? 1 : 0;
formData.append('blur_services', isBlurred);
console.log('Blur Toggle checked:', blurToggle?.checked);
console.log('Blur Services being sent:', isBlurred);

// Menu items
if (locationData.menu && locationData.menu.length > 0) {
    locationData.menu.forEach((m, i) => {
        formData.append(`menu_items[${i}][name]`, m.name);
        formData.append(`menu_items[${i}][price]`, m.price);
    });
}

// Websites - validate URLs before appending (works for all location types)
if (locationData.hotel_websites && locationData.hotel_websites.length > 0) {
    locationData.hotel_websites.forEach(website => {
        // Only append non-empty URLs
        if (website && website.trim() !== '') {
            formData.append('hotel_websites[]', website.trim());
        }
    });
}

// Handle different photo inputs based on location type
let photoInputId = null;
switch(locationData.type) {
    case 'restaurant':
        photoInputId = 'foodPhotos';
        break;
    case 'hotels':
        photoInputId = 'hotelPhotos';
        break;
    case 'tourist_destination':
        photoInputId = 'touristPhotos';
        break;
    default:
        console.warn('Unknown location type:', locationData.type);
}

// Append photos if the appropriate input exists and has files
if (photoInputId) {
    const photoInput = document.getElementById(photoInputId);
    if (photoInput && photoInput.files && photoInput.files.length > 0) {
        // Validate file types on client side
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        Array.from(photoInput.files).forEach((file, index) => {
            // Validate file type
            if (!validTypes.includes(file.type)) {
                console.error(`Invalid file type for ${file.name}:`, file.type);
                throw new Error(`Invalid file type: ${file.name}. Only images are allowed.`);
            }
            
            // Validate file size
            if (file.size > maxSize) {
                console.error(`File too large: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
                throw new Error(`File ${file.name} exceeds 5MB limit.`);
            }
            
            // FIXED: Append actual file object, not base64
            formData.append('highlights[]', file);
        });
        
        console.log(`Uploading ${photoInput.files.length} photo(s) for ${locationData.type}`);
    } else {
        console.log('No photos selected for upload');
    }
}

const response = await fetch('../controllers/admin/update_map_data.php', {
    method: 'POST',
    body: formData
});

const json = await response.json();
return json;

};
