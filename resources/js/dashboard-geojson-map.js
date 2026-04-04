import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '../css/leaflet-map.css';
import '../css/dashboard-geojson-map.css';

const initializedBlocks = new WeakMap();
/** @type {Map<string, object>} Summary API javoblari — kalit sifatida to‘liq `summaryUrl` (davlatlararo aralashmaslik uchun). */
const lastCountrySummaryByKey = new Map();

/**
 * /countries/{code}/summary URL dan davlat kodini ajratib oladi.
 * @param {string|null|undefined} summaryUrl
 * @returns {string}
 */
function summaryCountryCodeFromSummaryUrl(summaryUrl) {
    if (!summaryUrl || typeof summaryUrl !== 'string') {
        return '';
    }

    const match = summaryUrl.match(/\/countries\/([^/]+)\/summary(?:\?|#|$)/i);

    return match ? decodeURIComponent(match[1]).trim().toUpperCase() : '';
}

/**
 * @param {{ summaryUrl?: string|null, code?: string, name?: string }} country
 * @returns {string}
 */
function summaryCacheKeyForCountry(country) {
    if (country.summaryUrl) {
        return country.summaryUrl.split('?')[0].split('#')[0];
    }

    const code = String(country.code || '').trim().toUpperCase();
    const name = String(country.name || '').trim();

    if (code) {
        return `code:${code}`;
    }

    if (name) {
        return `name:${name}`;
    }

    return '';
}

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

    if (variant === 'ready') {
        status.dataset.variant = variant;
        status.hidden = true;
        spinner.hidden = true;
        status.style.setProperty('display', 'none', 'important');

        return;
    }

    status.style.removeProperty('display');

    status.hidden = false;
    status.dataset.variant = variant;
    spinner.hidden = variant !== 'loading';
    statusTitle.textContent = title;
    statusText.textContent = text;
}

function bindModal(block) {
    const modal = block.querySelector('[data-dashboard-geojson-modal]');
    const title = block.querySelector('[data-dashboard-geojson-modal-title]');
    const code = block.querySelector('[data-dashboard-geojson-modal-code]');
    const eventsLink = block.querySelector('[data-dashboard-geojson-events-link]');
    const visitsLink = block.querySelector('[data-dashboard-geojson-visits-link]');
    const eventEmpty = block.querySelector('[data-dashboard-geojson-event-empty]');
    const eventLink = block.querySelector('[data-dashboard-geojson-event-link]');
    const eventTitle = block.querySelector('[data-dashboard-geojson-event-title]');
    const eventDate = block.querySelector('[data-dashboard-geojson-event-date]');
    const visitEmpty = block.querySelector('[data-dashboard-geojson-visit-empty]');
    const visitLink = block.querySelector('[data-dashboard-geojson-visit-link]');
    const visitTitle = block.querySelector('[data-dashboard-geojson-visit-title]');
    const visitDate = block.querySelector('[data-dashboard-geojson-visit-date]');
    const closeButtons = Array.from(block.querySelectorAll('[data-dashboard-geojson-modal-close]'));
    let previousActiveElement = null;
    let pendingSummary = null;
    let modalSummaryGeneration = 0;

    const emptyEventCopy = "Hozircha bu davlat uchun tadbirlar yo'q.";
    const emptyVisitCopy = "Hozircha bu davlat uchun tashriflar yo'q.";

    /** Matn qatori (yuklanmoqda / bo‘sh) yoki kartochka — `display:grid` CSS bilan `hidden` to‘qnashmasin. */
    const setEventRowMode = (mode) => {
        const showCard = mode === 'card';

        if (eventLink instanceof HTMLAnchorElement) {
            if (showCard) {
                eventLink.removeAttribute('hidden');
                eventLink.style.setProperty('display', 'grid', 'important');
            } else {
                eventLink.setAttribute('hidden', '');
                eventLink.style.setProperty('display', 'none', 'important');
            }
        }

        if (eventEmpty) {
            if (showCard) {
                eventEmpty.setAttribute('hidden', '');
                eventEmpty.style.setProperty('display', 'none', 'important');
            } else {
                eventEmpty.removeAttribute('hidden');
                eventEmpty.style.removeProperty('display');
            }
        }
    };

    const setVisitRowMode = (mode) => {
        const showCard = mode === 'card';

        if (visitLink instanceof HTMLAnchorElement) {
            if (showCard) {
                visitLink.removeAttribute('hidden');
                visitLink.style.setProperty('display', 'grid', 'important');
            } else {
                visitLink.setAttribute('hidden', '');
                visitLink.style.setProperty('display', 'none', 'important');
            }
        }

        if (visitEmpty) {
            if (showCard) {
                visitEmpty.setAttribute('hidden', '');
                visitEmpty.style.setProperty('display', 'none', 'important');
            } else {
                visitEmpty.removeAttribute('hidden');
                visitEmpty.style.removeProperty('display');
            }
        }
    };

    const normalizeRecord = (record) => {
        if (!record || typeof record !== 'object') {
            return null;
        }

        const id = record.id;

        if (id === undefined || id === null || id === '') {
            return null;
        }

        return record;
    };

    const resetSummaryPanelsLoading = () => {
        if (eventTitle) {
            eventTitle.textContent = '';
        }

        if (eventDate) {
            eventDate.textContent = '';
        }

        if (visitTitle) {
            visitTitle.textContent = '';
        }

        if (visitDate) {
            visitDate.textContent = '';
        }

        if (eventLink instanceof HTMLAnchorElement) {
            eventLink.removeAttribute('href');
        }

        if (visitLink instanceof HTMLAnchorElement) {
            visitLink.removeAttribute('href');
        }

        const eventImg = block.querySelector('[data-dashboard-geojson-event-image]');
        if (eventImg instanceof HTMLImageElement) {
            eventImg.hidden = true;
            eventImg.removeAttribute('src');
        }

        const visitImg = block.querySelector('[data-dashboard-geojson-visit-image]');
        if (visitImg instanceof HTMLImageElement) {
            visitImg.hidden = true;
            visitImg.removeAttribute('src');
        }

        setEventRowMode('message');
        setVisitRowMode('message');

        if (eventEmpty) {
            eventEmpty.textContent = "Ma'lumot yuklanmoqda...";
        }

        if (visitEmpty) {
            visitEmpty.textContent = "Ma'lumot yuklanmoqda...";
        }
    };

    const applySummaryToUi = (payload) => {
        const eventPayload = normalizeRecord(payload?.event);
        const visitPayload = normalizeRecord(payload?.visit);

        if (eventPayload && eventLink instanceof HTMLAnchorElement && eventTitle && eventDate) {
            eventTitle.textContent = eventPayload.title || 'Tadbir';
            eventDate.textContent = eventPayload.date || '';
            eventLink.href = eventPayload.url || '#';
            setEventRowMode('card');

            const imageUrl = eventPayload.image_url || null;
            const img = block.querySelector('[data-dashboard-geojson-event-image]');
            if (img instanceof HTMLImageElement) {
                if (imageUrl) {
                    img.hidden = false;
                    img.src = String(imageUrl);
                } else {
                    img.hidden = true;
                    img.removeAttribute('src');
                }
            }
        } else if (eventEmpty) {
            eventEmpty.textContent = emptyEventCopy;
            setEventRowMode('message');
            const eventImg = block.querySelector('[data-dashboard-geojson-event-image]');
            if (eventImg instanceof HTMLImageElement) {
                eventImg.hidden = true;
                eventImg.removeAttribute('src');
            }
        }

        if (visitPayload && visitLink instanceof HTMLAnchorElement && visitTitle && visitDate) {
            visitTitle.textContent = visitPayload.title || 'Tashrif';
            visitDate.textContent = visitPayload.date || '';
            visitLink.href = visitPayload.url || '#';
            setVisitRowMode('card');

            const imageUrl = visitPayload.image_url || null;
            const img = block.querySelector('[data-dashboard-geojson-visit-image]');
            if (img instanceof HTMLImageElement) {
                if (imageUrl) {
                    img.hidden = false;
                    img.src = String(imageUrl);
                } else {
                    img.hidden = true;
                    img.removeAttribute('src');
                }
            }
        } else if (visitEmpty) {
            visitEmpty.textContent = emptyVisitCopy;
            setVisitRowMode('message');
            const visitImg = block.querySelector('[data-dashboard-geojson-visit-image]');
            if (visitImg instanceof HTMLImageElement) {
                visitImg.hidden = true;
                visitImg.removeAttribute('src');
            }
        }

        if (payload?.country) {
            if (eventsLink instanceof HTMLAnchorElement && payload.country.events_url) {
                eventsLink.href = payload.country.events_url;
            }
            if (visitsLink instanceof HTMLAnchorElement && payload.country.visits_url) {
                visitsLink.href = payload.country.visits_url;
            }
        }
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }

        if (pendingSummary) {
            pendingSummary.abort();
            pendingSummary = null;
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
            if (!modal || !title || !code) {
                return;
            }

            const generationAtOpen = ++modalSummaryGeneration;

            previousActiveElement = document.activeElement;
            title.textContent = country.name || country.code || 'Davlat';
            code.hidden = !country.code;
            code.textContent = country.code || '';

            if (eventsLink instanceof HTMLAnchorElement) {
                eventsLink.href = country.eventsUrl || eventsLink.href;
            }

            if (visitsLink instanceof HTMLAnchorElement) {
                visitsLink.href = country.visitsUrl || visitsLink.href;
            }

            resetSummaryPanelsLoading();

            const summaryCacheKey = summaryCacheKeyForCountry(country);
            if (summaryCacheKey && lastCountrySummaryByKey.has(summaryCacheKey)) {
                applySummaryToUi(lastCountrySummaryByKey.get(summaryCacheKey));
            }

            modal.hidden = false;
            document.body.classList.add('dashboard-geojson-map-modal-open');
            requestAnimationFrame(() => {
                const closeButton = block.querySelector('.dashboard-geojson-map__modal-close');

                if (closeButton instanceof HTMLElement) {
                    closeButton.focus();
                }
            });

            if (!country.summaryUrl) {
                if (eventEmpty) {
                    eventEmpty.textContent = emptyEventCopy;
                }

                if (visitEmpty) {
                    visitEmpty.textContent = emptyVisitCopy;
                }

                return;
            }

            if (pendingSummary) {
                pendingSummary.abort();
            }

            const openedSummaryUrl = String(country.summaryUrl);
            const abortController = new AbortController();
            pendingSummary = abortController;

            fetch(openedSummaryUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: abortController.signal,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`Request failed with status ${response.status}`);
                    }
                    return response.json();
                })
                .then((payload) => {
                    if (generationAtOpen !== modalSummaryGeneration) {
                        return;
                    }

                    const expectedCode = summaryCountryCodeFromSummaryUrl(openedSummaryUrl);
                    const responseCode = String(payload?.country?.code || '').trim().toUpperCase();

                    if (expectedCode && responseCode && expectedCode !== responseCode) {
                        console.warn('Dashboard GeoJSON map: summary country mismatch', {
                            expectedCode,
                            responseCode,
                        });
                        applySummaryToUi({country: {code: expectedCode}, event: null, visit: null});
                        return;
                    }

                    if (summaryCacheKey) {
                        lastCountrySummaryByKey.set(summaryCacheKey, payload);
                    }

                    applySummaryToUi(payload);
                })
                .catch((error) => {
                    if (error?.name === 'AbortError') {
                        return;
                    }

                    console.error('Country summary could not be loaded.', error);

                    if (generationAtOpen !== modalSummaryGeneration) {
                        return;
                    }

                    if (!(summaryCacheKey && lastCountrySummaryByKey.has(summaryCacheKey))) {
                        if (eventEmpty) {
                            eventEmpty.textContent = "Tadbir ma'lumotini yuklab bo'lmadi.";
                        }

                        if (visitEmpty) {
                            visitEmpty.textContent = "Tashrif ma'lumotini yuklab bo'lmadi.";
                        }

                        setEventRowMode('message');
                        setVisitRowMode('message');
                    }
                })
                .finally(() => {
                    if (pendingSummary === abortController) {
                        pendingSummary = null;
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
                    summaryUrl: country.summary_url || null,
                    eventsUrl: country.events_url || null,
                    visitsUrl: country.visits_url || null,
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

function renderCountryCollectionLayer(map, modalController, geoJsonCollection) {
    const safeCollection = geoJsonCollection && typeof geoJsonCollection === 'object'
        ? geoJsonCollection
        : {type: 'FeatureCollection', features: []};

    const layer = L.geoJSON(safeCollection, {
        style: (feature) => baseStyleForCountry(feature?.properties?.code || feature?.properties?.name || ''),
        onEachFeature: (feature, featureLayer) => {
            const name = feature?.properties?.name || feature?.properties?.NAME || feature?.properties?.name_en || 'Davlat';
            const code = feature?.properties?.code || null;
            const countryUrl = feature?.properties?.country_url || null;
            const summaryUrl = feature?.properties?.summary_url || null;
            const eventsUrl = feature?.properties?.events_url || null;
            const visitsUrl = feature?.properties?.visits_url || null;

            featureLayer.bindTooltip(String(name), {
                sticky: true,
                direction: 'top',
                className: 'dashboard-geojson-map__tooltip',
            });

            const initialStyle = baseStyleForCountry(code || name || '');

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
                        name: String(name),
                        code: code ? String(code) : '',
                        countryUrl: countryUrl ? String(countryUrl) : null,
                        summaryUrl: summaryUrl ? String(summaryUrl) : null,
                        eventsUrl: eventsUrl ? String(eventsUrl) : null,
                        visitsUrl: visitsUrl ? String(visitsUrl) : null,
                    });
                },
            });
        },
    }).addTo(map);

    return layer;
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
        if (config.collectionUrl) {
            setStatus(
                block,
                'loading',
                'GeoJSON kolleksiyasi yuklanmoqda',
                'Davlatlar bittalab emas, bitta fayl orqali yuklanmoqda...',
            );

            const geoJsonCollection = await fetchJson(config.collectionUrl);
            const featureCount = Array.isArray(geoJsonCollection?.features) ? geoJsonCollection.features.length : 0;

            if (featureCount === 0) {
                setStatus(
                    block,
                    'empty',
                    'GeoJSON fayllari topilmadi',
                    "storage/geojson/countries papkasiga davlat fayllarini joylang.",
                );

                return null;
            }

            renderCountryCollectionLayer(map, modalController, geoJsonCollection);

            setStatus(
                block,
                'ready',
                'Xarita tayyor',
                `${featureCount} ta GeoJSON feature muvaffaqiyatli joylandi.`,
            );
        } else {
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
        }
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
