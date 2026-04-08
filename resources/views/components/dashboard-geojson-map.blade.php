@props([
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
    'height' => 460,
    'center' => [20, 0],
    'zoom' => 2,
    'minZoom' => 2,
    'maxZoom' => 7,
    'chips' => [],
    'listUrl',
    'tileUrl' => '',
    'tileAttribution' => '',
])

@php
    $mapId = 'dashboard-geojson-map-'.\Illuminate\Support\Str::uuid()->toString();
    $modalTitleId = $mapId.'-modal-title';
    $eyebrow = $eyebrow ?? __('ui.map.geojson.eyebrow');
    $title = $title ?? __('ui.map.geojson.title');
    $mapConfig = [
        'center' => $center,
        'zoom' => $zoom,
        'minZoom' => $minZoom,
        'maxZoom' => $maxZoom,
        'listUrl' => $listUrl,
        'collectionUrl' => $listUrl ? route('dashboard.map.countries.collection') : null,
        'tileUrl' => $tileUrl,
        'tileAttribution' => $tileAttribution,
        'i18n' => [
            'progress_loading' => __('ui.map.geojson.progress_loading'),
            'layers_success' => __('ui.map.geojson.layers_success'),
            'layers_partial' => __('ui.map.geojson.layers_partial'),
            'loading_detail' => __('ui.map.geojson.loading_detail'),
            'fallback_event' => __('ui.map.geojson.fallback_event'),
            'fallback_visit' => __('ui.map.geojson.fallback_visit'),
            'fallback_country' => __('ui.map.geojson.fallback_country'),
            'error_event' => __('ui.map.geojson.error_event'),
            'error_visit' => __('ui.map.geojson.error_visit'),
            'layer_loading_title' => __('ui.map.geojson.layer_loading_title'),
            'layer_error_title' => __('ui.map.geojson.layer_error_title'),
            'layer_error_text' => __('ui.map.geojson.layer_error_text'),
            'layer_partial_title' => __('ui.map.geojson.layer_partial_title'),
            'layer_ready_title' => __('ui.map.geojson.layer_ready_title'),
            'collection_loading_title' => __('ui.map.geojson.collection_loading_title'),
            'collection_loading_text' => __('ui.map.geojson.collection_loading_text'),
            'geojson_not_found' => __('ui.map.geojson.geojson_not_found'),
            'geojson_empty_hint' => __('ui.map.geojson.geojson_empty_hint'),
            'collection_ready_detail' => __('ui.map.geojson.collection_ready_detail'),
            'init_error_title' => __('ui.map.geojson.init_error_title'),
            'init_error_text' => __('ui.map.geojson.init_error_text'),
            'status_preparing_title' => __('ui.map.geojson.status_preparing_title'),
            'status_preparing_text' => __('ui.map.geojson.status_preparing_text'),
            'modal_event_empty' => __('ui.map.geojson.modal_event_empty'),
            'modal_visit_empty' => __('ui.map.geojson.modal_visit_empty'),
        ],
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
                <div class="leaflet-map-card__chips" aria-label="{{ __('ui.map.geojson.chips_aria') }}">
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
            aria-label="{{ $title ?: __('ui.map.geojson.canvas_aria') }}"
        ></div>

        <div class="dashboard-geojson-map__status" data-dashboard-geojson-status role="status" aria-live="polite" hidden>
            <span class="dashboard-geojson-map__status-spinner" data-dashboard-geojson-spinner aria-hidden="true" hidden></span>
            <div class="dashboard-geojson-map__status-copy">
                <strong data-dashboard-geojson-status-title>{{ __('ui.map.geojson.status_preparing_title') }}</strong>
                <span data-dashboard-geojson-status-text>{{ __('ui.map.geojson.status_preparing_text') }}</span>
            </div>
        </div>
    </div>

    <div class="dashboard-geojson-map__modal" data-dashboard-geojson-modal hidden>
        <button type="button" class="dashboard-geojson-map__modal-backdrop" data-dashboard-geojson-modal-close aria-label="{{ __('ui.map.geojson.modal_backdrop_aria') }}"></button>

        <div class="dashboard-geojson-map__modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalTitleId }}">
            <div class="dashboard-geojson-map__modal-head">
                <div class="dashboard-geojson-map__modal-copy">
                    <p class="dashboard-geojson-map__modal-eyebrow">{{ __('ui.map.geojson.modal_country_eyebrow') }}</p>
                    <h3 class="dashboard-geojson-map__modal-title" id="{{ $modalTitleId }}" data-dashboard-geojson-modal-title>{{ __('ui.map.geojson.modal_country_title_placeholder') }}</h3>
                </div>

                <button type="button" class="dashboard-geojson-map__modal-close" data-dashboard-geojson-modal-close aria-label="{{ __('ui.map.geojson.modal_close') }}">
                    <i class="material-icons" aria-hidden="true">close</i>
                </button>
            </div>

            <div class="dashboard-geojson-map__modal-meta">
                <span class="dashboard-geojson-map__modal-chip" data-dashboard-geojson-modal-code hidden></span>
            </div>

            <div class="dashboard-geojson-map__modal-body" data-dashboard-geojson-modal-body>
                <div class="dashboard-geojson-map__modal-grid" role="group" aria-label="{{ __('ui.map.geojson.modal_group_aria') }}">
                    <section class="dashboard-geojson-map__modal-panel" aria-label="{{ __('ui.map.geojson.modal_event_section_aria') }}">
                        <p class="dashboard-geojson-map__modal-panel-title">{{ __('ui.map.geojson.modal_event_title') }}</p>
                        <div class="dashboard-geojson-map__modal-panel-content" data-dashboard-geojson-event>
                            <p class="dashboard-geojson-map__modal-muted" data-dashboard-geojson-event-empty>{{ __('ui.map.geojson.loading_detail') }}</p>
                            <a class="dashboard-geojson-map__modal-item" href="#" data-dashboard-geojson-event-link hidden>
                                <img class="dashboard-geojson-map__modal-item-image" alt="" loading="lazy" data-dashboard-geojson-event-image hidden>
                                <div class="dashboard-geojson-map__modal-item-main">
                                    <strong data-dashboard-geojson-event-title></strong>
                                    <span data-dashboard-geojson-event-date></span>
                                </div>
                            </a>
                        </div>
                        <a class="btn btn--ghost dashboard-geojson-map__modal-action" href="{{ route('events.index') }}" data-dashboard-geojson-events-link>
                            {{ __('ui.map.geojson.modal_events_link') }}
                        </a>
                    </section>

                    <section class="dashboard-geojson-map__modal-panel" aria-label="{{ __('ui.map.geojson.modal_visit_section_aria') }}">
                        <p class="dashboard-geojson-map__modal-panel-title">{{ __('ui.map.geojson.modal_visit_title') }}</p>
                        <div class="dashboard-geojson-map__modal-panel-content" data-dashboard-geojson-visit>
                            <p class="dashboard-geojson-map__modal-muted" data-dashboard-geojson-visit-empty>{{ __('ui.map.geojson.loading_detail') }}</p>
                            <a class="dashboard-geojson-map__modal-item" href="#" data-dashboard-geojson-visit-link hidden>
                                <img class="dashboard-geojson-map__modal-item-image" alt="" loading="lazy" data-dashboard-geojson-visit-image hidden>
                                <div class="dashboard-geojson-map__modal-item-main">
                                    <strong data-dashboard-geojson-visit-title></strong>
                                    <span data-dashboard-geojson-visit-date></span>
                                </div>
                            </a>
                        </div>
                        <a class="btn btn--ghost dashboard-geojson-map__modal-action" href="{{ route('visits.index') }}" data-dashboard-geojson-visits-link>
                            {{ __('ui.map.geojson.modal_visits_link') }}
                        </a>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" data-dashboard-geojson-config>{!! json_encode($mapConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</section>
