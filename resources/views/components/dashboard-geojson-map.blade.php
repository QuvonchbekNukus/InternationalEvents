@props([
    'eyebrow' => 'Global hamkorlik xaritasi',
    'title' => 'GeoJSON world map',
    'subtitle' => null,
    'height' => 460,
    'center' => [20, 0],
    'zoom' => 2,
    'minZoom' => 2,
    'maxZoom' => 7,
    'chips' => [],
    'listUrl',
    'tileUrl' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    'tileAttribution' => '&copy; OpenStreetMap contributors',
])

@php
    $mapId = 'dashboard-geojson-map-'.\Illuminate\Support\Str::uuid()->toString();
    $modalTitleId = $mapId.'-modal-title';
    $mapConfig = [
        'center' => $center,
        'zoom' => $zoom,
        'minZoom' => $minZoom,
        'maxZoom' => $maxZoom,
        'listUrl' => $listUrl,
        'collectionUrl' => $listUrl ? route('dashboard.map.countries.collection') : null,
        'tileUrl' => $tileUrl,
        'tileAttribution' => $tileAttribution,
    ];
@endphp

@once
    @push('head')
        @vite('resources/js/dashboard-geojson-map.js')
    @endpush
@endonce

<section {{ $attributes->class(['leaflet-map-card', 'dashboard-geojson-map']) }} data-dashboard-geojson-map>
    @if ($eyebrow || $title || $subtitle || !empty($chips))
        <div class="leaflet-map-card__head">
            <div class="leaflet-map-card__copy">
                @if ($eyebrow)
                    <p class="leaflet-map-card__eyebrow">{{ $eyebrow }}</p>
                @endif

                @if ($title)
                    <h2 class="leaflet-map-card__title">{{ $title }}</h2>
                @endif

                @if ($subtitle)
                    <p class="leaflet-map-card__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @if (!empty($chips))
                <div class="leaflet-map-card__chips" aria-label="Xarita meta ma'lumotlari">
                    @foreach ($chips as $chip)
                        <span class="leaflet-map-card__chip">{{ $chip }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="leaflet-map-card__viewport" style="--leaflet-map-height: {{ (int) $height }}px;">
        <div
            id="{{ $mapId }}"
            class="leaflet-map-card__canvas dashboard-geojson-map__canvas"
            data-dashboard-geojson-map-canvas
            aria-label="{{ $title ?: 'Davlatlar xaritasi' }}"
        ></div>

        <div class="dashboard-geojson-map__status" data-dashboard-geojson-status role="status" aria-live="polite">
            <span class="dashboard-geojson-map__status-spinner" data-dashboard-geojson-spinner aria-hidden="true"></span>
            <div class="dashboard-geojson-map__status-copy">
                <strong data-dashboard-geojson-status-title>Davlat qatlamlari tayyorlanmoqda</strong>
                <span data-dashboard-geojson-status-text>GeoJSON fayllar serverdan olinib, xaritaga bosqichma-bosqich joylanadi.</span>
            </div>
        </div>

        <div class="dashboard-geojson-map__legend" aria-label="Xarita legendasi">
            <span class="dashboard-geojson-map__legend-item">
                <span class="dashboard-geojson-map__legend-swatch"></span>
                <span>Davlat hududi</span>
            </span>
            <span class="dashboard-geojson-map__legend-item">
                <span class="dashboard-geojson-map__legend-swatch dashboard-geojson-map__legend-swatch--active"></span>
                <span>Tanlangan hudud</span>
            </span>
        </div>
    </div>

    <div class="dashboard-geojson-map__modal" data-dashboard-geojson-modal hidden>
        <button type="button" class="dashboard-geojson-map__modal-backdrop" data-dashboard-geojson-modal-close aria-label="Modal oynani yopish"></button>

        <div class="dashboard-geojson-map__modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalTitleId }}">
            <div class="dashboard-geojson-map__modal-head">
                <div class="dashboard-geojson-map__modal-copy">
                    <p class="dashboard-geojson-map__modal-eyebrow">Test modal oynasi</p>
                    <h3 class="dashboard-geojson-map__modal-title" id="{{ $modalTitleId }}" data-dashboard-geojson-modal-title>Davlat nomi</h3>
                    <p class="dashboard-geojson-map__modal-text">
                        Tanlangan GeoJSON qatlamining nomi shu yerda ko‘rsatiladi.
                    </p>
                </div>

                <button type="button" class="dashboard-geojson-map__modal-close" data-dashboard-geojson-modal-close aria-label="Yopish">
                    <i class="material-icons" aria-hidden="true">close</i>
                </button>
            </div>

            <div class="dashboard-geojson-map__modal-meta">
                <span class="dashboard-geojson-map__modal-chip" data-dashboard-geojson-modal-code hidden></span>
                <a class="dashboard-geojson-map__modal-link" href="#" data-dashboard-geojson-modal-link hidden>
                    Davlat sahifasini ochish
                </a>
            </div>

            <div class="dashboard-geojson-map__modal-body" data-dashboard-geojson-modal-body>
                <div class="dashboard-geojson-map__modal-grid" role="group" aria-label="Davlat bo'yicha yaqin tadbirlar va tashriflar">
                    <section class="dashboard-geojson-map__modal-panel" aria-label="Yaqin tadbir">
                        <p class="dashboard-geojson-map__modal-panel-title">Yaqin tadbir</p>
                        <div class="dashboard-geojson-map__modal-panel-content" data-dashboard-geojson-event>
                            <p class="dashboard-geojson-map__modal-muted" data-dashboard-geojson-event-empty>Ma'lumot yuklanmoqda...</p>
                            <a class="dashboard-geojson-map__modal-item" href="#" data-dashboard-geojson-event-link hidden>
                                <img class="dashboard-geojson-map__modal-item-image" alt="" loading="lazy" data-dashboard-geojson-event-image hidden>
                                <strong data-dashboard-geojson-event-title></strong>
                                <span data-dashboard-geojson-event-date></span>
                            </a>
                        </div>
                        <a class="btn btn--ghost dashboard-geojson-map__modal-action" href="{{ route('events.index') }}" data-dashboard-geojson-events-link>
                            Barcha tadbirlar
                        </a>
                    </section>

                    <section class="dashboard-geojson-map__modal-panel" aria-label="Yaqin tashrif">
                        <p class="dashboard-geojson-map__modal-panel-title">Yaqin tashrif</p>
                        <div class="dashboard-geojson-map__modal-panel-content" data-dashboard-geojson-visit>
                            <p class="dashboard-geojson-map__modal-muted" data-dashboard-geojson-visit-empty>Ma'lumot yuklanmoqda...</p>
                            <a class="dashboard-geojson-map__modal-item" href="#" data-dashboard-geojson-visit-link hidden>
                                <img class="dashboard-geojson-map__modal-item-image" alt="" loading="lazy" data-dashboard-geojson-visit-image hidden>
                                <strong data-dashboard-geojson-visit-title></strong>
                                <span data-dashboard-geojson-visit-date></span>
                            </a>
                        </div>
                        <a class="btn btn--ghost dashboard-geojson-map__modal-action" href="{{ route('visits.index') }}" data-dashboard-geojson-visits-link>
                            Barcha tashriflar
                        </a>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" data-dashboard-geojson-config>{!! json_encode($mapConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</section>
