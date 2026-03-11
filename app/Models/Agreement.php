<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedAttributes;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agreement extends Model
{
    use LogsModelActivity;
    use ResolvesLocalizedAttributes;

    public const STATUSES = [
        'draft',
        'active',
        'expired',
        'terminated',
        'completed',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Qoralama',
        'active' => 'Faol',
        'expired' => 'Muddati tugagan',
        'terminated' => 'Bekor qilingan',
        'completed' => 'Yakunlangan',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'agreement_number',
        'title_ru',
        'title_uz',
        'title_cryl',
        'short_title_ru',
        'short_title_uz',
        'short_title_cryl',
        'country_id',
        'partner_organization_id',
        'agreement_type_id',
        'agreement_direction_id',
        'signed_date',
        'start_date',
        'end_date',
        'status',
        'responsible_user_id',
        'responsible_department_id',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'signed_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function partnerOrganization(): BelongsTo
    {
        return $this->belongsTo(PartnerOrganization::class);
    }

    public function agreementType(): BelongsTo
    {
        return $this->belongsTo(AgreementType::class);
    }

    public function agreementDirection(): BelongsTo
    {
        return $this->belongsTo(AgreementDirection::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function responsibleDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'responsible_department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('short_title', 'title');
    }
}
