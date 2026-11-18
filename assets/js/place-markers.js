(() => {
    const placeMarkerIcons = new Map([
        ['restaurant', 'utensils'],
        ['hotels', 'bed'],
        ['touristdestination', 'camera'],
        ['cafe', 'mug-hot'],
        ['malls', 'bag-shopping'],
        ['default', 'location-dot']
    ]);
    const iconCache = new Map();

    function getPlaceMarkerKind(type) {
        const key = typeof type === 'string' ? type.replace(/[-_\s]/g, '').toLowerCase() : '';
        if (key === 'tourist' || key === 'touristdest') return 'touristdestination';
        if (key === 'hotel') return 'hotels';
        if (key === 'carinderia') return 'restaurant';
        return placeMarkerIcons.has(key) ? key : 'default';
    }

    function getIconForType(type) {
        const kind = getPlaceMarkerKind(type);
        if (!iconCache.has(kind)) {
            iconCache.set(kind, L.divIcon({
                className: `place-map-marker place-map-kind--${kind}`,
                html: `<span class="place-map-glyph" aria-hidden="true"><i class="fa-solid fa-${placeMarkerIcons.get(kind)}"></i></span>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15],
                popupAnchor: [0, -14],
                tooltipAnchor: [12, 0]
            }));
        }
        return iconCache.get(kind);
    }


    function bindPlaceMarkerLabel(marker, name, type) {
        const label = document.createElement('span');
        label.textContent = name;
        marker.bindTooltip(label, {
            permanent: true,
            direction: 'right',
            className: `location-label place-map-kind--${getPlaceMarkerKind(type)}`,
            offset: [4, 0],
            opacity: 1
        });
    }

    window.placeMapMarkers = { icon: getIconForType, bindLabel: bindPlaceMarkerLabel };
})();
