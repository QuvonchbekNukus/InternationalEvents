@php
    $isPrimary = (bool) old('is_primary', $partnerContact->exists ? $partnerContact->is_primary : false);
@endphp

<form class="resource-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.partner_organization') }}</span>
            <select name="partner_organization_id" required>
                <option value="">{{ __('ui.pages.partner_contacts.form.placeholders.select_partner_organization') }}</option>
                @foreach ($partnerOrganizations as $partnerOrganization)
                    <option value="{{ $partnerOrganization->id }}" @selected((string) old('partner_organization_id', $partnerContact->partner_organization_id) === (string) $partnerOrganization->id)>
                        {{ $partnerOrganization->display_name }}{{ $partnerOrganization->country?->iso2 ? ' ('.$partnerOrganization->country->iso2.')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('partner_organization_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.full_name_uz') }}</span>
            <input type="text" name="full_name_uz" value="{{ old('full_name_uz', $partnerContact->full_name_uz) }}" placeholder="{{ __('ui.pages.partner_contacts.form.placeholders.full_name_uz') }}" required>
            @error('full_name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.full_name_ru') }}</span>
            <input type="text" name="full_name_ru" value="{{ old('full_name_ru', $partnerContact->full_name_ru) }}" placeholder="{{ __('ui.pages.partner_contacts.form.placeholders.full_name_ru') }}" required>
            @error('full_name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.birthday') }}</span>
            <input type="date" name="birthday" value="{{ old('birthday', $partnerContact->birthday?->format('Y-m-d')) }}">
            @error('birthday')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.position_uz') }}</span>
            <input type="text" name="position_uz" value="{{ old('position_uz', $partnerContact->position_uz) }}" placeholder="{{ __('ui.pages.partner_contacts.form.placeholders.position_uz') }}">
            @error('position_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.position_ru') }}</span>
            <input type="text" name="position_ru" value="{{ old('position_ru', $partnerContact->position_ru) }}" placeholder="{{ __('ui.pages.partner_contacts.form.placeholders.position_ru') }}">
            @error('position_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.email') }}</span>
            <input type="email" name="email" value="{{ old('email', $partnerContact->email) }}" placeholder="{{ __('ui.pages.partner_contacts.form.placeholders.email') }}">
            <span class="field-help">{{ __('ui.pages.partner_contacts.form.help.email_encrypted') }}</span>
            @error('email')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.phone') }}</span>
            <input type="text" name="phone" value="{{ old('phone', $partnerContact->phone) }}" placeholder="{{ __('ui.pages.partner_contacts.form.placeholders.phone') }}">
            <span class="field-help">{{ __('ui.pages.partner_contacts.form.help.phone_encrypted') }}</span>
            @error('phone')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.photo') }}</span>
            <input type="file" name="photo_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            <span class="field-help">{{ __('ui.pages.partner_contacts.form.help.photo_file') }}</span>
            @if ($partnerContact->photoDocument?->file_url)
                <span class="field-help">
                    {{ __('ui.pages.partner_contacts.form.help.current_file') }}:
                    <a href="{{ $partnerContact->photoDocument->file_url }}" download="{{ $partnerContact->photoDocument->file_name }}">
                        {{ $partnerContact->photoDocument->file_name }}
                    </a>
                </span>
                <div class="detail-actions-inline">
                    <a class="action-pill" href="{{ route('partner-contacts.attachments.preview', ['partnerContact' => $partnerContact, 'type' => 'photo']) }}" target="_blank" rel="noopener">
                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                        <span>{{ __('ui.common.actions.open') }}</span>
                    </a>
                    <a class="action-pill" href="{{ route('partner-contacts.attachments.download', ['partnerContact' => $partnerContact, 'type' => 'photo']) }}">
                        <i class="material-icons" aria-hidden="true">file_download</i>
                        <span>{{ __('ui.common.actions.download_file') }}</span>
                    </a>
                    <button
                        class="action-pill action-pill--danger"
                        type="submit"
                        form="partner-contact-attachment-delete-{{ $partnerContact->id }}-photo"
                        data-confirm-message="{{ __('ui.pages.partner_contacts.form.confirm.delete_photo') }}"
                        onclick="return confirm(this.dataset.confirmMessage)"
                    >
                        <i class="material-icons" aria-hidden="true">delete</i>
                        <span>{{ __('ui.pages.partner_contacts.form.actions.delete_photo') }}</span>
                    </button>
                </div>
            @endif
            @error('photo_file')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.cv') }}</span>
            <input type="file" name="cv_file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
            <span class="field-help">{{ __('ui.pages.partner_contacts.form.help.cv_file') }}</span>
            @if ($partnerContact->cvDocument?->file_url)
                <span class="field-help">
                    {{ __('ui.pages.partner_contacts.form.help.current_file') }}:
                    <a href="{{ $partnerContact->cvDocument->file_url }}" download="{{ $partnerContact->cvDocument->file_name }}">
                        {{ $partnerContact->cvDocument->file_name }}
                    </a>
                </span>
                <div class="detail-actions-inline">
                    <a class="action-pill" href="{{ route('partner-contacts.attachments.preview', ['partnerContact' => $partnerContact, 'type' => 'cv']) }}" target="_blank" rel="noopener">
                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                        <span>{{ __('ui.common.actions.open') }}</span>
                    </a>
                    <a class="action-pill" href="{{ route('partner-contacts.attachments.download', ['partnerContact' => $partnerContact, 'type' => 'cv']) }}">
                        <i class="material-icons" aria-hidden="true">file_download</i>
                        <span>{{ __('ui.common.actions.download_file') }}</span>
                    </a>
                    <button
                        class="action-pill action-pill--danger"
                        type="submit"
                        form="partner-contact-attachment-delete-{{ $partnerContact->id }}-cv"
                        data-confirm-message="{{ __('ui.pages.partner_contacts.form.confirm.delete_cv') }}"
                        onclick="return confirm(this.dataset.confirmMessage)"
                    >
                        <i class="material-icons" aria-hidden="true">delete</i>
                        <span>{{ __('ui.pages.partner_contacts.form.actions.delete_cv') }}</span>
                    </button>
                </div>
            @endif
            @error('cv_file')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.partner_contacts.form.labels.description') }}</span>
            <textarea name="description" placeholder="{{ __('ui.pages.partner_contacts.form.placeholders.description') }}">{{ old('description', $partnerContact->description) }}</textarea>
            @error('description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="checkbox-field field--span-2">
            <input type="hidden" name="is_primary" value="0">
            <input type="checkbox" name="is_primary" value="1" @checked($isPrimary)>
            <span>{{ __('ui.pages.partner_contacts.form.labels.is_primary') }}</span>
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('partner-contacts.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>

@if ($partnerContact->exists && $partnerContact->photoDocument)
    <form
        id="partner-contact-attachment-delete-{{ $partnerContact->id }}-photo"
        method="POST"
        action="{{ route('partner-contacts.attachments.destroy', ['partnerContact' => $partnerContact, 'type' => 'photo']) }}"
        hidden
    >
        @csrf
        @method('DELETE')
    </form>
@endif

@if ($partnerContact->exists && $partnerContact->cvDocument)
    <form
        id="partner-contact-attachment-delete-{{ $partnerContact->id }}-cv"
        method="POST"
        action="{{ route('partner-contacts.attachments.destroy', ['partnerContact' => $partnerContact, 'type' => 'cv']) }}"
        hidden
    >
        @csrf
        @method('DELETE')
    </form>
@endif
