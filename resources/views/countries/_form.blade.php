<form class="resource-form" method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">{{ __('ui.pages.countries.form.labels.name_ru') }}</span>
            <input type="text" name="name_ru" value="{{ old('name_ru', $country->name_ru) }}" placeholder="{{ __('ui.pages.countries.form.placeholders.name_ru') }}" required>
            @error('name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.countries.form.labels.name_uz') }}</span>
            <input type="text" name="name_uz" value="{{ old('name_uz', $country->name_uz) }}" placeholder="{{ __('ui.pages.countries.form.placeholders.name_uz') }}">
            @error('name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">ISO2</span>
            <input type="text" name="iso2" value="{{ old('iso2', $country->iso2) }}" placeholder="KZ" maxlength="2">
            @error('iso2')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">ISO3</span>
            <input type="text" name="iso3" value="{{ old('iso3', $country->iso3) }}" placeholder="KAZ" maxlength="3">
            @error('iso3')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.countries.form.labels.region_ru') }}</span>
            <input type="text" name="region_ru" value="{{ old('region_ru', $country->region_ru) }}" placeholder="{{ __('ui.pages.countries.form.placeholders.region_ru') }}">
            @error('region_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.countries.form.labels.region_uz') }}</span>
            <input type="text" name="region_uz" value="{{ old('region_uz', $country->region_uz) }}" placeholder="{{ __('ui.pages.countries.form.placeholders.region_uz') }}">
            @error('region_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.countries.form.labels.cooperation_status') }}</span>
            <select name="cooperation_status" required>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('cooperation_status', $country->cooperation_status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            @error('cooperation_status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <div class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.countries.form.labels.partnership_history') }}</span>
            @php($partnershipHistoryInitial = old('partnership_history_content', $partnershipHistoryContent ?? ''))
            <div class="country-ph-quill" data-country-ph-quill>
                <div data-country-ph-quill-toolbar>
                    <span class="ql-formats">
                        <button type="button" class="ql-bold" aria-label="Bold"></button>
                        <button type="button" class="ql-italic" aria-label="Italic"></button>
                        <button type="button" class="ql-underline" aria-label="Underline"></button>
                    </span>
                    <span class="ql-formats">
                        <button type="button" class="ql-list" value="ordered" aria-label="Ordered list"></button>
                        <button type="button" class="ql-list" value="bullet" aria-label="Bullet list"></button>
                    </span>
                    <span class="ql-formats">
                        <button type="button" class="ql-clean" aria-label="Clear formatting"></button>
                    </span>
                </div>
                <div data-country-ph-quill-editor aria-label="{{ __('ui.pages.countries.form.labels.partnership_history') }}"></div>
                <textarea name="partnership_history_content" data-country-ph-quill-input hidden>{{ $partnershipHistoryInitial }}</textarea>
            </div>
            <span class="field-help">Word fayl avtomatik yaratiladi va yangilanadi: `ISO3_ph.docx`</span>
            @error('partnership_history_content')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field field--span-2 country-notes-field">
            <span class="field-label country-notes-field__label" style="display:block; margin-top: 18px; margin-bottom: 10px;">{{ __('ui.pages.countries.form.labels.notes') }}</span>
            <textarea class="country-notes-field__textarea" name="notes" placeholder="{{ __('ui.pages.countries.form.placeholders.notes') }}" style="margin-top: 10px; min-height: 210px; padding-top: 34px;">{{ old('notes', $country->notes) }}</textarea>
            @error('notes')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('countries.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>

@once
    @push('head')
        @vite(['resources/css/countries/ph-editor.css', 'resources/js/countries/ph-editor.js'])
    @endpush
@endonce
