import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '../css/leaflet-map.css';

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const initializedBlocks = new WeakMap();

function parseConfig(block) {
    const configElement = block.querySelector('[data-leaflet-config]');

    if (!configElement) {
        return null;
    }

    try {
        return JSON.parse(configElement.textContent || '{}');
    } catch (error) {
        console.error('Leaflet map config is malformed.', error);
        return null;
    }
}

function createPopupContent(marker) {
    const parts = [];

    if (marker.title) {
        parts.push(`<strong>${marker.title}</strong>`);
    }

    if (marker.description) {
        parts.push(`<span>${marker.description}</span>`);
    }

    return parts.join('<br>');
}

function buildMap(block) {
    if (initializedBlocks.has(block)) {
        return initializedBlocks.get(block);
    }

    const canvas = block.querySelector('[data-leaflet-map-canvas]');
    const config = parseConfig(block);

    if (!canvas || !config) {
        return null;
    }

    const center = Array.isArray(config.center) && config.center.length === 2
        ? config.center
        : [41.3111, 69.2797];
    const zoom = Number.isFinite(Number(config.zoom)) ? Number(config.zoom) : 5;
    const minZoom = Number.isFinite(Number(config.minZoom)) ? Number(config.minZoom) : 2;
    const maxZoom = Number.isFinite(Number(config.maxZoom)) ? Number(config.maxZoom) : 18;
    const markers = Array.isArray(config.markers) ? config.markers : [];
    const tileUrl = typeof config.tileUrl === 'string' && config.tileUrl.trim() !== ''
        ? config.tileUrl
        : null;
    const tileAttribution = typeof config.tileAttribution === 'string' ? config.tileAttribution : '';

    const map = L.map(canvas, {
        center,
        zoom,
        minZoom,
        zoomControl: true,
        scrollWheelZoom: true,
        attributionControl: true,
    });

    if (tileUrl) {
        L.tileLayer(tileUrl, {
            attribution: tileAttribution,
            maxZoom,
        }).addTo(map);
    }

    const bounds = [];

    markers.forEach((marker) => {
        const lat = Number(marker.lat);
        const lng = Number(marker.lng);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
        }

        const leafletMarker = L.marker([lat, lng]).addTo(map);
        bounds.push([lat, lng]);

        const popupContent = createPopupContent(marker);

        if (popupContent !== '') {
            leafletMarker.bindPopup(popupContent, {
                closeButton: false,
                className: 'leaflet-map-card__popup',
            });
        }

        if (marker.openPopup === true && popupContent !== '') {
            leafletMarker.openPopup();
        }
    });

    if (bounds.length > 1) {
        map.fitBounds(bounds, {
            padding: [32, 32],
            maxZoom: zoom,
        });
    } else if (bounds.length === 1) {
        map.setView(bounds[0], zoom);
    }

    const resizeMap = () => map.invalidateSize({ pan: false });
    const resizeObserver = new ResizeObserver(() => resizeMap());
    resizeObserver.observe(block);

    const intersectionObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                resizeMap();
            }
        });
    }, {
        threshold: 0.15,
    });
    intersectionObserver.observe(block);

    window.addEventListener('resize', resizeMap, { passive: true });
    requestAnimationFrame(() => resizeMap());

    const instance = { map, resizeObserver, intersectionObserver };
    initializedBlocks.set(block, instance);

    return instance;
}

function initLeafletMaps() {
    document.querySelectorAll('[data-leaflet-map]').forEach((block) => {
        buildMap(block);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLeafletMaps, { once: true });
} else {
    initLeafletMaps();
}
