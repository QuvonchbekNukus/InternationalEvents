<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller implements HasMiddleware
{
    /**
     * @var list<string>
     */
    private const SECTION_RESOURCES = [
        'users',
        'departments',
        'ranks',
        'activity logs',
        'role permissions',
        'countries',
        'partner organizations',
        'partner contacts',
        'organization types',
        'agreements',
        'agreement types',
        'agreement directions',
        'events',
        'event types',
        'visits',
        'visit types',
        'documents',
        'document types',
    ];

    /**
     * @var array<string, string>
     */
    private const RESOURCE_CATEGORY = [
        'users' => 'settings',
        'departments' => 'settings',
        'ranks' => 'settings',
        'activity logs' => 'settings',
        'role permissions' => 'settings',
        'countries' => 'cooperation',
        'partner organizations' => 'cooperation',
        'partner contacts' => 'cooperation',
        'organization types' => 'cooperation',
        'agreements' => 'agreements',
        'agreement types' => 'agreements',
        'agreement directions' => 'agreements',
        'events' => 'events',
        'event types' => 'events',
        'visits' => 'visits',
        'visit types' => 'visits',
        'documents' => 'documents',
        'document types' => 'documents',
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

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAccess($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('role-permissions.index', ['role' => $role->name])
            ->with('status', __('ui.role_permissions.flash.created', ['name' => $role->name]));
    }

    public function rename(Request $request, Role $role): RedirectResponse
    {
        $this->ensureAccess($request);

        abort_unless($role->guard_name === 'web', 404);

        abort_if($role->name === 'super-admin', 403, __('ui.role_permissions.abort.super_admin_rename'));

        $validated = $request->validate([
            'new_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web'))->ignore($role->id),
            ],
        ]);

        $oldName = $role->name;
        $newName = $validated['new_name'];

        if ($newName !== $oldName) {
            $role->update(['name' => $newName]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return redirect()
            ->route('role-permissions.index', ['role' => $newName])
            ->with(
                'status',
                $newName === $oldName
                    ? __('ui.role_permissions.flash.renamed_same')
                    : __('ui.role_permissions.flash.renamed', ['old' => $oldName, 'new' => $newName])
            );
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->ensureAccess($request);

        abort_unless($role->guard_name === 'web', 404);

        abort_if($role->name === 'super-admin', 403, __('ui.role_permissions.abort.super_admin_delete'));

        $label = $role->name;
        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('role-permissions.index')
            ->with('status', __('ui.role_permissions.flash.deleted', ['name' => $label]));
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
            ->with('status', __('ui.role_permissions.flash.permissions_updated', ['name' => $role->name]));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function permissionSections(Collection $permissions, Role $selectedRole): array
    {
        $assignedPermissions = $selectedRole->permissions->pluck('name')->flip();
        $protectedPermissions = collect($this->protectedPermissionNames($selectedRole))->flip();
        $sections = [];

        foreach (self::SECTION_RESOURCES as $resource) {
            $definition = $this->sectionMeta($resource);
            $sectionPermissions = $permissions
                ->map(function (Permission $permission) use ($resource, $assignedPermissions, $protectedPermissions, $definition): ?array {
                    $parsed = $this->parsePermissionName($permission->name);

                    if ($parsed['resource'] !== $resource) {
                        return null;
                    }

                    return [
                        'name' => $permission->name,
                        'action_label' => $this->permissionActionLabel($parsed['action']),
                        'description' => $this->permissionDescription($parsed['action'], $definition['label']),
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

    /**
     * @return array{category: string, label: string}
     */
    private function sectionMeta(string $resource): array
    {
        $categoryKey = self::RESOURCE_CATEGORY[$resource] ?? 'settings';

        return [
            'category' => __('ui.role_permissions.categories.'.$categoryKey),
            'label' => __('ui.role_permissions.sections.'.$resource),
        ];
    }

    private function permissionActionLabel(string $action): string
    {
        $key = 'ui.role_permissions.actions.'.$action;
        $label = __($key);

        return $label !== $key ? $label : Str::headline($action);
    }

    private function permissionDescription(string $action, string $sectionLabel): string
    {
        $key = 'ui.role_permissions.descriptions.'.$action;

        if (trans($key) === $key) {
            return __('ui.role_permissions.descriptions.default', ['module' => $sectionLabel]);
        }

        return __($key, ['module' => $sectionLabel]);
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
