<?php

namespace App\Listeners;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class RecordSpatiePermissionActivity
{
    public function handleRoleAttached(RoleAttached $event): void
    {
        $detail = $this->formatRoleNames($event->rolesOrIds);
        $this->log(
            $event->model,
            'role_attached',
            __('ui.activity_log.log_messages.role_attached', ['detail' => $detail]),
            ['roles' => $detail]
        );
    }

    public function handleRoleDetached(RoleDetached $event): void
    {
        $detail = $this->formatRoleNames($event->rolesOrIds);
        $this->log(
            $event->model,
            'role_detached',
            __('ui.activity_log.log_messages.role_detached', ['detail' => $detail]),
            ['roles' => $detail]
        );
    }

    public function handlePermissionAttached(PermissionAttached $event): void
    {
        $detail = $this->formatPermissionNames($event->permissionsOrIds);
        $this->log(
            $event->model,
            'permission_attached',
            __('ui.activity_log.log_messages.permission_attached', ['detail' => $detail]),
            ['permissions' => $detail]
        );
    }

    public function handlePermissionDetached(PermissionDetached $event): void
    {
        $detail = $this->formatPermissionNames($event->permissionsOrIds);
        $this->log(
            $event->model,
            'permission_detached',
            __('ui.activity_log.log_messages.permission_detached', ['detail' => $detail]),
            ['permissions' => $detail]
        );
    }

    /**
     * @param  array<string, mixed>  $extraProps
     */
    private function log(Model $subject, string $event, string $description, array $extraProps): void
    {
        $causer = auth()->user();

        activity('system')
            ->causedBy($causer)
            ->performedOn($subject)
            ->event($event)
            ->withProperties(array_merge(
                array_filter([
                    'subject_label' => $this->subjectLabel($subject),
                    'subject_type_label' => $this->subjectTypeLabel($subject),
                    'causer_name' => $causer instanceof User ? $causer->full_name : null,
                    'ip_address' => request()?->ip(),
                    'user_agent' => Str::limit((string) request()?->userAgent(), 255, ''),
                ], fn (mixed $v): bool => $v !== null && $v !== ''),
                $extraProps
            ))
            ->log($description);
    }

    private function subjectLabel(Model $model): string
    {
        if ($model instanceof User) {
            return $model->full_name;
        }

        if ($model instanceof Role) {
            return $model->name;
        }

        if ($model instanceof Permission) {
            return $model->name;
        }

        return class_basename($model).' #'.$model->getKey();
    }

    private function subjectTypeLabel(Model $model): string
    {
        $labels = trans('ui.activity_log.subject_types');

        if (is_array($labels) && isset($labels[get_class($model)])) {
            return (string) $labels[get_class($model)];
        }

        return class_basename($model);
    }

    private function formatRoleNames(mixed $rolesOrIds): string
    {
        $names = [];

        foreach ($this->normalizeList($rolesOrIds) as $item) {
            if ($item instanceof RoleContract) {
                $names[] = $item->name;

                continue;
            }

            if (is_numeric($item)) {
                $name = Role::query()->whereKey($item)->value('name');
                $names[] = $name !== null ? (string) $name : (string) $item;

                continue;
            }

            $names[] = (string) $item;
        }

        return implode(', ', array_values(array_unique($names)));
    }

    private function formatPermissionNames(mixed $permissionsOrIds): string
    {
        $names = [];

        foreach ($this->normalizeList($permissionsOrIds) as $item) {
            if ($item instanceof PermissionContract) {
                $names[] = $item->name;

                continue;
            }

            if (is_numeric($item)) {
                $name = Permission::query()->whereKey($item)->value('name');
                $names[] = $name !== null ? (string) $name : (string) $item;

                continue;
            }

            $names[] = (string) $item;
        }

        return implode(', ', array_values(array_unique($names)));
    }

    /**
     * @return list<mixed>
     */
    private function normalizeList(mixed $value): array
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->all();
        }

        if (! is_array($value)) {
            return [$value];
        }

        return $value;
    }
}
