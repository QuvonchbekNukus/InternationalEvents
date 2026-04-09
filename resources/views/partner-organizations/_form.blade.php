<form class="resource-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.country') }}</span>
            <select name="country_id" required>
                <option value="">{{ __('ui.pages.partner_organizations.form.placeholders.select_country') }}</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $partnerOrganization->country_id) === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>
            @error('country_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.organization_type') }}</span>
            <select name="organization_type_id">
                <option value="">{{ __('ui.pages.partner_organizations.form.values.unassigned') }}</option>
                @foreach ($organizationTypes as $organizationType)
                    <option value="{{ $organizationType->id }}" @selected((string) old('organization_type_id', $partnerOrganization->organization_type_id) === (string) $organizationType->id)>{{ $organizationType->display_name }}</option>
                @endforeach
            </select>
            @error('organization_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.name_uz') }}</span>
            <input type="text" name="name_uz" value="{{ old('name_uz', $partnerOrganization->name_uz) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.name_uz') }}" required>
            @error('name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.name_ru') }}</span>
            <input type="text" name="name_ru" value="{{ old('name_ru', $partnerOrganization->name_ru) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.name_ru') }}" required>
            @error('name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.short_name') }}</span>
            <input type="text" name="short_name" value="{{ old('short_name', $partnerOrganization->short_name) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.short_name') }}">
            @error('short_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.status') }}</span>
            <select name="status" required>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('status', $partnerOrganization->status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.city') }}</span>
            <input type="text" name="city" value="{{ old('city', $partnerOrganization->city) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.city') }}">
            @error('city')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.website') }}</span>
            <input type="text" name="website" value="{{ old('website', $partnerOrganization->website) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.website') }}">
            @error('website')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.organization_info_file') }}</span>
            <input
                type="file"
                name="organization_info_file"
                accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            >
            <span class="field-help">{{ __('ui.pages.partner_organizations.form.file.help') }}</span>
            @if ($partnerOrganization->organizationInfoDocument?->file_url)
                <span class="field-help">
                    {{ __('ui.pages.partner_organizations.form.file.current') }}:
                    <a href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" download="{{ $partnerOrganization->organizationInfoDocument->file_name }}">
                        {{ $partnerOrganization->organizationInfoDocument->file_name }}
                    </a>
                </span>
                <div class="detail-actions-inline">
                    <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" target="_blank" rel="noopener">
                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                        <span>{{ __('ui.common.actions.open') }}</span>
                    </a>
                    <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" download="{{ $partnerOrganization->organizationInfoDocument->file_name }}">
                        <i class="material-icons" aria-hidden="true">file_download</i>
                        <span>{{ __('ui.common.actions.download_file') }}</span>
                    </a>
                    <button
                        class="action-pill action-pill--danger"
                        type="submit"
                        form="partner-organization-info-delete-{{ $partnerOrganization->id }}"
                        data-confirm-message="{{ __('ui.pages.partner_organizations.form.file.confirm_delete') }}"
                        onclick="return confirm(this.dataset.confirmMessage)"
                    >
                        <i class="material-icons" aria-hidden="true">delete</i>
                        <span>{{ __('ui.common.actions.delete_file') }}</span>
                    </button>
                </div>
            @endif
            @error('organization_info_file')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.address') }}</span>
            <input type="text" name="address" value="{{ old('address', $partnerOrganization->address) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.address') }}">
            @error('address')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <div class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.partnership_history') }}</span>
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
                <div data-country-ph-quill-editor aria-label="{{ __('ui.pages.partner_organizations.form.labels.partnership_history') }}"></div>
                <textarea name="partnership_history_content" data-country-ph-quill-input hidden>{{ $partnershipHistoryInitial }}</textarea>
            </div>
            <span class="field-help">Word fayl avtomatik yaratiladi va yangilanadi: `*_ph.docx`</span>
            @error('partnership_history_content')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field field--span-2">
            <span class="field-label" style="display:block; margin-top:16px !important; margin-bottom:12px !important;">{{ __('ui.pages.partner_organizations.form.labels.notes') }}</span>
            <textarea name="notes" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.notes') }}" style="display:block; margin-top:12px !important; min-height:220px !important; padding-top:42px !important; line-height:1.65 !important;">{{ old('notes', $partnerOrganization->notes) }}</textarea>
            @error('notes')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('partner-organizations.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>

@if ($partnerOrganization->exists && $partnerOrganization->organizationInfoDocument)
    <form
        id="partner-organization-info-delete-{{ $partnerOrganization->id }}"
        method="POST"
        action="{{ route('partner-organizations.organization-info.destroy', $partnerOrganization) }}"
        hidden
    >
        @csrf
        @method('DELETE')
    </form>
@endif

@once
    @push('head')
        @vite(['resources/css/countries/ph-editor.css', 'resources/js/countries/ph-editor.js'])
    @endpush
@endonce
