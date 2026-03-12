<form class="resource-form" method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">Nomi (UZ)</span>
            <input type="text" name="name_uz" value="{{ old('name_uz', $eventType->name_uz) }}" placeholder="Seminar" required>
            @error('name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Nomi (RU)</span>
            <input type="text" name="name_ru" value="{{ old('name_ru', $eventType->name_ru) }}" placeholder="Семинар" required>
            @error('name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Nomi (KRYL)</span>
            <input type="text" name="name_cryl" value="{{ old('name_cryl', $eventType->name_cryl) }}" placeholder="Семинар" required>
            @error('name_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('event-types.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
