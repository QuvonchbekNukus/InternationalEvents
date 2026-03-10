<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class DocumentTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view document types', only: ['index']),
            new Middleware('permission:create document types', only: ['create', 'store']),
            new Middleware('permission:edit document types', only: ['edit', 'update']),
            new Middleware('permission:delete document types', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $documentTypes = DocumentType::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($documentTypeQuery) use ($search) {
                    $documentTypeQuery
                        ->where('name_uz', 'like', "%{$search}%")
                        ->orWhere('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_cryl', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('document-types.index', [
            'documentTypes' => $documentTypes,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('document-types.create', [
            'documentType' => new DocumentType(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $documentType = DocumentType::create($this->validatedData($request));

        return redirect()
            ->route('document-types.index')
            ->with('status', "Hujjat turi {$documentType->name_uz} muvaffaqiyatli yaratildi.");
    }

    public function edit(DocumentType $documentType): View
    {
        return view('document-types.edit', [
            'documentType' => $documentType,
        ]);
    }

    public function update(Request $request, DocumentType $documentType): RedirectResponse
    {
        $documentType->update($this->validatedData($request));

        return redirect()
            ->route('document-types.index')
            ->with('status', "Hujjat turi {$documentType->name_uz} yangilandi.");
    }

    public function destroy(DocumentType $documentType): RedirectResponse
    {
        $documentTypeName = $documentType->name_uz;
        $documentType->delete();

        return redirect()
            ->route('document-types.index')
            ->with('status', "Hujjat turi {$documentTypeName} o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name_ru' => ['required', 'string', 'max:255'],
            'name_uz' => ['required', 'string', 'max:255'],
            'name_cryl' => ['required', 'string', 'max:255'],
        ]);
    }
}
