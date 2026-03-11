<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
            'success' => 'task_alt',
            'warning' => 'warning',
            'danger' => 'priority_high',
            default => 'notifications',
        };
    }
}
