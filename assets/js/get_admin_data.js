document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('adminSearch');
    const results = document.getElementById('searchResults');
    const owner = document.getElementById('admin');
    const placeId = document.getElementById('placeId');
    const placesPanel = document.getElementById('ownerPlacesPanel');
    const placeSelect = document.getElementById('ownerPlaceSelect');
    const placeStatus = document.getElementById('ownerPlaceStatus');
    const newPlaceButton = document.getElementById('newOwnedPlaceButton');
    const saveButton = document.getElementById('saveLocation');
    const form = document.getElementById('locationForm');
    if (!form || !placeId) return;

    let timer;
    let adminRequest = null;
    let placesRequest = null;
    let ownerPlaces = [];
    let editorRequest = null;

    function closeResults() {
        clearTimeout(timer);
        adminRequest?.abort();
        adminRequest = null;
        results.style.display = 'none';
    }

    function closeOwnerPlaces() {
        placesRequest?.abort();
        placesRequest = null;
        ownerPlaces = [];
        placesPanel.hidden = true;
        placesPanel.removeAttribute('aria-busy');
        placeSelect.replaceChildren(new Option('Choose a place to load', ''));
        placeSelect.disabled = true;
        placeStatus.textContent = '';
    }

    function setEditMode(isEditing) {
        if (!saveButton) return;
        saveButton.innerHTML = isEditing
            ? '<i class="fas fa-save" aria-hidden="true"></i> Update Location'
            : '<i class="fas fa-save" aria-hidden="true"></i> Save Location';
    }

    function resetEditor(selectedOwner = '', selectedOwnerName = '') {
        if (typeof window.clearForm === 'function') {
            window.clearForm();
        } else {
            form?.reset();
        }
        if (owner) owner.value = selectedOwner;
        if (input) input.value = selectedOwnerName;
        if (placeId) placeId.value = '';
        setEditMode(false);
        const saveStatus = document.getElementById('locationSaveStatus');
        if (saveStatus) saveStatus.textContent = '';
    }

    function renderPlaceOptions(selectedId = '') {
        const placeholder = ownerPlaces.length
            ? 'Choose a place to load'
            : 'No saved places found';
        placeSelect.replaceChildren(new Option(placeholder, ''));

        for (const place of ownerPlaces) {
            const type = place.type ? ` (${place.type.replaceAll('_', ' ')})` : '';
            placeSelect.add(new Option(`${place.name || 'Untitled place'}${type}`, String(place.id)));
        }

        placeSelect.disabled = ownerPlaces.length === 0;
        if (selectedId) placeSelect.value = String(selectedId);
    }

    function renderWebsiteInputs(container, websites) {
        if (!container) return;
        container.replaceChildren();
        const values = websites.length ? websites : [''];

        for (const value of values) {
            const row = document.createElement('div');
            row.className = 'website-item';

            const input = document.createElement('input');
            input.type = 'url';
            input.className = 'website-input';
            input.name = 'websites[]';
            input.setAttribute('aria-label', 'Website or social media URL');
            input.placeholder = 'https://example.com';
            input.value = value;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn-remove-website';
            remove.setAttribute('aria-label', 'Remove website');
            remove.innerHTML = '<i class="fas fa-trash" aria-hidden="true"></i>';
            remove.addEventListener('click', () => window.removeWebsite(remove));

            row.append(input, remove);
            container.append(row);
        }
    }

    function renderMenuItems(menu) {
        const list = document.getElementById('menuList');
        if (!list) return;
        list.replaceChildren();
        const items = menu.length ? menu : [{ name: '', price: '' }];

        for (const item of items) {
            const row = document.createElement('div');
            row.className = 'menu-item';

            const name = document.createElement('input');
            name.type = 'text';
            name.name = 'menu_name[]';
            name.className = 'menu-name';
            name.placeholder = 'Item name';
            name.setAttribute('aria-label', 'Menu item name');
            name.value = item.name || '';

            const price = document.createElement('input');
            price.type = 'number';
            price.name = 'menu_price[]';
            price.className = 'menu-price';
            price.step = '0.01';
            price.placeholder = 'Price';
            price.setAttribute('aria-label', 'Menu item price');
            price.value = item.price ?? '';

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn-remove';
            remove.setAttribute('aria-label', 'Remove menu item');
            remove.innerHTML = '<i class="fas fa-trash" aria-hidden="true"></i>';
            remove.addEventListener('click', () => window.removeMenuItem(remove));

            row.append(name, price, remove);
            list.append(row);
        }
    }

    function renderExistingPhotos(place) {
        const typeToPhotoId = {
            restaurant: 'foodPhotos',
            hotels: 'hotelPhotos',
            tourist_destination: 'touristPhotos'
        };
        const photoId = typeToPhotoId[place.type];
        const preview = photoId && document.getElementById(`${photoId}Preview`);
        if (!preview) return;

        for (const photo of place.highlights || []) {
            if (!photo.image_url) continue;
            const wrapper = document.createElement('div');
            wrapper.className = 'preview-img-wrapper existing-photo-preview';
            const image = document.createElement('img');
            image.src = photo.image_url;
            image.alt = 'Saved place photo';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'preview-remove-btn';
            remove.setAttribute('aria-label', 'Remove saved photo on update');
            remove.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';
            remove.addEventListener('click', () => {
                const field = document.createElement('input');
                field.type = 'hidden';
                field.name = 'removed_highlights[]';
                field.value = photo.id;
                form.append(field);
                wrapper.remove();
            });
            wrapper.append(image, remove);
            preview.append(wrapper);
        }
    }

    function setMapMarker(place) {
        const map = window.GIS?.map;
        const lat = Number(place.lat);
        const lng = Number(place.lng);
        if (!map || !Number.isFinite(lat) || !Number.isFinite(lng)) return;

        const currentMarker = window.GIS.getCurrentMarker?.();
        if (currentMarker && map.hasLayer(currentMarker)) map.removeLayer(currentMarker);

        const markerOptions = { draggable: true };
        if (typeof window.getIconForType === 'function') {
            markerOptions.icon = window.getIconForType(place.type);
        }

        const marker = L.marker([lat, lng], markerOptions).addTo(map);
        marker.on('dragend', async event => {
            const position = event.target.getLatLng();
            document.getElementById('latitude').value = position.lat.toFixed(8);
            document.getElementById('longitude').value = position.lng.toFixed(8);
            if (typeof window.reverseGeocode === 'function') {
                await window.reverseGeocode(position.lat, position.lng);
            }
        });

        window.GIS.setCurrentMarker?.(marker);
        map.setView([lat, lng], Math.max(map.getZoom(), 16));
    }

    function loadPlace(place, fromOwnerPicker = false, showEditMarker = true) {
        const currentPlaces = ownerPlaces;
        const selectedOwner = fromOwnerPicker ? owner.value : '';
        const selectedOwnerName = fromOwnerPicker ? input.value : place.owner_name;
        resetEditor(selectedOwner, selectedOwnerName);
        ownerPlaces = currentPlaces;
        if (fromOwnerPicker) {
            placesPanel.hidden = false;
            renderPlaceOptions(place.id);
        } else if (input) {
            owner.value = place.admin_id ? String(place.admin_id) : '';
        }

        document.getElementById('placeId').value = place.id;
        document.getElementById('locationName').value = place.name || '';
        document.getElementById('locationType').value = place.type || '';
        document.getElementById('latitude').value = place.lat ?? '';
        document.getElementById('longitude').value = place.lng ?? '';
        document.getElementById('address').value = place.address || '';
        document.getElementById('openHours').value = place.open_hours || '';
        document.getElementById('contact').value = place.contact || '';
        document.getElementById('description').value = place.description || '';

        const blurToggle = document.getElementById('blurServicesToggle');
        if (blurToggle) blurToggle.checked = Number(place.blur_services) === 1;
        document.getElementById('locationType').dispatchEvent(new Event('change', { bubbles: true }));

        const serviceSections = {
            restaurant: 'foodServices',
            hotels: 'hotelServices',
            tourist_destination: 'touristServices'
        };
        const serviceSection = document.getElementById(serviceSections[place.type]);
        renderWebsiteInputs(
            serviceSection?.querySelector('[data-website-list], #websitesList'),
            place.hotel_websites || []
        );
        renderMenuItems(place.type === 'restaurant' ? (place.menu || []) : []);

        const about = place.place_about || {};
        for (const input of document.querySelectorAll('input[name="amenities[]"], input[name="house_rules[]"]')) {
            const group = input.name === 'amenities[]' ? 'amenities' : 'house_rules';
            input.checked = Array.isArray(about[group]) && about[group].includes(input.value);
        }

        renderExistingPhotos(place);
        if (showEditMarker) setMapMarker(place);
        setEditMode(true);
        if (fromOwnerPicker) placeStatus.textContent = 'Place loaded. Edit any details, then choose Update Location to save.';
    }

    async function fetchEditablePlace(id, signal) {
        const response = await fetch(`../controllers/admin/get_admin_places.php?place_id=${encodeURIComponent(id)}`, { signal });
        const data = await response.json();
        if (!response.ok || !data.success || !data.items?.[0]) {
            throw new Error(data.message || 'Could not load the location.');
        }
        return data.items[0];
    }

    window.openLocationEditor = async (id, { fromMap = false } = {}) => {
        editorRequest?.abort();
        editorRequest = null;
        if (id == null) return;
        if (placeId.value === String(id)) {
            if (!fromMap) focusLocationEditor();
            return;
        }
        const controller = new AbortController();
        editorRequest = controller;
        setLocationSaveStatus('Loading location…');
        try {
            const place = await fetchEditablePlace(id, controller.signal);
            if (editorRequest !== controller) return;
            editorRequest = null;
            loadPlace(place, false, !fromMap);
            if (fromMap) setLocationSaveStatus(`Editing ${place.name}`);
            else focusLocationEditor();
        } catch (error) {
            if (editorRequest !== controller) return;
            editorRequest = null;
            if (!fromMap) closeModal();
            setLocationSaveStatus(error.message, true);
        }
    };

    function focusLocationEditor() {
        closeModal();
        document.querySelector('.tab-btn[data-tab="overview"]')?.click();
        document.getElementById('locationName').focus();
    }

    window.refreshEditedLocation = async id => {
        const place = await fetchEditablePlace(id);
        window.GIS.updateEditedLocation(place);
        ownerPlaces = ownerPlaces.map(item => item.id === place.id ? place : item);
        if (placeId.value === String(id) && !editorRequest) {
            loadPlace(place, Boolean(placeSelect?.value === String(id)));
        }
    };

    form.addEventListener('reset', () => {
        editorRequest?.abort();
        editorRequest = null;
        setEditMode(false);
    });
    const linkedPlace = new URLSearchParams(window.location.search).get('edit');
    if (linkedPlace) window.openLocationEditor(linkedPlace);
    if (!input || !results || !owner || !placesPanel || !placeSelect || !placeStatus || !newPlaceButton) return;

    async function loadAdminPlaces(adminId) {
        placesRequest?.abort();
        const controller = new AbortController();
        placesRequest = controller;
        placesPanel.hidden = false;
        placesPanel.setAttribute('aria-busy', 'true');
        placeSelect.disabled = true;
        placeSelect.replaceChildren(new Option('Loading places…', ''));
        placeStatus.textContent = '';

        try {
            const response = await fetch(
                `../controllers/admin/get_admin_places.php?admin_id=${encodeURIComponent(adminId)}`,
                { signal: controller.signal }
            );
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to load places');
            }
            if (placesRequest !== controller) return;

            placesRequest = null;
            placesPanel.removeAttribute('aria-busy');
            ownerPlaces = data.items || [];
            renderPlaceOptions();

            if (ownerPlaces.length === 0) {
                placeStatus.textContent = 'This admin has no saved places yet. You can create one below.';
            } else if (ownerPlaces.length === 1) {
                loadPlace(ownerPlaces[0], true);
            } else {
                placeStatus.textContent = `${ownerPlaces.length} places found. Choose one to load its details.`;
            }
        } catch (error) {
            if (error.name === 'AbortError') return;
            if (placesRequest === controller) {
                placesRequest = null;
                placesPanel.removeAttribute('aria-busy');
                placeSelect.replaceChildren(new Option('Places could not be loaded', ''));
                placeSelect.disabled = true;
                placeStatus.textContent = error.message || 'Could not load places. Try again.';
            }
        }
    }

    async function searchOwners(term) {
        adminRequest?.abort();
        const controller = new AbortController();
        adminRequest = controller;

        try {
            const response = await fetch(
                `../controllers/admin/get_admins.php?search=${encodeURIComponent(term)}&page=1`,
                { signal: controller.signal }
            );
            if (!response.ok) throw new Error('Unable to load store admins');
            const { items } = await response.json();
            if (adminRequest !== controller) return;

            results.replaceChildren();
            for (const admin of items) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'search-result-item';
                button.textContent = admin.text;
                button.addEventListener('click', () => {
                    if (placeId?.value) resetEditor();
                    closeOwnerPlaces();
                    if (owner instanceof HTMLSelectElement && ![...owner.options].some(option => option.value === String(admin.id))) {
                        owner.add(new Option(admin.text, admin.id));
                    }
                    owner.value = admin.id;
                    input.value = admin.text;
                    input.setCustomValidity('');
                    closeResults();
                    loadAdminPlaces(admin.id);
                    input.focus();
                });
                results.append(button);
            }
            if (items.length === 0) results.textContent = 'No matching store admins.';
            results.style.display = 'block';
        } catch (error) {
            if (error.name === 'AbortError') return;
            results.textContent = 'Could not load store admins. Try again.';
            results.style.display = 'block';
        }
    }

    input.addEventListener('input', () => {
        const term = input.value.trim();
        owner.value = '';
        if (placeId?.value) resetEditor('', input.value);
        closeOwnerPlaces();
        closeResults();
        input.value = term;
        input.setCustomValidity(term ? 'Choose a store admin from the results or clear this field.' : '');
        if (term) timer = setTimeout(() => searchOwners(term), 220);
    });

    placeSelect.addEventListener('change', () => {
        const selectedPlace = ownerPlaces.find(place => String(place.id) === placeSelect.value);
        if (selectedPlace) loadPlace(selectedPlace, true);
    });

    newPlaceButton.addEventListener('click', () => {
        const selectedOwner = owner.value;
        const selectedOwnerName = input.value;
        resetEditor(selectedOwner, selectedOwnerName);
        placesPanel.hidden = false;
        renderPlaceOptions();
        placeStatus.textContent = 'New place for this admin. Enter the details and save when ready.';
        document.getElementById('locationName')?.focus();
    });

    form?.addEventListener('reset', () => {
        if (owner.type === 'hidden') owner.defaultValue = '';
        input.setCustomValidity('');
        closeResults();
        closeOwnerPlaces();
        setEditMode(false);
    });

    document.addEventListener('click', event => {
        if (event.target !== input && !results.contains(event.target)) closeResults();
    });

    input.parentElement.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeResults();
            input.focus();
        } else if (event.key === 'ArrowDown' && event.target === input && results.style.display !== 'none') {
            event.preventDefault();
            results.querySelector('button')?.focus();
        }
    });
});
