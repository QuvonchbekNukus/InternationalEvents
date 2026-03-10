@php
    $isPrimary = (bool) old('is_primary', $partnerContact->exists ? $partnerContact->is_primary : false);
@endphp

<form class="resource-form" method="POST" action="{{ $action }}">
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
                        {{ $partnerOrganization->name_uz ?: $partnerOrganization->name_ru }}{{ $partnerOrganization->country?->iso2 ? ' ('.$partnerOrganization->country->iso2.')' : '' }}
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
        <a class="btn btn--ghost" href="{{ route('partner-contacts.index') }}">Bekor qilish</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
