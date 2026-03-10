<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PartnerOrganization extends Model
{
    use LogsModelActivity;

    public const STATUSES = [
        'faol',
        'rejada',
        'tugallangan',
    ];

    public const STATUS_LABELS = [
        'faol' => 'Faol',
        'rejada' => 'Rejada',
        'tugallangan' => 'Tugallangan',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'country_id',
        'name_ru',
        'name_uz',
        'name_cryl',
        'short_name',
        'organization_type_id',
        'address',
        'city',
        'website',
        'status',
        'notes',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function organizationType(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class);
    }

    public function partnerContacts(): HasMany
    {
        return $this->hasMany(PartnerContact::class);
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

    public function getDisplayNameAttribute(): string
    {
        return $this->name_uz ?: $this->name_ru;
    }

    public function getWebsiteUrlAttribute(): ?string
    {
        if (! $this->website) {
            return null;
        }

        return Str::startsWith($this->website, ['http://', 'https://'])
            ? $this->website
            : 'https://'.$this->website;
    }
}
