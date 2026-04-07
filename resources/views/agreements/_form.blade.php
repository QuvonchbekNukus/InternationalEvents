<form class="resource-form" method="POST" action="{{ $action }}" data-agreement-form enctype="multipart/form-data">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.agreement_number') }}</span>
            <input type="text" name="agreement_number" value="{{ old('agreement_number', $agreement->agreement_number) }}" placeholder="MG-2026-001">
            @error('agreement_number')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.country') }}</span>
            <select name="country_id" required data-agreement-country-select>
                <option value="">{{ __('ui.pages.agreements.form.placeholders.select_country') }}</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $agreement->country_id) === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>
            @error('country_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.partner_organization') }}</span>
            <select name="partner_organization_id" data-agreement-organization-select>
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($partnerOrganizations as $partnerOrganization)
                    <option
                        value="{{ $partnerOrganization->id }}"
                        data-country-id="{{ $partnerOrganization->country_id }}"
                        @selected((string) old('partner_organization_id', $agreement->partner_organization_id) === (string) $partnerOrganization->id)
                    >
                        {{ $partnerOrganization->display_name }}
                    </option>
                @endforeach
            </select>
            @error('partner_organization_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.agreement_type') }}</span>
            <select name="agreement_type_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($agreementTypes as $agreementType)
                    <option value="{{ $agreementType->id }}" @selected((string) old('agreement_type_id', $agreement->agreement_type_id) === (string) $agreementType->id)>{{ $agreementType->display_name }}</option>
                @endforeach
            </select>
            @error('agreement_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.agreement_direction') }}</span>
            <select name="agreement_direction_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($agreementDirections as $agreementDirection)
                    <option value="{{ $agreementDirection->id }}" @selected((string) old('agreement_direction_id', $agreement->agreement_direction_id) === (string) $agreementDirection->id)>{{ $agreementDirection->display_name }}</option>
                @endforeach
            </select>
            @error('agreement_direction_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.status') }}</span>
            <select name="status" required>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('status', $agreement->status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.signed_date') }}</span>
            <input type="date" name="signed_date" value="{{ old('signed_date', $agreement->signed_date?->format('Y-m-d')) }}">
            @error('signed_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.start_date') }}</span>
            <input type="date" name="start_date" value="{{ old('start_date', $agreement->start_date?->format('Y-m-d')) }}">
            @error('start_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.end_date') }}</span>
            <input type="date" name="end_date" value="{{ old('end_date', $agreement->end_date?->format('Y-m-d')) }}">
            @error('end_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.responsible_user') }}</span>
            <select name="responsible_user_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($responsibleUsers as $responsibleUser)
                    <option value="{{ $responsibleUser->id }}" @selected((string) old('responsible_user_id', $agreement->responsible_user_id) === (string) $responsibleUser->id)>{{ $responsibleUser->full_name }}</option>
                @endforeach
            </select>
            @error('responsible_user_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.responsible_department') }}</span>
            <select name="responsible_department_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($responsibleDepartments as $responsibleDepartment)
                    <option value="{{ $responsibleDepartment->id }}" @selected((string) old('responsible_department_id', $agreement->responsible_department_id) === (string) $responsibleDepartment->id)>{{ $responsibleDepartment->display_name }}</option>
                @endforeach
            </select>
            @error('responsible_department_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.title_uz') }}</span>
            <input type="text" name="title_uz" value="{{ old('title_uz', $agreement->title_uz) }}" placeholder="{{ __('ui.pages.agreements.form.placeholders.title_uz') }}" required>
            @error('title_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.title_ru') }}</span>
            <input type="text" name="title_ru" value="{{ old('title_ru', $agreement->title_ru) }}" placeholder="{{ __('ui.pages.agreements.form.placeholders.title_ru') }}" required>
            @error('title_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.short_title_uz') }}</span>
            <input type="text" name="short_title_uz" value="{{ old('short_title_uz', $agreement->short_title_uz) }}" placeholder="{{ __('ui.pages.agreements.form.placeholders.short_title_uz') }}">
            @error('short_title_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.short_title_ru') }}</span>
            <input type="text" name="short_title_ru" value="{{ old('short_title_ru', $agreement->short_title_ru) }}" placeholder="{{ __('ui.pages.agreements.form.placeholders.short_title_ru') }}">
            @error('short_title_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.description') }}</span>
            <textarea name="description" placeholder="{{ __('ui.pages.agreements.form.placeholders.description') }}">{{ old('description', $agreement->description) }}</textarea>
            @error('description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.agreements.form.labels.files') }}</span>
            <input
                type="file"
                name="agreement_files[]"
                accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                multiple
                data-agreement-file-input
            >
            <span class="field-help" data-agreement-file-help>{{ __('ui.pages.agreements.form.files.help') }}</span>
            @error('agreement_files')
                <span class="field-error">{{ $message }}</span>
            @enderror
            @error('agreement_files.*')
                <span class="field-error">{{ $message }}</span>
            @enderror
            @if ($agreement->documents->isNotEmpty())
                <span class="field-help">{{ __('ui.pages.agreements.form.files.existing') }}</span>
                <div class="stack-list">
                    @foreach ($agreement->documents as $document)
                        <article class="stack-list__item">
                            @php($deleteFormId = "agreement-attachment-delete-{$agreement->id}-{$document->id}")
                            <strong>{{ $document->file_name }}</strong>
                            <span>
                                {{ strtoupper($document->file_ext ?: __('ui.pages.agreements.form.files.file_fallback')) }}
                                @if ($document->file_size_human)
                                    {{ ' | '.$document->file_size_human }}
                                @endif
                            </span>
                            @if ($document->file_url)
                                <div class="detail-actions-inline">
                                    <a class="action-pill" href="{{ $document->file_url }}" target="_blank" rel="noopener">
                                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                                        <span>{{ __('ui.common.actions.open') }}</span>
                                    </a>
                                    <a class="action-pill" href="{{ $document->file_url }}" download="{{ $document->file_name }}">
                                        <i class="material-icons" aria-hidden="true">file_download</i>
                                        <span>{{ __('ui.common.actions.download_file') }}</span>
                                    </a>
                                    <button
                                        class="action-pill action-pill--danger"
                                        type="submit"
                                        form="{{ $deleteFormId }}"
                                        data-confirm-message="{{ __('ui.pages.agreements.form.files.confirm_delete') }}"
                                        onclick="return confirm(this.dataset.confirmMessage)"
                                    >
                                        <i class="material-icons" aria-hidden="true">delete</i>
                                        <span>{{ __('ui.common.actions.delete_file') }}</span>
                                    </button>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('agreements.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>

@if ($agreement->exists && $agreement->documents->isNotEmpty())
    @foreach ($agreement->documents as $document)
        <form
            id="agreement-attachment-delete-{{ $agreement->id }}-{{ $document->id }}"
            method="POST"
            action="{{ route('agreements.attachments.destroy', [$agreement, $document]) }}"
            hidden
        >
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-agreement-form]').forEach((form) => {
                    const countrySelect = form.querySelector('[data-agreement-country-select]');
                    const organizationSelect = form.querySelector('[data-agreement-organization-select]');
                    const fileInput = form.querySelector('[data-agreement-file-input]');
                    const fileHelp = form.querySelector('[data-agreement-file-help]');

                    if (!countrySelect || !organizationSelect) {
                        return;
                    }

                    const syncOrganizations = (preserveSelectedMismatch = false) => {
                        const selectedCountryId = countrySelect.value;
                        const currentValue = organizationSelect.value;

                        Array.from(organizationSelect.options).forEach((option) => {
                            if (!option.value) {
                                option.hidden = false;
                                option.disabled = false;
                                return;
                            }

                            const matchesCountry = selectedCountryId === '' || option.dataset.countryId === selectedCountryId;
                            const keepSelectedMismatch = preserveSelectedMismatch && option.value === currentValue;
                            const shouldShow = matchesCountry || keepSelectedMismatch;

                            option.hidden = !shouldShow;
                            option.disabled = !shouldShow;
                        });

                        if (!preserveSelectedMismatch && currentValue !== '') {
                            const selectedOption = organizationSelect.options[organizationSelect.selectedIndex];

                            if (
                                selectedOption &&
                                selectedOption.value !== '' &&
                                selectedCountryId !== '' &&
                                selectedOption.dataset.countryId !== selectedCountryId
                            ) {
                                organizationSelect.value = '';
                            }
                        }
                    };

                    syncOrganizations(true);
                    countrySelect.addEventListener('change', () => syncOrganizations(false));

                    if (fileInput && fileHelp) {
                        const defaultHelpText = fileHelp.textContent;
                        const allowedExtensions = new Set(['pdf', 'doc', 'docx']);

                        fileInput.addEventListener('change', () => {
                            const selectedFiles = Array.from(fileInput.files || []);
                            const hasInvalidFile = selectedFiles.some((file) => {
                                const extension = file.name.includes('.')
                                    ? file.name.split('.').pop().toLowerCase()
                                    : '';

                                return !allowedExtensions.has(extension);
                            });

                            if (!hasInvalidFile) {
                                fileHelp.textContent = defaultHelpText;
                                fileHelp.classList.remove('field-error');
                                return;
                            }

                            fileInput.value = '';
                            fileHelp.textContent = "{{ __('ui.pages.agreements.form.files.invalid_extension') }}";
                            fileHelp.classList.add('field-error');
                        });
                    }
                });
            });
        </script>
    @endpush
@endonce
