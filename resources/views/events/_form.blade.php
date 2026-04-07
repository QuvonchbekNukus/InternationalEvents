<form class="resource-form" method="POST" action="{{ $action }}" enctype="multipart/form-data" data-event-form>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.events.form.labels.title_uz') }}</span>
            <input type="text" name="title_uz" value="{{ old('title_uz', $event->title_uz) }}" placeholder="{{ __('ui.pages.events.form.placeholders.title_uz') }}" required>
            @error('title_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.title_ru') }}</span>
            <input type="text" name="title_ru" value="{{ old('title_ru', $event->title_ru) }}" placeholder="{{ __('ui.pages.events.form.placeholders.title_ru') }}" required>
            @error('title_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.event_type') }}</span>
            <select name="event_type_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($eventTypes as $eventType)
                    <option value="{{ $eventType->id }}" @selected((string) old('event_type_id', $event->event_type_id) === (string) $eventType->id)>{{ $eventType->display_name }}</option>
                @endforeach
            </select>
            @error('event_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.country') }}</span>
            <select name="country_id" required data-event-country-select>
                <option value="">{{ __('ui.pages.events.form.placeholders.select_country') }}</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $event->country_id) === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>
            @error('country_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.partner_organization') }}</span>
            <select name="partner_organization_id" data-event-organization-select>
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($partnerOrganizations as $partnerOrganization)
                    <option
                        value="{{ $partnerOrganization->id }}"
                        data-country-id="{{ $partnerOrganization->country_id }}"
                        @selected((string) old('partner_organization_id', $event->partner_organization_id) === (string) $partnerOrganization->id)
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
            <span class="field-label">{{ __('ui.pages.events.form.labels.agreement') }}</span>
            <select name="agreement_id" data-event-agreement-select>
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($agreements as $agreement)
                    <option
                        value="{{ $agreement->id }}"
                        data-country-id="{{ $agreement->country_id }}"
                        @selected((string) old('agreement_id', $event->agreement_id) === (string) $agreement->id)
                    >
                        {{ $agreement->display_title }}
                    </option>
                @endforeach
            </select>
            @error('agreement_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.format') }}</span>
            <select name="format" required>
                @foreach ($formats as $formatValue => $formatLabel)
                    <option value="{{ $formatValue }}" @selected(old('format', $event->format) === $formatValue)>{{ $formatLabel }}</option>
                @endforeach
            </select>
            @error('format')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.status') }}</span>
            <select name="status" required>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('status', $event->status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.start_datetime') }}</span>
            <input type="datetime-local" name="start_datetime" value="{{ old('start_datetime', $event->start_datetime?->format('Y-m-d\\TH:i')) }}" required>
            @error('start_datetime')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.end_datetime') }}</span>
            <input type="datetime-local" name="end_datetime" value="{{ old('end_datetime', $event->end_datetime?->format('Y-m-d\\TH:i')) }}">
            @error('end_datetime')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.responsible_user') }}</span>
            <select name="responsible_user_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($responsibleUsers as $responsibleUser)
                    <option value="{{ $responsibleUser->id }}" @selected((string) old('responsible_user_id', $event->responsible_user_id) === (string) $responsibleUser->id)>{{ $responsibleUser->full_name }}</option>
                @endforeach
            </select>
            @error('responsible_user_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.responsible_department') }}</span>
            <select name="responsible_department_id">
                <option value="">{{ __('ui.common.values.unassigned') }}</option>
                @foreach ($responsibleDepartments as $responsibleDepartment)
                    <option value="{{ $responsibleDepartment->id }}" @selected((string) old('responsible_department_id', $event->responsible_department_id) === (string) $responsibleDepartment->id)>{{ $responsibleDepartment->display_name }}</option>
                @endforeach
            </select>
            @error('responsible_department_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.city') }}</span>
            <input type="text" name="city" value="{{ old('city', $event->city) }}" placeholder="{{ __('ui.pages.events.form.placeholders.city') }}">
            @error('city')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.events.form.labels.address') }}</span>
            <input type="text" name="address" value="{{ old('address', $event->address) }}" placeholder="{{ __('ui.pages.events.form.placeholders.address') }}">
            @error('address')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.events.form.labels.result_summary_uz') }}</span>
            <textarea name="result_summary_uz" placeholder="{{ __('ui.pages.events.form.placeholders.result_summary_uz') }}">{{ old('result_summary_uz', $event->result_summary_uz) }}</textarea>
            @error('result_summary_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.events.form.labels.result_summary_ru') }}</span>
            <textarea name="result_summary_ru" placeholder="{{ __('ui.pages.events.form.placeholders.result_summary_ru') }}">{{ old('result_summary_ru', $event->result_summary_ru) }}</textarea>
            @error('result_summary_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.events.form.labels.description') }}</span>
            <textarea name="description" placeholder="{{ __('ui.pages.events.form.placeholders.description') }}">{{ old('description', $event->description) }}</textarea>
            @error('description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.events.form.labels.files') }}</span>
            <input type="file" name="event_files[]" multiple>
            <span class="field-help">{{ __('ui.pages.events.form.files.help') }}</span>
            @error('event_files')
                <span class="field-error">{{ $message }}</span>
            @enderror
            @error('event_files.*')
                <span class="field-error">{{ $message }}</span>
            @enderror
            @if ($event->exists && $event->documents->isNotEmpty())
                <span class="field-help">{{ __('ui.pages.events.form.files.existing') }}</span>
                <div class="stack-list">
                    @foreach ($event->documents as $document)
                        <article class="stack-list__item">
                            @php($deleteFormId = "event-attachment-delete-{$event->id}-{$document->id}")
                            @if ($document->is_image && $document->file_url)
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
                                            {{ strtoupper($document->file_ext ?: __('ui.pages.events.form.files.file_fallback')) }}
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
                                                data-confirm-message="{{ __('ui.pages.events.form.files.confirm_delete') }}"
                                                onclick="return confirm(this.dataset.confirmMessage)"
                                            >
                                                <i class="material-icons" aria-hidden="true">delete</i>
                                                <span>{{ __('ui.common.actions.delete_file') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <strong>{{ $document->file_name }}</strong>
                                <span>
                                    {{ strtoupper($document->file_ext ?: __('ui.pages.events.form.files.file_fallback')) }}
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
                                            data-confirm-message="{{ __('ui.pages.events.form.files.confirm_delete') }}"
                                            onclick="return confirm(this.dataset.confirmMessage)"
                                        >
                                            <i class="material-icons" aria-hidden="true">delete</i>
                                            <span>{{ __('ui.common.actions.delete_file') }}</span>
                                        </button>
                                    </div>
                                @endif
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('events.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-event-form]').forEach((form) => {
                    const countrySelect = form.querySelector('[data-event-country-select]');
                    const organizationSelect = form.querySelector('[data-event-organization-select]');
                    const agreementSelect = form.querySelector('[data-event-agreement-select]');

                    if (!countrySelect) {
                        return;
                    }

                    const syncSelectOptions = (select, preserveSelectedMismatch = false) => {
                        if (!select) {
                            return;
                        }

                        const selectedCountryId = countrySelect.value;
                        const currentValue = select.value;

                        Array.from(select.options).forEach((option) => {
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
                            const selectedOption = select.options[select.selectedIndex];

                            if (
                                selectedOption &&
                                selectedOption.value !== '' &&
                                selectedCountryId !== '' &&
                                selectedOption.dataset.countryId !== selectedCountryId
                            ) {
                                select.value = '';
                            }
                        }
                    };

                    const syncLinkedSelects = (preserveSelectedMismatch = false) => {
                        syncSelectOptions(organizationSelect, preserveSelectedMismatch);
                        syncSelectOptions(agreementSelect, preserveSelectedMismatch);
                    };

                    syncLinkedSelects(true);
                    countrySelect.addEventListener('change', () => syncLinkedSelects(false));
                });
            });
        </script>
    @endpush
@endonce

@if ($event->exists && $event->documents->isNotEmpty())
    @foreach ($event->documents as $document)
        <form
            id="event-attachment-delete-{{ $event->id }}-{{ $document->id }}"
            method="POST"
            action="{{ route('events.attachments.destroy', [$event, $document]) }}"
            hidden
        >
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
