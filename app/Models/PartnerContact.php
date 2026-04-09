<?php

namespace App\Models;

use App\Models\Concerns\DeletesOwnedDocuments;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerContact extends Model
{
    use DeletesOwnedDocuments;
    use LogsModelActivity;
    use ResolvesLocalizedAttributes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'partner_organization_id',
        'full_name_ru',
        'full_name_uz',
        'birthday',
        'photo',
        'cv',
        'position_ru',
        'position_uz',
        'email',
        'phone',
        'description',
        'is_primary',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'is_primary' => 'boolean',
        ];
    }

    public function partnerOrganization(): BelongsTo
    {
        return $this->belongsTo(PartnerOrganization::class);
    }

    public function photoDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'photo');
    }

    public function cvDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'cv');
    }

    protected function ownedDocumentsQuery(): Builder
    {
        $documentIds = array_values(array_unique(array_filter([
            $this->photo,
            $this->cv,
        ])));

        return Document::query()->whereKey($documentIds);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('full_name');
    }

    public function getDisplayPositionAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('position');
    }
}
