<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Collection;
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
                    'country_url' => $country ? route('countries.show', $country) : null,
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
