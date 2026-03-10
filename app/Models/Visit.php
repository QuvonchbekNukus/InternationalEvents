<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    public const DIRECTIONS = [
        'incoming',
        'outgoing',
    ];

    public const DIRECTION_LABELS = [
        'incoming' => 'Kiruvchi',
        'outgoing' => 'Chiquvchi',
    ];

    public const STATUSES = [
        'planned',
        'ongoing',
        'completed',
        'cancelled',
    ];

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
        'title_cryl',
        'visit_type_id',
        'country_id',
        'partner_organization_id',
        'city',
        'address',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
        'direction',
        'status',
        'responsible_user_id',
        'responsible_department_id',
        'purpose_ru',
        'purpose_uz',
        'purpose_cryl',
        'result_summary_ru',
        'result_summary_uz',
        'result_summary_cryl',
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
            'latitude' => 'float',
            'longitude' => 'float',
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

    public function getDisplayTitleAttribute(): string
    {
        return $this->title_uz ?: $this->title_ru;
    }
}
