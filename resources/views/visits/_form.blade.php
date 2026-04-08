<form class="resource-form" method="POST" action="{{ $action }}" enctype="multipart/form-data" data-visit-form>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.title_uz') }}</span>
            <input type="text" name="title_uz" value="{{ old('title_uz', $visit->title_uz) }}" placeholder="{{ __('ui.pages.visits.form.placeholders.title_uz') }}" required>
            @error('title_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.title_ru') }}</span>
            <input type="text" name="title_ru" value="{{ old('title_ru', $visit->title_ru) }}" placeholder="{{ __('ui.pages.visits.form.placeholders.title_ru') }}" required>
            @error('title_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.visit_type') }}</span>
            <select name="visit_type_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($visitTypes as $visitType)
                    <option value="{{ $visitType->id }}" @selected((string) old('visit_type_id', $visit->visit_type_id) === (string) $visitType->id)>{{ $visitType->display_name }}</option>
                @endforeach
            </select>
            @error('visit_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.country') }}</span>
            <select name="country_id" required data-visit-country-select>
                <option value="">{{ __('ui.pages.visits.form.placeholders.select_country') }}</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $visit->country_id) === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>
            @error('country_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.partner_organization') }}</span>
            <select name="partner_organization_id" data-visit-organization-select>
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($partnerOrganizations as $partnerOrganization)
                    <option
                        value="{{ $partnerOrganization->id }}"
                        data-country-id="{{ $partnerOrganization->country_id }}"
                        @selected((string) old('partner_organization_id', $visit->partner_organization_id) === (string) $partnerOrganization->id)
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
            <span class="field-label">{{ __('ui.pages.visits.form.labels.direction') }}</span>
            <select name="direction">
                <option value="">{{ __('ui.pages.visits.form.values.direction_unselected') }}</option>
                @foreach ($directions as $directionValue => $directionLabel)
                    <option value="{{ $directionValue }}" @selected(old('direction', $visit->direction) === $directionValue)>{{ $directionLabel }}</option>
                @endforeach
            </select>
            @error('direction')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.status') }}</span>
            <select name="status" required>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('status', $visit->status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.start_date') }}</span>
            <input type="date" name="start_date" value="{{ old('start_date', $visit->start_date?->format('Y-m-d')) }}" required>
            @error('start_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.end_date') }}</span>
            <input type="date" name="end_date" value="{{ old('end_date', $visit->end_date?->format('Y-m-d')) }}">
            @error('end_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.responsible_user') }}</span>
            <select name="responsible_user_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($responsibleUsers as $responsibleUser)
                    <option value="{{ $responsibleUser->id }}" @selected((string) old('responsible_user_id', $visit->responsible_user_id) === (string) $responsibleUser->id)>{{ $responsibleUser->full_name }}</option>
                @endforeach
            </select>
            @error('responsible_user_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.responsible_department') }}</span>
            <select name="responsible_department_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($responsibleDepartments as $responsibleDepartment)
                    <option value="{{ $responsibleDepartment->id }}" @selected((string) old('responsible_department_id', $visit->responsible_department_id) === (string) $responsibleDepartment->id)>{{ $responsibleDepartment->display_name }}</option>
                @endforeach
            </select>
            @error('responsible_department_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.city') }}</span>
            <input type="text" name="city" value="{{ old('city', $visit->city) }}" placeholder="{{ __('ui.pages.visits.form.placeholders.city') }}">
            @error('city')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.address') }}</span>
            <input type="text" name="address" value="{{ old('address', $visit->address) }}" placeholder="{{ __('ui.pages.visits.form.placeholders.address') }}">
            @error('address')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.purpose_uz') }}</span>
            <textarea name="purpose_uz" placeholder="{{ __('ui.pages.visits.form.placeholders.purpose_uz') }}">{{ old('purpose_uz', $visit->purpose_uz) }}</textarea>
            @error('purpose_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.purpose_ru') }}</span>
            <textarea name="purpose_ru" placeholder="{{ __('ui.pages.visits.form.placeholders.purpose_ru') }}">{{ old('purpose_ru', $visit->purpose_ru) }}</textarea>
            @error('purpose_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.result_summary_uz') }}</span>
            <textarea name="result_summary_uz" placeholder="{{ __('ui.pages.visits.form.placeholders.result_summary_uz') }}">{{ old('result_summary_uz', $visit->result_summary_uz) }}</textarea>
            @error('result_summary_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.result_summary_ru') }}</span>
            <textarea name="result_summary_ru" placeholder="{{ __('ui.pages.visits.form.placeholders.result_summary_ru') }}">{{ old('result_summary_ru', $visit->result_summary_ru) }}</textarea>
            @error('result_summary_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.description') }}</span>
            <textarea name="description" placeholder="{{ __('ui.pages.visits.form.placeholders.description') }}">{{ old('description', $visit->description) }}</textarea>
            @error('description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.visits.form.labels.files') }}</span>
            <input
                type="file"
                name="visit_files[]"
                accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.svg,.pdf,.doc,.docx,image/jpeg,image/png,image/gif,image/webp,image/bmp,image/svg+xml,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                multiple
            >
            <span class="field-help">{{ __('ui.pages.visits.form.files.help') }}</span>
            @error('visit_files')
                <span class="field-error">{{ $message }}</span>
            @enderror
            @error('visit_files.*')
                <span class="field-error">{{ $message }}</span>
            @enderror
            @if ($visit->exists && $visit->documents->isNotEmpty())
                @php
                    $documents = $visit->documents->sortByDesc('created_at');
                    $imageDocuments = $documents->filter(fn ($document) => $document->is_image && $document->file_url);
                    $otherDocuments = $documents->reject(fn ($document) => $document->is_image && $document->file_url);
                @endphp

                <span class="field-help">{{ __('ui.pages.visits.form.files.existing') }}</span>

                @if ($imageDocuments->isNotEmpty())
                    <div class="attachment-section">
                        <p class="attachment-section__label">{{ __('ui.pages.visits.form.files.images_section') }}</p>

                        <div class="stack-list">
                            @foreach ($imageDocuments as $document)
                                <article class="stack-list__item">
                                    @php($deleteFormId = "visit-attachment-delete-{$visit->id}-{$document->id}")
                                    <div class="detail-media detail-media--compact">
                                        <a href="{{ $document->file_url }}" target="_blank" rel="noopener">
                                            <img
                                                class="detail-media__thumb"
                                                src="{{ $document->file_url }}"
                                                alt="{{ $document->file_name }}"
                                                loading="lazy"
                                            >
                                        </a>

                                        <div class="detail-media__body">
                                            <strong>{{ $document->file_name }}</strong>
                                            <span>
                                                {{ strtoupper($document->file_ext ?: __('ui.pages.visits.form.files.file_fallback')) }}
                                                @if ($document->file_size_human)
                                                    {{ ' | '.$document->file_size_human }}
                                                @endif
                                            </span>

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
                                                    data-confirm-message="{{ __('ui.pages.visits.form.files.confirm_delete') }}"
                                                    onclick="return confirm(this.dataset.confirmMessage)"
                                                >
                                                    <i class="material-icons" aria-hidden="true">delete</i>
                                                    <span>{{ __('ui.common.actions.delete_file') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($otherDocuments->isNotEmpty())
                    <div class="attachment-section">
                        <p class="attachment-section__label">{{ __('ui.pages.visits.form.files.files_section') }}</p>

                        <div class="stack-list">
                            @foreach ($otherDocuments as $document)
                                <article class="stack-list__item">
                                    @php($deleteFormId = "visit-attachment-delete-{$visit->id}-{$document->id}")
                                    <strong>{{ $document->file_name }}</strong>
                                    <span>
                                        {{ strtoupper($document->file_ext ?: __('ui.pages.visits.form.files.file_fallback')) }}
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
                                                data-confirm-message="{{ __('ui.pages.visits.form.files.confirm_delete') }}"
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
                    </div>
                @endif
            @endif
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('visits.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>

@if ($visit->exists && $visit->documents->isNotEmpty())
    @foreach ($visit->documents as $document)
        <form
            id="visit-attachment-delete-{{ $visit->id }}-{{ $document->id }}"
            method="POST"
            action="{{ route('visits.attachments.destroy', [$visit, $document]) }}"
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
                document.querySelectorAll('[data-visit-form]').forEach((form) => {
                    const countrySelect = form.querySelector('[data-visit-country-select]');
                    const organizationSelect = form.querySelector('[data-visit-organization-select]');

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
                });
            });
        </script>
    @endpush
@endonce
