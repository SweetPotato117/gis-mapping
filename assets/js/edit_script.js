// Add menu item (restaurants only)
document.getElementById('addMenuItem')?.addEventListener('click', () => {
    const menuList = document.getElementById('menuList');
    if (!menuList) return;
    const menuItem = document.createElement('div');
    menuItem.className = 'menu-item';
    menuItem.innerHTML = `
        <input type="text" placeholder="Item name" class="menu-name" name="menu_name[]">
        <input type="number" step="0.01" placeholder="Price" class="menu-price" name="menu_price[]">
        <button type="button" class="btn-remove" onclick="removeMenuItem(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    menuList.appendChild(menuItem);
});

// Remove menu item
window.removeMenuItem = function (btn) {
    const menuList = document.getElementById('menuList');
    if (!menuList) return;
    if (menuList.children.length > 1) {
        btn.parentElement.remove();
    } else {
        alert('At least one menu item is required');
    }
};

// UNIFIED WEBSITE HANDLER - Works for all location types
document.querySelectorAll('.add-website-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        // Find the closest service section
        const serviceSection = e.target.closest('.service-section');
        if (!serviceSection) return;
        
        // Find the websitesList within this section
        const list = serviceSection.querySelector('#websitesList');
        if (!list) return;
        
        const item = document.createElement('div');
        item.className = 'website-item';
        item.innerHTML = `
            <input type="url" class="website-input" name="websites[]" placeholder="https://website.com">
            <button type="button" class="btn-remove-website" onclick="removeWebsite(this)">
                <i class="fas fa-trash"></i>
            </button>
        `;
        list.appendChild(item);
    });
});

// Universal remove website function (works for all types)
window.removeWebsite = function(btn) {
    const item = btn.parentElement;
    if (item) item.remove();
};

// Save location
document.getElementById('saveLocation')?.addEventListener('click', async () => {
    const placeIdEl = document.getElementById('placeId');
    const idVal = placeIdEl ? placeIdEl.value : null;
    
    // Collect basic form data
    const locationData = {
        id: idVal ? parseInt(idVal) : null,
        name: document.getElementById('locationName')?.value || '',
        type: document.getElementById('locationType')?.value || '',
        lat: parseFloat(document.getElementById('latitude')?.value || 0),
        lng: parseFloat(document.getElementById('longitude')?.value || 0),
        address: document.getElementById('address')?.value || '',
        open_hours: document.getElementById('openHours')?.value || '',
        contact: document.getElementById('contact')?.value || '',
        description: document.getElementById('description')?.value || '',
        menu: [],
        highlights: [],
        hotel_websites: []
    };

    // Validation
    if (!locationData.name || !locationData.type || !locationData.lat || !locationData.lng || !locationData.description) {
        alert('Please fill in all required fields (Name, Type, Location on map, and Description)');
        return;
    }

    // Collect menu items (restaurants only)
    if (locationData.type === 'restaurant') {
        document.querySelectorAll('#menuList .menu-item').forEach(item => {
            const name = item.querySelector('.menu-name')?.value;
            const price = item.querySelector('.menu-price')?.value;
            if (name && price) {
                locationData.menu.push({
                    name: name,
                    type: 'main',
                    price: parseFloat(price)
                });
            }
        });
    }

    // Collect websites from the visible service section
    const websiteInputs = document.querySelectorAll('#websitesList .website-input');
    websiteInputs.forEach(inp => {
        const value = inp.value.trim();
        if (value) {
            locationData.hotel_websites.push(value);
        }
    });
    

    // Handle photo uploads based on type
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
    }

    // Collect photos if available
    if (photoInputId) {
        const photoInput = document.getElementById(photoInputId);
        if (photoInput && photoInput.files.length > 0) {
            const files = Array.from(photoInput.files);
            await Promise.all(files.map(file => new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    locationData.highlights.push({ imageurl: e.target.result });
                    resolve();
                };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            })));
        }
    }

    // Update or add location (send to server)
    if (locationData.id) {
        // Update existing
        try {
            console.log('Updated location:', locationData.id);
            const json = await window.submitUpdateLocation(locationData);
            
            if (!json || !json.success) {
                alert('Error updating location: ' + (json?.error || 'Unknown'));
                return;
            }
            alert(json.message || 'Location updated successfully!');

           // window.location.href = "edit_location.php?id=" + locationData.id;
           
       
        } catch (err) {
            console.error('Update error', err);
            alert('Failed to update location. See console for details.');
        }
    } else {
        // Insert new location
        try {
            const json = await window.submitNewLocation(locationData);
            if (!json || !json.success) {
                alert('Error saving location: ' + (json?.error || 'Unknown'));
                return;
            }

            locationData.id = json.place_id || (locations.length > 0 ? Math.max(...locations.map(l => l.id)) + 1 : 1);
            if (json.url) {
                try {
                    const parsed = JSON.parse(json.url);
                    if (Array.isArray(parsed)) locationData.hotel_websites = parsed;
                } catch (e) {
                    if (!locationData.hotel_websites || locationData.hotel_websites.length === 0) {
                        locationData.url = json.url;
                    }
                }
            }

            locations.push(locationData);
            addLocationMarker(locationData, locations.length - 1);

            alert(json.message || 'Location saved successfully!');
            console.log('Saved location:', locationData);

            if (currentMarker) {
                map.removeLayer(currentMarker);
                currentMarker = null;
            }
            clearForm();
        } catch (err) {
            console.error('Save error', err);
            alert('Failed to save location. See console for details.');
        }
    }
});

// Clear form
document.getElementById('clearForm')?.addEventListener('click', clearForm);

function clearForm() {
    const form = document.getElementById('locationForm');
    if (form) form.reset();
    
    const placeIdEl = document.getElementById('placeId');
    if (placeIdEl) placeIdEl.value = '';
    
    ['latitude', 'longitude', 'address', 'description'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    
    // Reset menu list
    const menuList = document.getElementById('menuList');
    if (menuList) {
        menuList.innerHTML = `
            <div class="menu-item">
                <input type="text" placeholder="Item name" class="menu-name" name="menu_name[]">
                <input type="number" step="0.01" placeholder="Price" class="menu-price" name="menu_price[]">
                <button type="button" class="btn-remove" onclick="removeMenuItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }
    
    // Clear photo previews
    ['foodPhotosPreview', 'hotelPhotosPreview', 'touristPhotosPreview'].forEach(id => {
        const preview = document.getElementById(id);
        if (preview) preview.innerHTML = '';
    });
    
    // Clear photo inputs
    ['foodPhotos', 'hotelPhotos', 'touristPhotos'].forEach(id => {
        const input = document.getElementById(id);
        if (input) input.value = '';
    });

    // Reset ALL website lists (in all service sections)
    document.querySelectorAll('#websitesList').forEach(list => {
        list.innerHTML = `
            <div class="website-item">
                <input type="url" class="website-input" name="websites[]" placeholder="https://website.com">
                <button type="button" class="btn-remove-website" onclick="removeWebsite(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
    
    // Hide all service sections
    document.querySelectorAll('.service-section').forEach(s => s.style.display = 'none');
    const notice = document.querySelector('.services-notice');
    if (notice) notice.style.display = 'block';
    
    if (currentMarker) {
        map.removeLayer(currentMarker);
        currentMarker = null;
    }
    
    currentLocationId = null;
}

// Modal functionality
const modal = document.getElementById('locationModal');
const closeBtn = document.querySelector('.close');
if (closeBtn) closeBtn.onclick = () => modal.style.display = 'none';
window.onclick = (e) => {
    if (e.target === modal) modal.style.display = 'none';
};

// View location details
window.viewLocationDetails = function (index) {
    const location = locations[index];
    if (!location) return;
    currentLocationId = index;

    document.getElementById('modalTitle').textContent = location.name;
    document.getElementById('detailType').textContent = formatType(location.type);
    document.getElementById('detailCoords').textContent = `${location.lat}, ${location.lng}`;
    document.getElementById('detailAddress').textContent = location.address || 'Not specified';
    document.getElementById('detailHours').textContent = location.open_hours || 'Not specified';
    document.getElementById('detailContact').textContent = location.contact || 'Not specified';
    document.getElementById('deleteLocation').setAttribute('data-location-id', location.id || '');
    document.getElementById('editLocation').setAttribute('data-location-id', location.id || '');
    
    // Render websites
    const websitesEl = document.getElementById('detailWebsites');
    if (websitesEl) {
        websitesEl.innerHTML = '';
        const sites = (location.hotel_websites && location.hotel_websites.length > 0)
            ? location.hotel_websites
            : (location.url ? [location.url] : []);
        if (sites.length === 0) {
            websitesEl.textContent = 'Not specified';
        } else {
            const ul = document.createElement('ul');
            sites.forEach(s => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = s;
                a.target = '_blank';
                a.textContent = s;
                li.appendChild(a);
                ul.appendChild(li);
            });
            websitesEl.appendChild(ul);
        }
    }
    
    document.getElementById('detailDescription').textContent = location.description || 'No description available';

    // Display menu (restaurants only)
    const menuContent = document.getElementById('detailMenuContent');
    if (menuContent) {
        if (location.menu && location.menu.length > 0) {
            let html = '<h4>Menu</h4><table class="menu-table"><thead><tr><th>Item</th><th>Price</th></tr></thead><tbody>';
            location.menu.forEach(item => {
                html += `<tr><td>${item.name}</td><td>₱${parseFloat(item.price).toFixed(2)}</td></tr>`;
            });
            html += '</tbody></table>';
            menuContent.innerHTML = html;
        } else {
            menuContent.innerHTML = '';
        }
    }

    // Display highlights
    const photosGallery = document.getElementById('detailPhotos');
    if (photosGallery) {
        if (location.highlights && location.highlights.length > 0) {
            photosGallery.innerHTML = '<h4>Highlights</h4>';
            location.highlights.forEach(img => {
                const imgEl = document.createElement('img');
                imgEl.src = img.image_url;
                imgEl.alt = 'Highlight';
                photosGallery.appendChild(imgEl);
            });
        } else {
            photosGallery.innerHTML = '';
        }
    }

    if (modal) modal.style.display = 'block';
};

// Image Preview Handler for ALL file inputs
function setupImagePreviews() {
    const previewConfigs = [
        { inputId: 'foodPhotos', previewId: 'foodPhotosPreview' },
        { inputId: 'hotelPhotos', previewId: 'hotelPhotosPreview' },
        { inputId: 'touristPhotos', previewId: 'touristPhotosPreview' }
    ];

    previewConfigs.forEach(config => {
        const input = document.getElementById(config.inputId);
        const preview = document.getElementById(config.previewId);
        if (input && preview) {
            input.addEventListener('change', (e) => {
                displayImagePreviews(e.target.files, preview);
            });
        }
    });
}

// Display image previews with remove functionality
function displayImagePreviews(files, previewContainer) {
    if (!previewContainer || !files || files.length === 0) return;
    
    previewContainer.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            
            reader.onload = (ev) => {
                const imgWrapper = document.createElement('div');
                imgWrapper.className = 'preview-img-wrapper';
                
                const img = document.createElement('img');
                img.src = ev.target.result;
                img.alt = 'Preview image';
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'preview-remove-btn';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.onclick = function() {
                    imgWrapper.remove();
                };
                
                imgWrapper.appendChild(img);
                imgWrapper.appendChild(removeBtn);
                previewContainer.appendChild(imgWrapper);
            };
            
            reader.readAsDataURL(file);
        }
    });
}

// Initialize image previews on page load
document.addEventListener('DOMContentLoaded', setupImagePreviews);