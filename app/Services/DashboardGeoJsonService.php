<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Document;
use App\Models\Event;
use App\Models\Visit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;
use SplFileInfo;

class DashboardGeoJsonService
{
    private ?Collection $countryLookupCache = null;

    /**
     * @return list<array<string, mixed>>
     */
    public function listCountries(): array
    {
        return $this->countryFiles()
            ->map(function (SplFileInfo $file): array {
                $countryCode = $this->countryCodeFromFilename($file->getFilename());
                $geoJson = $this->decodeGeoJsonFile($file->getPathname());
                $country = $this->countryLookup()->get($countryCode);

                return [
                    'code' => $countryCode,
                    'name' => $this->resolveCountryName($geoJson, $countryCode, $country?->display_name),
                    'file_name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'geojson_url' => route('dashboard.map.countries.show', ['country' => $countryCode]),
                    'summary_url' => route('dashboard.map.countries.summary', ['country' => $countryCode]),
                    'country_url' => $country ? route('countries.show', $country) : null,
                    'events_url' => $country ? route('events.index', ['country_id' => $country->id]) : route('events.index'),
                    'visits_url' => $country ? route('visits.index', ['country_id' => $country->id]) : route('visits.index'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function loadCountryGeoJson(string $countryCode): ?array
    {
        $file = $this->findCountryFile($countryCode);

        if ($file === null) {
            return null;
        }

        $resolvedCountryCode = $this->countryCodeFromFilename($file->getFilename());
        $country = $this->countryLookup()->get($resolvedCountryCode);
        $geoJson = $this->decodeGeoJsonFile($file->getPathname());

        $geoJson['metadata'] = [
            'code' => $resolvedCountryCode,
            'name' => $this->resolveCountryName($geoJson, $resolvedCountryCode, $country?->display_name),
            'country_url' => $country ? route('countries.show', $country) : null,
        ];

        return $geoJson;
    }

    /**
     * @return array{type: 'FeatureCollection', features: list<array<string, mixed>>}
     */
    public function loadCountryCollectionGeoJson(): array
    {
        $files = $this->countryFiles();

        if ($files->isEmpty()) {
            return [
                'type' => 'FeatureCollection',
                'features' => [],
            ];
        }

        $signature = $files
            ->map(fn (SplFileInfo $file): string => $file->getFilename().'|'.$file->getMTime().'|'.$file->getSize())
            ->implode(';');
        $cacheKey = 'dashboard.geojson.countries.collection.v2.'.sha1($signature);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($files): array {
            $features = [];
            $lookup = $this->countryLookup();

            foreach ($files as $file) {
                $countryCode = $this->countryCodeFromFilename($file->getFilename());
                $geoJson = $this->decodeGeoJsonFile($file->getPathname());
                $country = $lookup->get($countryCode);
                $name = $this->resolveCountryName($geoJson, $countryCode, $country?->display_name);
                $countryUrl = $country ? route('countries.show', $country) : null;
                $summaryUrl = route('dashboard.map.countries.summary', ['country' => $countryCode]);
                $eventsUrl = $country ? route('events.index', ['country_id' => $country->id]) : route('events.index');
                $visitsUrl = $country ? route('visits.index', ['country_id' => $country->id]) : route('visits.index');
                $countryId = $country?->id;

                $fileFeatures = is_array($geoJson['features'] ?? null) ? $geoJson['features'] : [];

                foreach ($fileFeatures as $feature) {
                    if (! is_array($feature)) {
                        continue;
                    }

                    $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
                    $properties = array_merge($properties, [
                        'code' => $countryCode,
                        'name' => $name,
                        'country_url' => $countryUrl,
                        'summary_url' => $summaryUrl,
                        'events_url' => $eventsUrl,
                        'visits_url' => $visitsUrl,
                        'country_id' => $countryId,
                    ]);

                    $feature['properties'] = $properties;
                    $features[] = $feature;
                }
            }

            return [
                'type' => 'FeatureCollection',
                'features' => $features,
            ];
        });
    }

    /**
     * @return array{
     *   country: array{code: string, id: int|null, name: string, country_url: string|null, events_url: string, visits_url: string},
     *   event: array{id: int, title: string, date: string|null, url: string}|null,
     *   visit: array{id: int, title: string, date: string|null, url: string}|null
     * }
     */
    public function countryDashboardSummary(string $countryCode): array
    {
        $normalized = mb_strtoupper(trim($countryCode));
        $country = $this->countryLookup()->get($normalized);

        $countryId = $country?->id;
        $eventsUrl = $country ? route('events.index', ['country_id' => $country->id]) : route('events.index');
        $visitsUrl = $country ? route('visits.index', ['country_id' => $country->id]) : route('visits.index');
        $countryUrl = $country ? route('countries.show', $country) : null;

        $event = null;
        $visit = null;

        if ($countryId) {
            $eventModel = Event::query()
                ->where('country_id', $countryId)
                ->orderByDesc('created_at')
                ->first();

            if ($eventModel) {
                $eventImageUrl = $this->latestImageForRelation(eventId: (int) $eventModel->id);
                $event = [
                    'id' => (int) $eventModel->id,
                    'title' => (string) ($eventModel->display_title ?: 'Tadbir'),
                    'date' => $eventModel->start_datetime?->toDateTimeString(),
                    'url' => route('events.show', $eventModel),
                    'image_url' => $eventImageUrl,
                ];
            }

            $visitModel = Visit::query()
                ->where('country_id', $countryId)
                ->orderByDesc('created_at')
                ->first();

            if ($visitModel) {
                $visitImageUrl = $this->latestImageForRelation(visitId: (int) $visitModel->id);
                $visit = [
                    'id' => (int) $visitModel->id,
                    'title' => (string) ($visitModel->display_title ?: 'Tashrif'),
                    'date' => $visitModel->start_date?->toDateString(),
                    'url' => route('visits.show', $visitModel),
                    'image_url' => $visitImageUrl,
                ];
            }
        }

        $fallbackName = $country?->display_name;
        $name = $fallbackName ?: ($normalized !== '' ? $normalized : 'Davlat');

        return [
            'country' => [
                'code' => $normalized,
                'id' => $countryId,
                'name' => $name,
                'country_url' => $countryUrl,
                'events_url' => $eventsUrl,
                'visits_url' => $visitsUrl,
            ],
            'event' => $event,
            'visit' => $visit,
        ];
    }

    private function latestImageForRelation(?int $eventId = null, ?int $visitId = null): ?string
    {
        $query = Document::query()
            ->where('status', 'faol')
            ->where('is_confidential', false);

        if ($eventId !== null) {
            $query->where('event_id', $eventId);
        }

        if ($visitId !== null) {
            $query->where('visit_id', $visitId);
        }

        /** @var Document|null $document */
        $document = $query
            ->orderByDesc('created_at')
            ->get(['id', 'file_path', 'file_ext', 'mime_type'])
            ->first(fn (Document $doc): bool => $doc->is_image && filled($doc->file_url));

        return $document?->file_url;
    }

    private function findCountryFile(string $countryCode): ?SplFileInfo
    {
        $normalizedCountryCode = mb_strtoupper(trim($countryCode));

        return $this->countryFiles()
            ->first(fn (SplFileInfo $file): bool => $this->countryCodeFromFilename($file->getFilename()) === $normalizedCountryCode);
    }

    /**
     * @return Collection<int, SplFileInfo>
     */
    private function countryFiles(): Collection
    {
        $directory = (string) config('dashboard.geojson_country_path', storage_path('geojson/countries'));

        if ($directory === '' || ! File::isDirectory($directory)) {
            return collect();
        }

        return collect(File::files($directory))
            ->filter(fn (SplFileInfo $file): bool => $file->isFile() && $this->isGeoJsonFilename($file->getFilename()))
            ->sortBy(fn (SplFileInfo $file): string => $this->countryCodeFromFilename($file->getFilename()))
            ->values();
    }

    /**
     * @return Collection<string, Country>
     */
    private function countryLookup(): Collection
    {
        if ($this->countryLookupCache instanceof Collection) {
            return $this->countryLookupCache;
        }

        $this->countryLookupCache = Country::query()
            ->get()
            ->filter(fn (Country $country): bool => filled($country->iso3))
            ->keyBy(fn (Country $country): string => mb_strtoupper((string) $country->iso3));

        return $this->countryLookupCache;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeGeoJsonFile(string $path): array
    {
        try {
            $geoJson = json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException("GeoJSON faylini parse qilib bo'lmadi: {$path}", previous: $exception);
        }

        if (! is_array($geoJson)) {
            throw new RuntimeException("GeoJSON fayli noto'g'ri formatda: {$path}");
        }

        return $geoJson;
    }

    private function resolveCountryName(array $geoJson, string $countryCode, ?string $fallbackName = null): string
    {
        $feature = $geoJson['features'][0] ?? null;
        $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];

        return (string) ($properties['name'] ?? $properties['NAME'] ?? $fallbackName ?? $countryCode);
    }

    private function isGeoJsonFilename(string $filename): bool
    {
        $normalizedFilename = mb_strtolower($filename);

        return str_ends_with($normalizedFilename, '.geo.json') || str_ends_with($normalizedFilename, '.geojson');
    }

    private function countryCodeFromFilename(string $filename): string
    {
        $countryCode = preg_replace('/(\.geo\.json|\.geojson)$/i', '', $filename) ?? $filename;

        return mb_strtoupper(trim($countryCode));
    }
}
