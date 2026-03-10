<form class="resource-form" method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">Shartnoma raqami</span>
            <input type="text" name="agreement_number" value="{{ old('agreement_number', $agreement->agreement_number) }}" placeholder="MG-2026-001">
            @error('agreement_number')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Davlat</span>
            <select name="country_id" required>
                <option value="">Davlatni tanlang</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $agreement->country_id) === (string) $country->id)>{{ $country->name_uz ?: $country->name_ru }}</option>
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
                    <option value="{{ $partnerOrganization->id }}" @selected((string) old('partner_organization_id', $agreement->partner_organization_id) === (string) $partnerOrganization->id)>{{ $partnerOrganization->name_uz ?: $partnerOrganization->name_ru }}</option>
                @endforeach
            </select>
            @error('partner_organization_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Kelishuv turi</span>
            <select name="agreement_type_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($agreementTypes as $agreementType)
                    <option value="{{ $agreementType->id }}" @selected((string) old('agreement_type_id', $agreement->agreement_type_id) === (string) $agreementType->id)>{{ $agreementType->name_uz }}</option>
                @endforeach
            </select>
            @error('agreement_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Kelishuv yo'nalishi</span>
            <select name="agreement_direction_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($agreementDirections as $agreementDirection)
                    <option value="{{ $agreementDirection->id }}" @selected((string) old('agreement_direction_id', $agreement->agreement_direction_id) === (string) $agreementDirection->id)>{{ $agreementDirection->name_uz }}</option>
                @endforeach
            </select>
            @error('agreement_direction_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Holat</span>
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
            <span class="field-label">Imzolangan sana</span>
            <input type="date" name="signed_date" value="{{ old('signed_date', $agreement->signed_date?->format('Y-m-d')) }}">
            @error('signed_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Boshlanish sanasi</span>
            <input type="date" name="start_date" value="{{ old('start_date', $agreement->start_date?->format('Y-m-d')) }}">
            @error('start_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tugash sanasi</span>
            <input type="date" name="end_date" value="{{ old('end_date', $agreement->end_date?->format('Y-m-d')) }}">
            @error('end_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Javobgar foydalanuvchi</span>
            <select name="responsible_user_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($responsibleUsers as $responsibleUser)
                    <option value="{{ $responsibleUser->id }}" @selected((string) old('responsible_user_id', $agreement->responsible_user_id) === (string) $responsibleUser->id)>{{ $responsibleUser->full_name }}</option>
                @endforeach
            </select>
            @error('responsible_user_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Javobgar bo'lim</span>
            <select name="responsible_department_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($responsibleDepartments as $responsibleDepartment)
                    <option value="{{ $responsibleDepartment->id }}" @selected((string) old('responsible_department_id', $agreement->responsible_department_id) === (string) $responsibleDepartment->id)>{{ $responsibleDepartment->name_uz }}</option>
                @endforeach
            </select>
            @error('responsible_department_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Sarlavha (UZ)</span>
            <input type="text" name="title_uz" value="{{ old('title_uz', $agreement->title_uz) }}" placeholder="O'zaro hamkorlik to'g'risidagi memorandum" required>
            @error('title_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Sarlavha (RU)</span>
            <input type="text" name="title_ru" value="{{ old('title_ru', $agreement->title_ru) }}" placeholder="Меморандум о сотрудничестве" required>
            @error('title_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Sarlavha (KRYL)</span>
            <input type="text" name="title_cryl" value="{{ old('title_cryl', $agreement->title_cryl) }}" placeholder="Ҳамкорлик тўғрисидаги меморандум" required>
            @error('title_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Qisqa sarlavha (UZ)</span>
            <input type="text" name="short_title_uz" value="{{ old('short_title_uz', $agreement->short_title_uz) }}" placeholder="Hamkorlik memorandumi">
            @error('short_title_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Qisqa sarlavha (RU)</span>
            <input type="text" name="short_title_ru" value="{{ old('short_title_ru', $agreement->short_title_ru) }}" placeholder="Меморандум">
            @error('short_title_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Qisqa sarlavha (KRYL)</span>
            <input type="text" name="short_title_cryl" value="{{ old('short_title_cryl', $agreement->short_title_cryl) }}" placeholder="Меморандум">
            @error('short_title_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Tavsif</span>
            <textarea name="description" placeholder="Kelishuv mazmuni, muddatlari yoki qo'shimcha izoh">{{ old('description', $agreement->description) }}</textarea>
            @error('description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('agreements.index') }}">Bekor qilish</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
