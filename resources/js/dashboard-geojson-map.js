import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '../css/leaflet-map.css';
import '../css/dashboard-geojson-map.css';

const initializedBlocks = new WeakMap();

const COUNTRY_PALETTE = [
    {fill: '#93c5fd', stroke: '#3b82f6'},
    {fill: '#86efac', stroke: '#22c55e'},
    {fill: '#f9a8d4', stroke: '#ec4899'},
    {fill: '#fcd34d', stroke: '#f59e0b'},
    {fill: '#c4b5fd', stroke: '#8b5cf6'},
    {fill: '#67e8f9', stroke: '#06b6d4'},
];

const ACTIVE_STYLE = {
    color: '#f8fafc',
    weight: 2.2,
    fillOpacity: 0.66,
};

function parseConfig(block) {
    const configElement = block.querySelector('[data-dashboard-geojson-config]');

    if (!configElement) {
        return null;
    }

    try {
        return JSON.parse(configElement.textContent || '{}');
    } catch (error) {
        console.error('Dashboard GeoJSON map config is malformed.', error);
        return null;
    }
}

function hashCode(value) {
    return Array.from(String(value)).reduce((hash, character) => {
        return ((hash << 5) - hash) + character.charCodeAt(0);
    }, 0);
}

function paletteForCountry(code) {
    const paletteIndex = Math.abs(hashCode(code)) % COUNTRY_PALETTE.length;

    return COUNTRY_PALETTE[paletteIndex];
}

function baseStyleForCountry(code) {
    const palette = paletteForCountry(code);

    return {
        color: palette.stroke,
        weight: 1.1,
        opacity: 0.95,
        fillColor: palette.fill,
        fillOpacity: 0.38,
    };
}

function fetchJson(url) {
    return fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    }).then((response) => {
        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        return response.json();
    });
}

function setStatus(block, variant, title, text) {
    const status = block.querySelector('[data-dashboard-geojson-status]');
    const statusTitle = block.querySelector('[data-dashboard-geojson-status-title]');
    const statusText = block.querySelector('[data-dashboard-geojson-status-text]');
    const spinner = block.querySelector('[data-dashboard-geojson-spinner]');

    if (!status || !statusTitle || !statusText || !spinner) {
        return;
    }

    status.hidden = false;
    status.dataset.variant = variant;
    spinner.hidden = variant !== 'loading';
    statusTitle.textContent = title;
    statusText.textContent = text;

    if (variant === 'ready') {
        window.setTimeout(() => {
            if (status.dataset.variant === 'ready') {
                status.hidden = true;
            }
        }, 2000);
    }
}

function bindModal(block) {
    const modal = block.querySelector('[data-dashboard-geojson-modal]');
    const title = block.querySelector('[data-dashboard-geojson-modal-title]');
    const code = block.querySelector('[data-dashboard-geojson-modal-code]');
    const link = block.querySelector('[data-dashboard-geojson-modal-link]');
    const closeButtons = Array.from(block.querySelectorAll('[data-dashboard-geojson-modal-close]'));
    let previousActiveElement = null;

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        document.body.classList.remove('dashboard-geojson-map-modal-open');

        if (previousActiveElement instanceof HTMLElement) {
            previousActiveElement.focus();
        }
    };

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal && !modal.hidden) {
            closeModal();
        }
    });

    return {
        open(country) {
            if (!modal || !title || !code || !link) {
                return;
            }

            previousActiveElement = document.activeElement;
            title.textContent = country.name || country.code || 'Davlat';
            code.hidden = !country.code;
            code.textContent = country.code || '';

            if (country.countryUrl) {
                link.hidden = false;
                link.href = country.countryUrl;
            } else {
                link.hidden = true;
                link.removeAttribute('href');
            }

            modal.hidden = false;
            document.body.classList.add('dashboard-geojson-map-modal-open');
            requestAnimationFrame(() => {
                const closeButton = block.querySelector('.dashboard-geojson-map__modal-close');

                if (closeButton instanceof HTMLElement) {
                    closeButton.focus();
                }
            });
        },
        close: closeModal,
    };
}

function createMap(block, config) {
    const canvas = block.querySelector('[data-dashboard-geojson-map-canvas]');

    if (!canvas) {
        return null;
    }

    const center = Array.isArray(config.center) && config.center.length === 2
        ? config.center
        : [20, 0];
    const zoom = Number.isFinite(Number(config.zoom)) ? Number(config.zoom) : 2;
    const minZoom = Number.isFinite(Number(config.minZoom)) ? Number(config.minZoom) : 2;
    const maxZoom = Number.isFinite(Number(config.maxZoom)) ? Number(config.maxZoom) : 7;
    const tileUrl = config.tileUrl || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const tileAttribution = config.tileAttribution || '&copy; OpenStreetMap contributors';

    const map = L.map(canvas, {
        center,
        zoom,
        minZoom,
        maxZoom,
        zoomControl: true,
        scrollWheelZoom: true,
        attributionControl: true,
        preferCanvas: true,
        worldCopyJump: true,
    });

    L.tileLayer(tileUrl, {
        attribution: tileAttribution,
        maxZoom,
    }).addTo(map);

    return map;
}

