<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view users|view own users', only: ['index']),
            new Middleware('permission:create users', only: ['create', 'store']),
            new Middleware('permission:edit users|edit own users', only: ['edit', 'update']),
            new Middleware('permission:delete users', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedRole = trim((string) $request->string('role'));
        $selectedDepartment = trim((string) $request->string('department_id'));
        $selectedStatus = trim((string) $request->string('status'));

        $usersQuery = User::query()->with(['department:id,name_uz,name_ru,name_cryl', 'rank:id,name_uz,name_ru,name_cryl', 'roles:id,name']);

        $this->applyOwnScope(
            $request,
            $usersQuery,
            'view users',
            'view own users',
            fn ($query, $user): mixed => $query->whereKey($user->id)
        );

        $users = $usersQuery
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($userQuery) use ($search) {
                    $userQuery
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('position_uz', 'like', "%{$search}%")
                        ->orWhere('position_ru', 'like', "%{$search}%")
                        ->orWhere('position_cryl', 'like', "%{$search}%")
                        ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%"))
                        ->orWhereHas('rank', fn ($rankQuery) => $rankQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%"));
                });
            })
            ->when($selectedRole !== '', fn ($query) => $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', $selectedRole)))
            ->when($selectedDepartment !== '', fn ($query) => $query->where('department_id', (int) $selectedDepartment))
            ->when($selectedStatus !== '', fn ($query) => $query->where('is_active', $selectedStatus === 'active'))
            ->orderByDesc('is_active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'departments' => Department::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'filters' => [
                'search' => $search,
                'role' => $selectedRole,
                'department_id' => $selectedDepartment,
                'status' => $selectedStatus,
            ],
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$validated, $roleName] = $this->validatedData($request);

        $user = User::create($validated);
        $user->syncRoles([$roleName]);

        return redirect()
            ->route('users.index')
            ->with('status', "Foydalanuvchi {$user->full_name} muvaffaqiyatli yaratildi.");
    }

    public function edit(User $user): View
    {
        $this->authorizeOwnedRecord(
            request(),
            $user,
            'edit users',
            'edit own users',
            fn (User $record, $currentUser): bool => $record->is($currentUser)
        );

        $user->loadMissing(['roles:id,name']);

        return view('users.edit', [
            'user' => $user,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeOwnedRecord(
            $request,
            $user,
            'edit users',
            'edit own users',
            fn (User $record, $currentUser): bool => $record->is($currentUser)
        );

        if ($request->user()?->can('edit users')) {
            [$validated, $roleName] = $this->validatedData($request, $user);

            $user->update($validated);
            $user->syncRoles([$roleName]);
        } else {
            $user->update($this->validatedOwnData($request, $user));
        }

        return redirect()
            ->route('users.index')
            ->with('status', "Foydalanuvchi {$user->full_name} yangilandi.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->with('error', "Joriy foydalanuvchini o'chirib bo'lmaydi.");
        }

        $superAdminCount = User::query()
            ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'super-admin'))
            ->count();

        if ($user->hasRole('super-admin') && $superAdminCount <= 1) {
            return back()->with('error', "Oxirgi super-admin foydalanuvchini o'chirib bo'lmaydi.");
        }

        if ($user->uploadedDocuments()->exists()) {
            return back()->with('error', "Foydalanuvchi yuklagan hujjatlar mavjud. Avval ularni o'chiring.");
        }

        $fullName = $user->full_name;
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', "Foydalanuvchi {$fullName} o'chirildi.");
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function validatedData(Request $request, ?User $user = null): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:25',
                Rule::unique('users', 'phone')->ignore($user?->id),
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'max:255'],
            'position_uz' => ['nullable', 'string', 'max:255'],
            'position_ru' => ['nullable', 'string', 'max:255'],
            'position_cryl' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'rank_id' => ['required', 'integer', 'exists:ranks,id'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $roleName = $validated['role'];

        unset($validated['role']);

        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }

        return [$validated, $roleName];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedOwnData(Request $request, User $user): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:25',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
            'position_uz' => ['nullable', 'string', 'max:255'],
            'position_ru' => ['nullable', 'string', 'max:255'],
            'position_cryl' => ['nullable', 'string', 'max:255'],
        ]);

        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }

        return $validated;
    }

    /**
     * @return array{departments: \Illuminate\Database\Eloquent\Collection<int, Department>, ranks: \Illuminate\Database\Eloquent\Collection<int, Rank>, roles: \Illuminate\Support\Collection<int, string>}
     */
    private function formOptions(): array
    {
        return [
            'departments' => Department::query()->orderBy('name_uz')->get(),
            'ranks' => Rank::query()->orderBy('name_uz')->get(),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ];
    }
}
