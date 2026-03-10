<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
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
        return $this->name_uz ?: $this->name_ru;
    }
}
