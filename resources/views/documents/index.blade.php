@extends('layouts.dashboard')

@section('title', 'Barcha hujjatlar')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Documents</p>
                <h1 class="page-title">Barcha hujjatlar</h1>
                <p class="page-subtitle">Yuklangan hujjatlar, fayl holati va bog'langan obyektlar bo'yicha nazorat oynasi.</p>
            </div>

            @can('create documents')
                <a class="btn btn--primary" href="{{ route('documents.create') }}">
                    <i class="material-icons" aria-hidden="true">upload_file</i>
                    <span>Yangi hujjat</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('documents.index') }}">
            <label class="toolbar-search" aria-label="Hujjat qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom, raqam, fayl yoki bog'liq obyekt bo'yicha qidiring">
            </label>

            <select class="toolbar-select" name="document_type_id" aria-label="Hujjat turi bo'yicha filter">
                <option value="">Barcha turlar</option>
                @foreach ($documentTypes as $documentType)
                    <option value="{{ $documentType->id }}" @selected((string) $filters['document_type_id'] === (string) $documentType->id)>{{ $documentType->name_uz }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="Holat bo'yicha filter">
                <option value="">Barcha holatlar</option>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="is_confidential" aria-label="Maxfiylik bo'yicha filter">
                <option value="">Barcha hujjatlar</option>
                <option value="1" @selected($filters['is_confidential'] === '1')>Faqat maxfiy</option>
                <option value="0" @selected($filters['is_confidential'] === '0')>Faqat oddiy</option>
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>Filtrlash</span>
            </button>

            @if (collect($filters)->filter(fn ($value) => $value !== '' && $value !== null)->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('documents.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($documents->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hujjat</th>
                            <th>Fayl</th>
                            <th>Bog'lanish</th>
                            <th>Yuklovchi</th>
                            <th>Holat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                            @php
                                $statusClass = match ($document->status) {
                                    'faol' => 'is-active',
                                    'nazoratda' => 'is-planned',
                                    'arxivlangan' => 'is-completed',
                                    default => 'is-muted',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $document->display_title }}</span>
                                    <span class="row-subtitle">{{ $document->title_ru }}{{ $document->title_cryl ? ' / '.$document->title_cryl : '' }}</span>
                                    <span class="row-subtitle">{{ $document->document_number ?: "Raqam biriktirilmagan" }}</span>
                                    <span class="row-subtitle">{{ $document->documentType?->name_uz ?: "Tur biriktirilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ \Illuminate\Support\Str::limit($document->file_name, 40) }}</span>
                                    <span class="row-subtitle">{{ strtoupper($document->file_ext ?: 'fayl') }}{{ $document->file_size_human ? ' / '.$document->file_size_human : '' }}</span>
                                    <span class="row-subtitle">{{ $document->mime_type ?: "MIME turi aniqlanmagan" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $document->country?->display_name ?: "Davlat biriktirilmagan" }}</span>
                                    <span class="row-subtitle">{{ $document->partnerOrganization?->display_name ?: "Tashkilot biriktirilmagan" }}</span>
                                    @if ($document->agreement)
                                        <span class="row-subtitle">Kelishuv: {{ $document->agreement->display_title }}</span>
                                    @endif
                                    @if ($document->visit)
                                        <span class="row-subtitle">Tashrif: {{ $document->visit->display_title }}</span>
                                    @endif
                                    @if ($document->event)
                                        <span class="row-subtitle">Tadbir: {{ $document->event->display_title }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $document->uploader?->full_name ?: '-' }}</span>
                                    <span class="row-subtitle">{{ $document->created_at?->format('d.m.Y H:i') }}</span>
                                    @if ($document->is_confidential)
                                        <span class="row-subtitle">Maxfiy hujjat</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$document->status] ?? $document->status }}
                                    </span>
                                    @if ($document->description)
                                        <span class="row-subtitle">{{ \Illuminate\Support\Str::limit($document->description, 100) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="action-pill" href="{{ route('documents.download', $document) }}">
                                            <i class="material-icons" aria-hidden="true">download</i>
                                            <span>Fayl</span>
                                        </a>

                                        @canany(['edit documents', 'edit own documents'])
                                            <a class="action-pill" href="{{ route('documents.edit', $document) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcanany

                                        @can('delete documents')
                                            <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Ushbu hujjatni o\\'chirishni tasdiqlaysizmi?');">
                                                @csrf
                                                @method('DELETE')

                                                <button class="action-pill action-pill--danger" type="submit">
                                                    <i class="material-icons" aria-hidden="true">delete</i>
                                                    <span>O'chirish</span>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="table-empty">
                    Hujjatlar hali yuklanmagan. Yangi hujjat qo'shing yoki filtrlarni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$documents" />
        </div>
    </div>
@endsection
