<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedAttributes;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use LogsModelActivity;
    use ResolvesLocalizedAttributes;

    public const FORMATS = [
        'offline',
        'online',
        'gibrid',
    ];

    public const FORMAT_LABELS = [
        'offline' => 'Offline',
        'online' => 'Online',
        'gibrid' => 'Gibrid',
    ];

    public const STATUSES = [
        'rejada',
        'hozirda',
        'tugatilgan',
        'bekorlangan',
    ];

    public const STATUS_LABELS = [
        'rejada' => 'Rejada',
        'hozirda' => 'Hozirda',
        'tugatilgan' => 'Tugatilgan',
        'bekorlangan' => 'Bekorlangan',
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
        'event_type_id',
        'country_id',
        'partner_organization_id',
        'agreement_id',
        'city',
        'address',
        'latitude',
        'longitude',
        'start_datetime',
        'end_datetime',
        'format',
        'status',
        'responsible_user_id',
        'responsible_department_id',
        'description',
        'result_summary_ru',
        'result_summary_uz',
        'result_summary_cryl',
        'control_due_date',
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
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'control_due_date' => 'date',
        ];
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function partnerOrganization(): BelongsTo
    {
        return $this->belongsTo(PartnerOrganization::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
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

    public function getDisplayTitleAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('title');
    }
}
