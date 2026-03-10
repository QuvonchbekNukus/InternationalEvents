<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view departments', only: ['index']),
            new Middleware('permission:create departments', only: ['create', 'store']),
            new Middleware('permission:edit departments', only: ['edit', 'update']),
            new Middleware('permission:delete departments', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $departments = Department::query()
            ->withCount('users')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($departmentQuery) use ($search) {
                    $departmentQuery
                        ->where('name_uz', 'like', "%{$search}%")
                        ->orWhere('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_cryl', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('departments.index', [
            'departments' => $departments,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('departments.create', [
            'department' => new Department(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $department = Department::create($this->validatedData($request));

        return redirect()
            ->route('departments.index')
            ->with('status', "Bo'lim {$department->name_uz} muvaffaqiyatli yaratildi.");
    }

    public function edit(Department $department): View
    {
        return view('departments.edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($this->validatedData($request, $department));

        return redirect()
            ->route('departments.index')
            ->with('status', "Bo'lim {$department->name_uz} yangilandi.");
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->users()->exists()) {
            return back()->with('error', "Bo'limga foydalanuvchilar biriktirilgan. Avval ularni boshqa bo'limga o'tkazing.");
        }

        $departmentName = $department->name_uz;
        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('status', "Bo'lim {$departmentName} o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Department $department = null): array
    {
        return $request->validate([
            'name_ru' => ['required', 'string', 'max:255'],
            'name_uz' => ['required', 'string', 'max:255'],
            'name_cryl' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('departments', 'code')->ignore($department?->id)],
            'description' => ['nullable', 'string'],
        ]);
    }
}
