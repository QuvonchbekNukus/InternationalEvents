<form class="resource-form" method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">{{ __('ui.pages.departments.form.labels.name_uz') }}</span>
            <input type="text" name="name_uz" value="{{ old('name_uz', $department->name_uz) }}" placeholder="{{ __('ui.pages.departments.form.placeholders.name_uz') }}" required>
            @error('name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.departments.form.labels.name_ru') }}</span>
            <input type="text" name="name_ru" value="{{ old('name_ru', $department->name_ru) }}" placeholder="{{ __('ui.pages.departments.form.placeholders.name_ru') }}" required>
            @error('name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.departments.form.labels.code') }}</span>
            <input type="text" name="code" value="{{ old('code', $department->code) }}" placeholder="XAB">
            @error('code')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.departments.form.labels.description') }}</span>
            <textarea name="description" placeholder="{{ __('ui.pages.departments.form.placeholders.description') }}">{{ old('description', $department->description) }}</textarea>
            @error('description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('departments.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
