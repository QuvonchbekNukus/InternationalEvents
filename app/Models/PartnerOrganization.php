<?php

namespace App\Models;

use App\Models\Concerns\DeletesOwnedDocuments;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PartnerOrganization extends Model
{
    use DeletesOwnedDocuments;
    use LogsModelActivity;
    use ResolvesLocalizedAttributes;

    public const STATUSES = [
        'faol',
        'rejada',
        'tugallangan',
    ];

    public const STATUS_TRANSLATION_KEY = 'ui.statuses.partner_organization';

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
        'short_name',
        'organization_type_id',
        'organization_info_document_id',
        'address',
        'city',
        'website',
        'status',
        'notes',
        'partnership_history',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function organizationType(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class);
    }

    public function organizationInfoDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'organization_info_document_id');
    }

    public function partnerContacts(): HasMany
    {
        return $this->hasMany(PartnerContact::class);
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

    protected function ownedDocumentsQuery()
    {
        return $this->documents()
            ->whereNull('agreement_id')
            ->whereNull('visit_id')
            ->whereNull('event_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('name');
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
