@php
    $isConfidential = (bool) old('is_confidential', $document->exists ? $document->is_confidential : false);
@endphp

<form class="resource-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field field--span-2">
            <span class="field-label">Sarlavha (UZ)</span>
            <input type="text" name="title_uz" value="{{ old('title_uz', $document->title_uz) }}" placeholder="Hamkorlik bo'yicha buyruq" required>
            @error('title_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Sarlavha (RU)</span>
            <input type="text" name="title_ru" value="{{ old('title_ru', $document->title_ru) }}" placeholder="Приказ по сотрудничеству" required>
            @error('title_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Sarlavha (KRYL)</span>
            <input type="text" name="title_cryl" value="{{ old('title_cryl', $document->title_cryl) }}" placeholder="Ҳамкорлик бўйича буйруқ" required>
            @error('title_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Hujjat raqami</span>
            <input type="text" name="document_number" value="{{ old('document_number', $document->document_number) }}" placeholder="MG-2026/15">
            @error('document_number')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Hujjat turi</span>
            <select name="document_type_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($documentTypes as $documentType)
                    <option value="{{ $documentType->id }}" @selected((string) old('document_type_id', $document->document_type_id) === (string) $documentType->id)>{{ $documentType->display_name }}</option>
                @endforeach
            </select>
            @error('document_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Davlat</span>
            <select name="country_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $document->country_id) === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>
            @error('country_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Hamkor tashkilot</span>
            <select name="partner_organization_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($partnerOrganizations as $partnerOrganization)
                    <option value="{{ $partnerOrganization->id }}" @selected((string) old('partner_organization_id', $document->partner_organization_id) === (string) $partnerOrganization->id)>{{ $partnerOrganization->display_name }}</option>
                @endforeach
            </select>
            @error('partner_organization_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Kelishuv</span>
            <select name="agreement_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($agreements as $agreement)
                    <option value="{{ $agreement->id }}" @selected((string) old('agreement_id', $document->agreement_id) === (string) $agreement->id)>{{ $agreement->display_title }}</option>
                @endforeach
            </select>
            @error('agreement_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tashrif</span>
            <select name="visit_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($visits as $visit)
                    <option value="{{ $visit->id }}" @selected((string) old('visit_id', $document->visit_id) === (string) $visit->id)>{{ $visit->display_title }}</option>
                @endforeach
            </select>
            @error('visit_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tadbir</span>
            <select name="event_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected((string) old('event_id', $document->event_id) === (string) $event->id)>{{ $event->display_title }}</option>
                @endforeach
            </select>
            @error('event_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Holat</span>
            <select name="status" required>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('status', $document->status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ $document->exists ? "Yangi fayl yuklash" : "Fayl yuklash" }}</span>
            <input type="file" name="file" {{ $document->exists ? '' : 'required' }}>
            <span class="field-help">{{ $document->exists ? "Bo'sh qoldirilsa mavjud fayl saqlanadi." : "Fayl majburiy. Maksimal hajm 50 MB." }}</span>
            @if ($document->exists)
                <span class="field-help">Joriy fayl: <a href="{{ route('documents.download', $document) }}">{{ $document->file_name }}</a>{{ $document->file_size_human ? ' / '.$document->file_size_human : '' }}</span>
            @endif
            @error('file')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Izoh</span>
            <textarea name="description" placeholder="Hujjat mazmuni yoki qo'shimcha izoh">{{ old('description', $document->description) }}</textarea>
            @error('description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="checkbox-field field--span-2">
            <input type="hidden" name="is_confidential" value="0">
            <input type="checkbox" name="is_confidential" value="1" @checked($isConfidential)>
            <span>Ushbu hujjat maxfiy hujjat sifatida belgilansin</span>
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('documents.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
