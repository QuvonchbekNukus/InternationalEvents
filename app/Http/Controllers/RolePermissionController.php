<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller implements HasMiddleware
{
    private const SECTION_DEFINITIONS = [
        'users' => ['category' => 'Sozlamalar', 'label' => 'Foydalanuvchilar'],
        'departments' => ['category' => 'Sozlamalar', 'label' => "Bo'limlar"],
        'ranks' => ['category' => 'Sozlamalar', 'label' => 'Unvonlar'],
        'activity logs' => ['category' => 'Sozlamalar', 'label' => 'Tizim loglari'],
        'role permissions' => ['category' => 'Sozlamalar', 'label' => "Ruxsatlarni boshqarish"],
        'countries' => ['category' => 'Hamkorlik', 'label' => 'Davlatlar'],
        'partner organizations' => ['category' => 'Hamkorlik', 'label' => 'Hamkor tashkilotlar'],
        'partner contacts' => ['category' => 'Hamkorlik', 'label' => 'Hamkor kontaktlar'],
        'organization types' => ['category' => 'Hamkorlik', 'label' => 'Tashkilot turlari'],
        'agreements' => ['category' => 'Kelishuvlar', 'label' => 'Barcha kelishuvlar'],
        'agreement types' => ['category' => 'Kelishuvlar', 'label' => 'Kelishuv turlari'],
        'agreement directions' => ['category' => 'Kelishuvlar', 'label' => "Kelishuv yo'nalishlari"],
        'events' => ['category' => 'Tadbirlar', 'label' => 'Barcha tadbirlar'],
        'event types' => ['category' => 'Tadbirlar', 'label' => 'Tadbir turlari'],
        'visits' => ['category' => 'Tashriflar', 'label' => 'Barcha tashriflar'],
        'visit types' => ['category' => 'Tashriflar', 'label' => 'Tashrif turlari'],
        'documents' => ['category' => 'Hujjatlar', 'label' => 'Barcha hujjatlar'],
        'document types' => ['category' => 'Hujjatlar', 'label' => 'Hujjat turlari'],
    ];

    private const ACTION_ORDER = [
        'view' => 10,
        'view own' => 20,
        'create' => 30,
        'edit' => 40,
        'edit own' => 50,
        'delete' => 60,
        'manage' => 70,
    ];

    public static function middleware(): array
    {
        return [];
    }

    public function index(Request $request): View
    {
        $this->ensureAccess($request);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->with(['permissions:id,name'])
            ->withCount('permissions')
            ->orderBy('name')
            ->get(['id', 'name']);

        abort_if($roles->isEmpty(), 404);

        $selectedRoleKey = trim((string) $request->string('role'));
        $selectedRole = $selectedRoleKey !== ''
            ? ($roles->firstWhere('name', $selectedRoleKey) ?? $roles->firstWhere('id', (int) $selectedRoleKey))
            : null;

        $permissions = $selectedRole
            ? Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('role-permissions.index', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'permissionSections' => $selectedRole ? $this->permissionSections($permissions, $selectedRole) : [],
            'protectedPermissions' => $selectedRole ? $this->protectedPermissionNames($selectedRole) : [],
            'totalPermissions' => $permissions->count(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->ensureAccess($request);

        abort_unless($role->guard_name === 'web', 404);

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
        ]);

        $permissionNames = collect($validated['permissions'] ?? [])
            ->map(fn (mixed $permission): string => (string) $permission)
            ->merge($this->protectedPermissionNames($role))
            ->unique()
            ->values();

        $role->syncPermissions($permissionNames);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('role-permissions.index', ['role' => $role->name])
            ->with('status', "Role {$role->name} uchun ruxsatlar yangilandi.");
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function permissionSections(Collection $permissions, Role $selectedRole): array
    {
        $assignedPermissions = $selectedRole->permissions->pluck('name')->flip();
        $protectedPermissions = collect($this->protectedPermissionNames($selectedRole))->flip();
        $sections = [];

        foreach (self::SECTION_DEFINITIONS as $resource => $definition) {
            $sectionPermissions = $permissions
                ->map(function (Permission $permission) use ($resource, $assignedPermissions, $protectedPermissions): ?array {
                    $parsed = $this->parsePermissionName($permission->name);

                    if ($parsed['resource'] !== $resource) {
                        return null;
                    }

                    return [
                        'name' => $permission->name,
                        'action_label' => $this->permissionActionLabel($parsed['action']),
                        'description' => $this->permissionDescription($parsed['action'], self::SECTION_DEFINITIONS[$resource]['label']),
                        'assigned' => $assignedPermissions->has($permission->name),
                        'protected' => $protectedPermissions->has($permission->name),
                    ];
                })
                ->filter()
                ->sortBy(fn (array $permission): int => self::ACTION_ORDER[$this->parsePermissionName($permission['name'])['action']] ?? 999)
                ->values();

            if ($sectionPermissions->isEmpty()) {
                continue;
            }

            $sections[$definition['category']][] = [
                'key' => Str::slug($resource),
                'category' => $definition['category'],
                'label' => $definition['label'],
                'assigned_count' => $sectionPermissions->where('assigned', true)->count(),
                'permissions' => $sectionPermissions->all(),
            ];
        }

        return collect($sections)
            ->flatten(1)
            ->values()
            ->all();
    }

    /**
     * @return array{action: string, resource: string}
     */
    private function parsePermissionName(string $permissionName): array
    {
        if ($permissionName === 'manage role permissions') {
            return [
                'action' => 'manage',
                'resource' => 'role permissions',
            ];
        }

        foreach (['view own', 'edit own', 'view', 'create', 'edit', 'delete'] as $action) {
            $prefix = $action.' ';

            if (Str::startsWith($permissionName, $prefix)) {
                return [
                    'action' => $action,
                    'resource' => Str::after($permissionName, $prefix),
                ];
            }
        }

        return [
            'action' => 'manage',
            'resource' => $permissionName,
        ];
    }

    private function permissionActionLabel(string $action): string
    {
        return match ($action) {
            'view' => "Ko'rish",
            'view own' => "Faqat o'ziniki",
            'create' => 'Yaratish',
            'edit' => 'Tahrirlash',
            'edit own' => "Faqat o'ziniki tahrir",
            'delete' => "O'chirish",
            'manage' => 'Boshqarish',
            default => Str::headline($action),
        };
    }

    private function permissionDescription(string $action, string $sectionLabel): string
    {
        return match ($action) {
            'view' => "{$sectionLabel} bo'limini ko'rish",
            'view own' => "{$sectionLabel} bo'yicha faqat o'ziga tegishli yozuvlarni ko'rish",
            'create' => "{$sectionLabel} bo'yicha yangi yozuv yaratish",
            'edit' => "{$sectionLabel} bo'yicha yozuvlarni tahrirlash",
            'edit own' => "{$sectionLabel} bo'yicha faqat o'ziga tegishli yozuvlarni tahrirlash",
            'delete' => "{$sectionLabel} bo'yicha yozuvlarni o'chirish",
            'manage' => "Role va permission bog'lanishlarini boshqarish",
            default => $sectionLabel,
        };
    }

    /**
     * @return array<int, string>
     */
    private function protectedPermissionNames(Role $role): array
    {
        if ($role->name === 'super-admin') {
            return ['manage role permissions'];
        }

        return [];
    }

    private function ensureAccess(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user?->hasRole('super-admin') || $user?->can('manage role permissions'),
            403
        );
    }
}
