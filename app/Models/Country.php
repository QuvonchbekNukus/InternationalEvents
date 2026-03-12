<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedAttributes;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use LogsModelActivity;
    use ResolvesLocalizedAttributes;

    public const STATUSES = [
        'active',
        'planned',
        'completed',
    ];

    public const STATUS_LABELS = [
        'active' => 'Faol',
        'planned' => 'Rejada',
        'completed' => 'Yakunlangan',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name_ru',
        'name_uz',
        'name_cryl',
        'iso2',
        'iso3',
        'region_ru',
        'region_uz',
        'region_cryl',
        'latitude',
        'longitude',
        'default_zoom',
        'cooperation_status',
        'boundary_geojson_path',
        'flag_path',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'default_zoom' => 'float',
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('name');
    }

    public function getDisplayRegionAttribute(): ?string
    {
        $value = $this->firstAvailableLocalizedValue('region');

        return $value !== '' ? $value : null;
    }

    public function getFlagAssetPathAttribute(): ?string
    {
        if (! $this->iso2) {
            return null;
        }

        return 'flags/'.strtolower($this->iso2).'.svg';
    }

    public function getHasFlagFileAttribute(): bool
    {
        return $this->flag_asset_path !== null && is_file(public_path($this->flag_asset_path));
    }

    public function partnerOrganizations(): HasMany
    {
        return $this->hasMany(PartnerOrganization::class);
    }

    public function agreements(): HasMany
    {
        return $this->hasMany(Agreement::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
