<?php

namespace App\Models;

use App\Jobs\OptimizeDocumentImage;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use LogsModelActivity;
    use ResolvesLocalizedAttributes;

    private const STORAGE_DISK = 'documents';

    public const STATUSES = [
        'qoralama',
        'faol',
        'nazoratda',
        'arxivlangan',
    ];

    public const STATUS_TRANSLATION_KEY = 'ui.statuses.document';

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

    protected static function booted(): void
    {
        static::saved(function (Document $document): void {
            if (! $document->shouldScheduleImageOptimization()) {
                return;
            }

            if (config('uploads.images_optimize_after_response', true)) {
                OptimizeDocumentImage::dispatchAfterResponse($document->id);

                return;
            }

            OptimizeDocumentImage::dispatch($document->id);
        });

        static::deleted(function (Document $document): void {
            $filePath = $document->getOriginal('file_path') ?: $document->file_path;

            if ($filePath) {
                Storage::disk(self::STORAGE_DISK)->delete($filePath);
            }
        });
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
        return $this->firstAvailableLocalizedValue('title')
            ?: ($this->document_number ?: $this->file_name ?: '');
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return '/documents/'.ltrim(str_replace('\\', '/', $this->file_path), '/');
    }

    public function getIsImageAttribute(): bool
    {
        if ($this->mime_type && str_starts_with(strtolower($this->mime_type), 'image/')) {
            return true;
        }

        return in_array(strtolower((string) $this->file_ext), [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'bmp',
            'svg',
        ], true);
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

    private function shouldScheduleImageOptimization(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        if (! config('uploads.images_optimize_async', true)) {
            return false;
        }

        if (! $this->file_path) {
            return false;
        }

        if (! $this->wasRecentlyCreated && ! $this->wasChanged(['file_path', 'file_ext', 'mime_type'])) {
            return false;
        }

        $mime = strtolower((string) $this->mime_type);
        $ext = strtolower((string) $this->file_ext);

        if ($mime === 'image/webp' || $ext === 'webp') {
            return false;
        }

        if (str_starts_with($mime, 'image/')) {
            return in_array($mime, ['image/jpeg', 'image/png'], true);
        }

        return in_array($ext, ['jpg', 'jpeg', 'png'], true);
    }
}
