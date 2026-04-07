<?php

namespace App\Models;

use App\Services\DateReminderNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\Activity;

class Notification extends Model
{
    use Concerns\LogsModelActivity;

    public const SUPER_ADMIN_ROLE = 'super-admin';

    /**
     * When true, {@see static::mirrorToSuperAdmins()} is skipped (avoids infinite loops).
     */
    public bool $skipSuperadminMirrorDispatch = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'title_key',
        'message_key',
        'message_params',
        'type',
        'is_read',
        'related_type',
        'related_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'message_params' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Notification $notification): void {
            $notification->mirrorToSuperAdmins();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'related_type', 'related_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        if ($this->is_read) {
            return;
        }

        $this->forceFill(['is_read' => true])->save();
    }

    /**
     * Copy this notification to every active super-admin (deduped per admin per day + related + type).
     */
    public function mirrorToSuperAdmins(): void
    {
        if ($this->skipSuperadminMirrorDispatch || ! config('notifications.mirror_to_super_admins', true)) {
            return;
        }

        if (! $this->user_id) {
            return;
        }

        if (User::query()
            ->whereKey($this->user_id)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', self::SUPER_ADMIN_ROLE))
            ->exists()) {
            return;
        }

        $adminIds = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', self::SUPER_ADMIN_ROLE))
            ->pluck('id');

        if ($adminIds->isEmpty()) {
            return;
        }

        $day = $this->created_at?->toDateString() ?? now()->toDateString();
        $type = (string) $this->type;
        $relatedType = $this->related_type;
        $relatedId = $this->related_id;

        foreach ($adminIds as $adminId) {
            if ((int) $adminId === (int) $this->user_id) {
                continue;
            }

            $already = static::query()
                ->where('user_id', $adminId)
                ->where('type', $type)
                ->where('related_type', $relatedType)
                ->where('related_id', $relatedId)
                ->whereDate('created_at', $day)
                ->exists();

            if ($already) {
                continue;
            }

            $copy = $this->replicate([
                'user_id',
                'is_read',
                'created_at',
                'updated_at',
            ]);

            $copy->user_id = (int) $adminId;
            $copy->is_read = false;
            $copy->skipSuperadminMirrorDispatch = true;
            Activity::withoutLogs(fn () => $copy->save());
        }
    }

    public function resolveTargetUrl(): ?string
    {
        $resource = $this->related;

        if (! $resource) {
            return null;
        }

        return match ($resource::class) {
            Agreement::class => route('agreements.show', $resource),
            Event::class => route('events.show', $resource),
            Visit::class => route('visits.show', $resource),
            PartnerContact::class => route('partner-contacts.show', $resource),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvedTranslationParams(): array
    {
        $params = is_array($this->message_params) ? $this->message_params : [];
        $related = $this->related;

        if ($related instanceof Agreement || $related instanceof Event || $related instanceof Visit) {
            $params['subject'] = $related->display_title;
        }

        if ($related instanceof PartnerContact) {
            $params['subject'] = $related->display_name;
        }

        if (! empty($params['date_raw'])) {
            $params['date'] = Carbon::parse((string) $params['date_raw'])
                ->locale(app()->getLocale())
                ->translatedFormat('d F');
            unset($params['date_raw']);
        } elseif ($this->effectiveMessageKey() === 'ui.notifications.in_app.birthday_message') {
            $anchor = $this->created_at ?? Carbon::now();
            $celebration = Carbon::instance($anchor)->addDay()->startOfDay();
            $params['date'] = $celebration->locale(app()->getLocale())->translatedFormat('d F');
        }

        if (empty($params['actor']) && ! $this->message_key && ! $this->title_key) {
            $legacy = $this->legacyI18nKeys();
            if (($legacy['message_key'] ?? null) !== null && in_array($this->type, ['success', 'info'], true)) {
                $parsed = $this->legacyParsedActorFromMessage();
                if ($parsed !== []) {
                    $params = array_merge($params, $parsed);
                }
            }
        }

        return $params;
    }

    public function effectiveTitleKey(): ?string
    {
        if ($this->title_key) {
            return $this->title_key;
        }

        return $this->legacyI18nKeys()['title_key'] ?? null;
    }

    public function effectiveMessageKey(): ?string
    {
        if ($this->message_key) {
            return $this->message_key;
        }

        return $this->legacyI18nKeys()['message_key'] ?? null;
    }

    /**
     * Eski yozuvlar: title_key bo‘lmasa, type va o‘zbekcha sarlavha/matndan kalitni taxmin qiladi.
     *
     * @return array{title_key: ?string, message_key: ?string}
     */
    private function legacyI18nKeys(): array
    {
        if ($this->legacyI18nKeysCache !== null) {
            return $this->legacyI18nKeysCache;
        }

        $type = (string) $this->type;

        if ($type === DateReminderNotificationService::EVENT_START_TYPE) {
            $this->legacyI18nKeysCache = [
                'title_key' => 'ui.notifications.in_app.event_start_title',
                'message_key' => 'ui.notifications.in_app.event_start_message',
            ];

            return $this->legacyI18nKeysCache;
        }

        if ($type === DateReminderNotificationService::VISIT_START_TYPE) {
            $this->legacyI18nKeysCache = [
                'title_key' => 'ui.notifications.in_app.visit_start_title',
                'message_key' => 'ui.notifications.in_app.visit_start_message',
            ];

            return $this->legacyI18nKeysCache;
        }

        if ($type === DateReminderNotificationService::PARTNER_CONTACT_BIRTHDAY_TYPE) {
            $this->legacyI18nKeysCache = [
                'title_key' => 'ui.notifications.in_app.birthday_title',
                'message_key' => 'ui.notifications.in_app.birthday_message',
            ];

            return $this->legacyI18nKeysCache;
        }

        if (! in_array($type, ['success', 'info'], true)) {
            $this->legacyI18nKeysCache = ['title_key' => null, 'message_key' => null];

            return $this->legacyI18nKeysCache;
        }

        $prefix = match ($this->related_type) {
            Event::class => 'ui.notifications.in_app.event',
            Visit::class => 'ui.notifications.in_app.visit',
            Agreement::class => 'ui.notifications.in_app.agreement',
            default => null,
        };

        if ($prefix === null) {
            $this->legacyI18nKeysCache = ['title_key' => null, 'message_key' => null];

            return $this->legacyI18nKeysCache;
        }

        $title = (string) ($this->attributes['title'] ?? '');
        $message = (string) ($this->attributes['message'] ?? '');

        $titleKey = null;
        if (str_contains($title, 'yangilandi')) {
            $titleKey = "{$prefix}.title_updated";
        } elseif (str_contains($title, 'Yangi') || str_contains($title, 'yangi')) {
            $titleKey = "{$prefix}.title_new";
        } elseif (str_contains($title, 'sizga biriktirildi')) {
            $titleKey = "{$prefix}.title_reassigned";
        }

        $messageKey = null;
        if (str_contains($message, "ma'lumotlarni yangiladi") || str_contains($message, 'maʼlumotlarni yangiladi')) {
            $messageKey = "{$prefix}.msg_updated";
        } elseif (str_contains($message, 'biriktirildi')) {
            $messageKey = "{$prefix}.msg_new";
        }

        $this->legacyI18nKeysCache = [
            'title_key' => $titleKey,
            'message_key' => $messageKey,
        ];

        return $this->legacyI18nKeysCache;
    }

    /**
     * @return array{actor?: string}
     */
    private function legacyParsedActorFromMessage(): array
    {
        $msg = trim((string) ($this->attributes['message'] ?? ''));
        if ($msg === '') {
            return [];
        }

        if (preg_match('/^(.+?)\s+tomonidan\s+[\"«“]/u', $msg, $m)) {
            return ['actor' => trim($m[1])];
        }

        if (preg_match('/^(.+?)\s+[\"«“]/u', $msg, $m)) {
            return ['actor' => trim($m[1])];
        }

        return [];
    }

    public function getDisplayTitleAttribute(): string
    {
        $key = $this->effectiveTitleKey();
        if ($key) {
            return __($key, $this->resolvedTranslationParams());
        }

        return (string) ($this->attributes['title'] ?? '');
    }

    public function getDisplayMessageAttribute(): string
    {
        $key = $this->effectiveMessageKey();
        if ($key) {
            return __($key, $this->resolvedTranslationParams());
        }

        return (string) ($this->attributes['message'] ?? '');
    }

    public function getTypeLabelAttribute(): string
    {
        $key = 'ui.notifications.types.'.$this->type;
        $label = __($key);

        return $label !== $key ? $label : __('ui.notifications.types.default');
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'success' => 'check_circle',
            'warning' => 'warning',
            'danger' => 'priority_high',
            default => 'notifications',
        };
    }

    /**
     * Material icon for the related record category (event, visit, etc.).
     */
    public function getRelatedCategoryIconAttribute(): string
    {
        return match ($this->related_type) {
            Event::class => 'event',
            Visit::class => 'place',
            Agreement::class => 'description',
            PartnerContact::class => 'cake',
            default => 'notifications',
        };
    }

    /**
     * Short CSS slug for styling: event, visit, agreement, birthday, generic.
     */
    public function getRelatedKindSlugAttribute(): string
    {
        return match ($this->related_type) {
            Event::class => 'event',
            Visit::class => 'visit',
            Agreement::class => 'agreement',
            PartnerContact::class => 'birthday',
            default => 'generic',
        };
    }

    public function getRelatedKindLabelAttribute(): string
    {
        return match ($this->related_type) {
            Event::class => __('ui.notifications_dropdown.kind_event'),
            Visit::class => __('ui.notifications_dropdown.kind_visit'),
            Agreement::class => __('ui.notifications_dropdown.kind_agreement'),
            PartnerContact::class => __('ui.notifications_dropdown.kind_birthday'),
            default => __('ui.notifications_dropdown.kind_other'),
        };
    }

    /**
     * One-line summary for dropdown / list (linked record title when available).
     */
    public function getPreviewTextAttribute(): string
    {
        $resource = $this->related;

        if ($resource instanceof Agreement || $resource instanceof Event || $resource instanceof Visit) {
            return Str::limit($resource->display_title, 88);
        }

        if ($resource instanceof PartnerContact) {
            return Str::limit($resource->display_name, 88);
        }

        $body = $this->effectiveMessageKey()
            ? $this->display_message
            : (string) ($this->attributes['message'] ?? '');

        return Str::limit($body, 100);
    }
}
