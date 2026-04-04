<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
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
        ];
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

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'success' => 'Muvaffaqiyatli',
            'warning' => 'Ogohlantirish',
            'danger' => 'Muhim',
            default => "Ma'lumot",
        };
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

        return Str::limit((string) $this->message, 100);
    }
}
