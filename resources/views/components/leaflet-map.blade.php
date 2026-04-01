@props([
    'eyebrow' => 'Xarita moduli',
    'title' => 'Interaktiv xarita',
    'subtitle' => null,
    'height' => 380,
    'center' => [41.3111, 69.2797],
    'zoom' => 5,
    'minZoom' => 2,
    'maxZoom' => 18,
    'markers' => [],
    'chips' => [],
    'tileUrl' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    'tileAttribution' => '&copy; OpenStreetMap contributors',
])

@php
    $mapId = 'leaflet-map-'.\Illuminate\Support\Str::uuid()->toString();
    $mapConfig = [
        'center' => $center,
        'zoom' => $zoom,
        'minZoom' => $minZoom,
        'maxZoom' => $maxZoom,
        'markers' => $markers,
        'tileUrl' => $tileUrl,
        'tileAttribution' => $tileAttribution,
    ];
@endphp

@once
    @push('head')
        @vite('resources/js/leaflet-map.js')
    @endpush
@endonce

<section {{ $attributes->class(['leaflet-map-card']) }} data-leaflet-map>
    <div class="leaflet-map-card__head">
        <div class="leaflet-map-card__copy">
            @if ($eyebrow)
                <p class="leaflet-map-card__eyebrow">{{ $eyebrow }}</p>
            @endif

            <h2 class="leaflet-map-card__title">{{ $title }}</h2>

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

    <div class="leaflet-map-card__viewport" style="--leaflet-map-height: {{ (int) $height }}px;">
        <div
            id="{{ $mapId }}"
            class="leaflet-map-card__canvas"
            data-leaflet-map-canvas
            aria-label="{{ $title }}"
        ></div>
    </div>

    <script type="application/json" data-leaflet-config>{!! json_encode($mapConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</section>
