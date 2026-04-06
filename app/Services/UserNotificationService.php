<?php

namespace App\Services;

use App\Models\Agreement;
use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserNotificationService
{
    /** @var list<string> */
    private const RESOURCE_TYPES = ['event', 'visit', 'agreement'];

    public function notifyResponsibleUser(
        Model $resource,
        ?int $previousResponsibleUserId,
        ?int $currentResponsibleUserId,
        ?User $actor,
        string $resourceType,
        bool $created = false
    ): void {
        if (! $currentResponsibleUserId) {
            return;
        }

        if (! in_array($resourceType, self::RESOURCE_TYPES, true)) {
            return;
        }

        $isReassigned = ! $created
            && $previousResponsibleUserId !== null
            && $previousResponsibleUserId !== $currentResponsibleUserId;

        $actorLabel = $actor?->full_name ?: __('ui.notifications.in_app.actor_system');

        $prefix = 'ui.notifications.in_app.'.$resourceType;

        $titleKey = match (true) {
            $created => "{$prefix}.title_new",
            $isReassigned => "{$prefix}.title_reassigned",
            default => "{$prefix}.title_updated",
        };

        $messageKey = ($created || $isReassigned)
            ? "{$prefix}.msg_new"
            : "{$prefix}.msg_updated";

        $params = ['actor' => $actorLabel];
        $subjectUz = $this->snapshotSubjectUz($resource);
        $paramsUz = array_merge($params, ['subject' => $subjectUz]);

        Notification::create([
            'user_id' => $currentResponsibleUserId,
            'title' => trans($titleKey, [], 'uz'),
            'message' => trans($messageKey, $paramsUz, 'uz'),
            'title_key' => $titleKey,
            'message_key' => $messageKey,
            'message_params' => $params,
            'type' => $created || $isReassigned ? 'success' : 'info',
            'related_type' => $resource::class,
            'related_id' => $resource->getKey(),
        ]);
    }

    private function snapshotSubjectUz(Model $resource): string
    {
        $previous = app()->getLocale();

        try {
            app()->setLocale('uz');

            if ($resource instanceof Agreement || $resource instanceof Event || $resource instanceof Visit) {
                return trim((string) $resource->display_title);
            }

            return trim((string) ($resource->display_title ?? Str::headline(class_basename($resource)).' #'.$resource->getKey()));
        } finally {
            app()->setLocale($previous);
        }
    }
}