function observeMap(block, map) {
    const resizeMap = () => map.invalidateSize({pan: false});

    if (typeof ResizeObserver !== 'undefined') {
        const resizeObserver = new ResizeObserver(() => resizeMap());
        resizeObserver.observe(block);
    }

    if (typeof IntersectionObserver !== 'undefined') {
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
    }

    window.addEventListener('resize', resizeMap, {passive: true});
    requestAnimationFrame(() => resizeMap());
}

function decorateCountryLayer(layer, country, map, modalController) {
    const initialStyle = baseStyleForCountry(country.code || country.name || '');

    layer.eachLayer((featureLayer) => {
        featureLayer.bindTooltip(country.name, {
            sticky: true,
            direction: 'top',
            className: 'dashboard-geojson-map__tooltip',
        });

        featureLayer.on({
            mouseover(event) {
                event.target.setStyle({
                    ...initialStyle,
                    ...ACTIVE_STYLE,
                });

                if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                    event.target.bringToFront();
                }
            },
            mouseout(event) {
                layer.resetStyle(event.target);
            },
            click(event) {
                const bounds = event.target.getBounds?.();

                if (bounds?.isValid?.()) {
                    map.fitBounds(bounds, {
                        padding: [28, 28],
                        maxZoom: 5,
                    });
                }

                modalController.open({
                    name: country.name,
                    code: country.code,
                    countryUrl: country.country_url || null,
                });
            },
        });
    });
}

function renderCountryLayer(map, modalController, country, geoJson) {
    const layer = L.geoJSON(geoJson, {
        style: () => baseStyleForCountry(country.code || country.name || ''),
    }).addTo(map);

    decorateCountryLayer(layer, country, map, modalController);
}

async function loadCountriesProgressively(block, map, modalController, countries) {
    let loadedCount = 0;
    let failedCount = 0;
    const concurrency = 8;
    let cursor = 0;

    const worker = async () => {
        while (cursor < countries.length) {
            const currentIndex = cursor;
            cursor += 1;
            const country = countries[currentIndex];

            setStatus(
                block,
                'loading',
                'Davlat qatlamlari yuklanmoqda',
                `${loadedCount + failedCount}/${countries.length} tayyor. Hozir: ${country.name}`,
            );

            try {
                const geoJson = await fetchJson(country.geojson_url);
                renderCountryLayer(map, modalController, country, geoJson);
                loadedCount += 1;
            } catch (error) {
                failedCount += 1;
                console.error(`Country GeoJSON could not be loaded: ${country.code}`, error);
            }
        }
    };

    await Promise.allSettled(
        Array.from({length: Math.min(concurrency, countries.length)}, () => worker())
    );

    if (loadedCount === 0) {
        setStatus(
            block,
            'error',
            'GeoJSON qatlamlari yuklanmadi',
            "Fayllarni o'qib bo'lmadi yoki papka bo'sh.",
        );
        return;
    }

    if (failedCount > 0) {
        setStatus(
            block,
            'warning',
            'Qisman yuklandi',
            `${loadedCount} ta davlat ko'rsatildi, ${failedCount} tasida xatolik bo'ldi.`,
        );
        return;
    }

    setStatus(
        block,
        'ready',
        'Xarita tayyor',
        `${loadedCount} ta davlat qatlamlari muvaffaqiyatli joylandi.`,
    );
}

async function buildGeoJsonMap(block) {
    if (initializedBlocks.has(block)) {
        return initializedBlocks.get(block);
    }

    const config = parseConfig(block);

    if (!config || !config.listUrl) {
        return null;
    }

    const map = createMap(block, config);

    if (!map) {
        return null;
    }

    observeMap(block, map);

    const modalController = bindModal(block);

    try {
        const payload = await fetchJson(config.listUrl);
        const countries = Array.isArray(payload?.data) ? payload.data : [];

        if (countries.length === 0) {
            setStatus(
                block,
                'empty',
                'GeoJSON fayllari topilmadi',
                "storage/geojson/countries papkasiga davlat fayllarini joylang.",
            );

            return null;
        }

        await loadCountriesProgressively(block, map, modalController, countries);
    } catch (error) {
        console.error('Dashboard GeoJSON map failed to initialize.', error);
        setStatus(
            block,
            'error',
            'Xaritani yuklab bo‘lmadi',
            "Dashboard GeoJSON endpointlarini yoki storage fayllarini tekshirib ko'ring.",
        );
    }

    const instance = {map};
    initializedBlocks.set(block, instance);

    return instance;
}

function initDashboardGeoJsonMaps() {
    document.querySelectorAll('[data-dashboard-geojson-map]').forEach((block) => {
        void buildGeoJsonMap(block);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardGeoJsonMaps, {once: true});
} else {
    initDashboardGeoJsonMaps();
}
