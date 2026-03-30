<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedAttributes;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerContact extends Model
{
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
        'full_name_cryl',
        'birthday',
        'photo',
        'cv',
        'position_ru',
        'position_uz',
        'position_cryl',
        'email',
        'phone',
        'description',
        'is_primary',
    ];

    /**
     * Attributes excluded from audit details.
     *
     * @var list<string>
     */
    protected array $activityLogExcept = [
        'birthday',
        'photo',
        'cv',
        'email',
        'phone',
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
            'email' => 'encrypted',
            'phone' => 'encrypted',
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

    public function getDisplayNameAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('full_name');
    }

    public function getDisplayPositionAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('position');
    }
}
