<form class="resource-form" method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreement_types.form.labels.name_uz') }}</span>
            <input type="text" name="name_uz" value="{{ old('name_uz', $agreementType->name_uz) }}" placeholder="{{ __('ui.pages.agreement_types.form.placeholders.name_uz') }}" required>
            @error('name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.agreement_types.form.labels.name_ru') }}</span>
            <input type="text" name="name_ru" value="{{ old('name_ru', $agreementType->name_ru) }}" placeholder="{{ __('ui.pages.agreement_types.form.placeholders.name_ru') }}" required>
            @error('name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('agreement-types.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
