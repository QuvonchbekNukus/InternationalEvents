<form class="resource-form" method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">Nomi (RU)</span>
            <input type="text" name="name_ru" value="{{ old('name_ru', $country->name_ru) }}" placeholder="Казахстан" required>
            @error('name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Nomi (UZ)</span>
            <input type="text" name="name_uz" value="{{ old('name_uz', $country->name_uz) }}" placeholder="Qozog'iston">
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
            <span class="field-label">Mintaqa (RU)</span>
            <input type="text" name="region_ru" value="{{ old('region_ru', $country->region_ru) }}" placeholder="Центральная Азия">
            @error('region_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Mintaqa (UZ)</span>
            <input type="text" name="region_uz" value="{{ old('region_uz', $country->region_uz) }}" placeholder="Markaziy Osiyo">
            @error('region_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Latitude</span>
            <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $country->latitude) }}" placeholder="48.0196000">
            @error('latitude')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Longitude</span>
            <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $country->longitude) }}" placeholder="66.9237000">
            @error('longitude')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Hamkorlik holati</span>
            <select name="cooperation_status" required>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('cooperation_status', $country->cooperation_status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            @error('cooperation_status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Izoh</span>
            <textarea name="notes" placeholder="Hamkorlikning qisqa tavsifi">{{ old('notes', $country->notes) }}</textarea>
            @error('notes')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('countries.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
