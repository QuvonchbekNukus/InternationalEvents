<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedAttributes;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use LogsModelActivity;
    use ResolvesLocalizedAttributes;

    public const STATUSES = [
        'qoralama',
        'faol',
        'nazoratda',
        'arxivlangan',
    ];

    public const STATUS_LABELS = [
        'qoralama' => 'Qoralama',
        'faol' => 'Faol',
        'nazoratda' => 'Nazoratda',
        'arxivlangan' => 'Arxivlangan',
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
        'document_number',
        'document_type_id',
        'file_name',
        'file_path',
        'file_ext',
        'file_size',
        'mime_type',
        'country_id',
        'partner_organization_id',
        'agreement_id',
        'visit_id',
        'event_id',
        'uploaded_by',
        'status',
        'is_confidential',
        'description',
    ];

    /**
     * Attributes excluded from audit details.
     *
     * @var list<string>
     */
    protected array $activityLogExcept = [
        'file_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_confidential' => 'boolean',
        ];
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
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

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('title');
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    public function getFileSizeHumanAttribute(): ?string
    {
        if ($this->file_size === null) {
            return null;
        }

        if ($this->file_size < 1024) {
            return $this->file_size.' B';
        }

        if ($this->file_size < 1024 * 1024) {
            return number_format($this->file_size / 1024, 1).' KB';
        }

        return number_format($this->file_size / (1024 * 1024), 1).' MB';
    }
}
