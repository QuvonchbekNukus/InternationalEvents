<form class="resource-form" method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">Davlat</span>
            <select name="country_id" required>
                <option value="">Davlatni tanlang</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $partnerOrganization->country_id) === (string) $country->id)>{{ $country->name_uz ?: $country->name_ru }}</option>
                @endforeach
            </select>
            @error('country_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tashkilot turi</span>
            <select name="organization_type_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($organizationTypes as $organizationType)
                    <option value="{{ $organizationType->id }}" @selected((string) old('organization_type_id', $partnerOrganization->organization_type_id) === (string) $organizationType->id)>{{ $organizationType->name_uz }}</option>
                @endforeach
            </select>
            @error('organization_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Nomi (UZ)</span>
            <input type="text" name="name_uz" value="{{ old('name_uz', $partnerOrganization->name_uz) }}" placeholder="Ichki ishlar vazirligi" required>
            @error('name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Nomi (RU)</span>
            <input type="text" name="name_ru" value="{{ old('name_ru', $partnerOrganization->name_ru) }}" placeholder="Министерство внутренних дел" required>
            @error('name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Nomi (KRYL)</span>
            <input type="text" name="name_cryl" value="{{ old('name_cryl', $partnerOrganization->name_cryl) }}" placeholder="Ички ишлар вазирлиги" required>
            @error('name_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Qisqa nom</span>
            <input type="text" name="short_name" value="{{ old('short_name', $partnerOrganization->short_name) }}" placeholder="IIV">
            @error('short_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Holat</span>
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
            <span class="field-label">Shahar</span>
            <input type="text" name="city" value="{{ old('city', $partnerOrganization->city) }}" placeholder="Toshkent">
            @error('city')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Website</span>
            <input type="text" name="website" value="{{ old('website', $partnerOrganization->website) }}" placeholder="example.org">
            @error('website')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Manzil</span>
            <input type="text" name="address" value="{{ old('address', $partnerOrganization->address) }}" placeholder="Amir Temur ko'chasi, 10-uy">
            @error('address')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Izoh</span>
            <textarea name="notes" placeholder="Hamkorlik bo'yicha qisqa izoh">{{ old('notes', $partnerOrganization->notes) }}</textarea>
            @error('notes')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('partner-organizations.index') }}">Bekor qilish</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
