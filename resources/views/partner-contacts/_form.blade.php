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
            <span class="field-label">Hamkor tashkilot</span>
            <select name="partner_organization_id" required>
                <option value="">Hamkor tashkilotni tanlang</option>
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
            <span class="field-label">F.I.Sh (UZ)</span>
            <input type="text" name="full_name_uz" value="{{ old('full_name_uz', $partnerContact->full_name_uz) }}" placeholder="Aliyev Alisher Anvarovich" required>
            @error('full_name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">F.I.Sh (RU)</span>
            <input type="text" name="full_name_ru" value="{{ old('full_name_ru', $partnerContact->full_name_ru) }}" placeholder="Алиев Алишер Анварович" required>
            @error('full_name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">F.I.Sh (KRYL)</span>
            <input type="text" name="full_name_cryl" value="{{ old('full_name_cryl', $partnerContact->full_name_cryl) }}" placeholder="Алиев Алишер Анварович" required>
            @error('full_name_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tug'ilgan sana</span>
            <input type="date" name="birthday" value="{{ old('birthday', $partnerContact->birthday?->format('Y-m-d')) }}">
            @error('birthday')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Lavozimi (UZ)</span>
            <input type="text" name="position_uz" value="{{ old('position_uz', $partnerContact->position_uz) }}" placeholder="Bosh mutaxassis">
            @error('position_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Lavozimi (RU)</span>
            <input type="text" name="position_ru" value="{{ old('position_ru', $partnerContact->position_ru) }}" placeholder="Главный специалист">
            @error('position_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Lavozimi (KRYL)</span>
            <input type="text" name="position_cryl" value="{{ old('position_cryl', $partnerContact->position_cryl) }}" placeholder="Бош мутахассис">
            @error('position_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Email</span>
            <input type="email" name="email" value="{{ old('email', $partnerContact->email) }}" placeholder="contact@example.org">
            <span class="field-help">Email bazada shifrlangan holda saqlanadi.</span>
            @error('email')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Telefon</span>
            <input type="text" name="phone" value="{{ old('phone', $partnerContact->phone) }}" placeholder="+998901234567">
            <span class="field-help">Telefon bazada shifrlangan holda saqlanadi.</span>
            @error('phone')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Foto</span>
            <input type="file" name="photo_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            <span class="field-help">Rasm fayli. Maksimal hajm 5 MB.</span>
            @if ($partnerContact->photoDocument?->file_url)
                <span class="field-help">
                    Joriy fayl:
                    <a href="{{ $partnerContact->photoDocument->file_url }}" download="{{ $partnerContact->photoDocument->file_name }}">
                        {{ $partnerContact->photoDocument->file_name }}
                    </a>
                </span>
                <div class="detail-actions-inline">
                    <a class="action-pill" href="{{ route('partner-contacts.attachments.preview', ['partnerContact' => $partnerContact, 'type' => 'photo']) }}" target="_blank" rel="noopener">
                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                        <span>Ochish</span>
                    </a>
                    <a class="action-pill" href="{{ route('partner-contacts.attachments.download', ['partnerContact' => $partnerContact, 'type' => 'photo']) }}">
                        <i class="material-icons" aria-hidden="true">file_download</i>
                        <span>Faylni olish</span>
                    </a>
                    <button
                        class="action-pill action-pill--danger"
                        type="submit"
                        form="partner-contact-attachment-delete-{{ $partnerContact->id }}-photo"
                        onclick="return confirm('Joriy fotoni o\\'chirishni tasdiqlaysizmi?')"
                    >
                        <i class="material-icons" aria-hidden="true">delete</i>
                        <span>Fotoni o'chirish</span>
                    </button>
                </div>
            @endif
            @error('photo_file')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">CV</span>
            <input type="file" name="cv_file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
            <span class="field-help">PDF yoki Word hujjati. Maksimal hajm 50 MB.</span>
            @if ($partnerContact->cvDocument?->file_url)
                <span class="field-help">
                    Joriy fayl:
                    <a href="{{ $partnerContact->cvDocument->file_url }}" download="{{ $partnerContact->cvDocument->file_name }}">
                        {{ $partnerContact->cvDocument->file_name }}
                    </a>
                </span>
                <div class="detail-actions-inline">
                    <a class="action-pill" href="{{ route('partner-contacts.attachments.preview', ['partnerContact' => $partnerContact, 'type' => 'cv']) }}" target="_blank" rel="noopener">
                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                        <span>Ochish</span>
                    </a>
                    <a class="action-pill" href="{{ route('partner-contacts.attachments.download', ['partnerContact' => $partnerContact, 'type' => 'cv']) }}">
                        <i class="material-icons" aria-hidden="true">file_download</i>
                        <span>Faylni olish</span>
                    </a>
                    <button
                        class="action-pill action-pill--danger"
                        type="submit"
                        form="partner-contact-attachment-delete-{{ $partnerContact->id }}-cv"
                        onclick="return confirm('Joriy CV faylini o\\'chirishni tasdiqlaysizmi?')"
                    >
                        <i class="material-icons" aria-hidden="true">delete</i>
                        <span>CV ni o'chirish</span>
                    </button>
                </div>
            @endif
            @error('cv_file')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Izoh</span>
            <textarea name="description" placeholder="Kontakt bo'yicha qisqa izoh">{{ old('description', $partnerContact->description) }}</textarea>
            @error('description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="checkbox-field field--span-2">
            <input type="hidden" name="is_primary" value="0">
            <input type="checkbox" name="is_primary" value="1" @checked($isPrimary)>
            <span>Ushbu kontakt tashkilotning asosiy kontakti bo'lsin</span>
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
