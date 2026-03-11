<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserNotificationService
{
    public function notifyResponsibleUser(
        Model $resource,
        ?int $previousResponsibleUserId,
        ?int $currentResponsibleUserId,
        ?User $actor,
        string $resourceLabel,
        bool $created = false
    ): void {
        if (! $currentResponsibleUserId) {
            return;
        }

        $isReassigned = ! $created
            && $previousResponsibleUserId !== null
            && $previousResponsibleUserId !== $currentResponsibleUserId;

        $displayTitle = trim((string) ($resource->display_title ?? class_basename($resource).' #'.$resource->getKey()));
        $actorLabel = $actor?->full_name ?: 'Tizim';

        $title = match (true) {
            $created => "Yangi {$resourceLabel} biriktirildi",
            $isReassigned => "{$resourceLabel} sizga biriktirildi",
            default => "Biriktirilgan {$resourceLabel} yangilandi",
        };

        $message = match (true) {
            $created, $isReassigned => "{$actorLabel} tomonidan \"{$displayTitle}\" {$resourceLabel} sizga biriktirildi.",
            default => "{$actorLabel} \"{$displayTitle}\" bo'yicha ma'lumotlarni yangiladi.",
        };

        Notification::create([
            'user_id' => $currentResponsibleUserId,
            'title' => $title,
            'message' => $message,
            'type' => $created || $isReassigned ? 'success' : 'info',
            'related_type' => $resource::class,
            'related_id' => $resource->getKey(),
        ]);
    }
}
