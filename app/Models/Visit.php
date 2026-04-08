<?php

namespace App\Models;

use App\Models\Concerns\DeletesOwnedDocuments;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    use DeletesOwnedDocuments;
    use LogsModelActivity;
    use ResolvesLocalizedAttributes;

    public const DIRECTIONS = [
        'incoming',
        'outgoing',
    ];

    public const DIRECTION_LABELS = [
        'incoming' => 'Kiruvchi',
        'outgoing' => 'Chiquvchi',
    ];

    public const DIRECTION_TRANSLATION_KEY = 'ui.directions.visit';

    public const STATUSES = [
        'planned',
        'ongoing',
        'completed',
        'cancelled',
    ];

    public const STATUS_TRANSLATION_KEY = 'ui.statuses.visit';

    public const STATUS_LABELS = [
        'planned' => 'Rejalashtirilgan',
        'ongoing' => 'Jarayonda',
        'completed' => 'Yakunlangan',
        'cancelled' => 'Bekor qilingan',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title_ru',
        'title_uz',
        'visit_type_id',
        'country_id',
        'partner_organization_id',
        'city',
        'address',
        'start_date',
        'end_date',
        'direction',
        'status',
        'responsible_user_id',
        'responsible_department_id',
        'purpose_ru',
        'purpose_uz',
        'result_summary_ru',
        'result_summary_uz',
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
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function visitType(): BelongsTo
    {
        return $this->belongsTo(VisitType::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function partnerOrganization(): BelongsTo
    {
        return $this->belongsTo(PartnerOrganization::class);
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

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    protected function ownedDocumentsQuery()
    {
        return $this->documents();
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('title');
    }

    public function getDisplayPurposeAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('purpose');
    }

    public function getDisplayResultSummaryAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('result_summary');
    }
}
