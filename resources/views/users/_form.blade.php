@php
    $selectedRole = old('role', $user->roles->first()?->name ?? '');
    $isActive = (bool) old('is_active', $user->exists ? $user->is_active : true);
@endphp

<form class="resource-form" method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">{{ __('ui.pages.users.form.labels.last_name') }}</span>
            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" placeholder="{{ __('ui.pages.users.form.placeholders.last_name') }}" required>
            @error('last_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.users.form.labels.first_name') }}</span>
            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" placeholder="{{ __('ui.pages.users.form.placeholders.first_name') }}" required>
            @error('first_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.users.form.labels.middle_name') }}</span>
            <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}" placeholder="{{ __('ui.pages.users.form.placeholders.middle_name') }}" required>
            @error('middle_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.users.form.labels.phone') }}</span>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="{{ __('ui.pages.users.form.placeholders.phone') }}" required>
            @error('phone')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        @can('edit users')
            <label class="field">
                <span class="field-label">{{ __('ui.pages.users.form.labels.role') }}</span>
                <select name="role" required>
                    <option value="">{{ __('ui.pages.users.form.placeholders.select_role') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected($selectedRole === $role)>{{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $role)) }}</option>
                    @endforeach
                </select>
                @error('role')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>

            <label class="field">
                <span class="field-label">{{ __('ui.pages.users.form.labels.rank') }}</span>
                <select name="rank_id" required>
                    <option value="">{{ __('ui.pages.users.form.placeholders.select_rank') }}</option>
                    @foreach ($ranks as $rank)
                        <option value="{{ $rank->id }}" @selected((string) old('rank_id', $user->rank_id) === (string) $rank->id)>{{ $rank->display_name }}</option>
                    @endforeach
                </select>
                @error('rank_id')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>

            <label class="field">
                <span class="field-label">{{ __('ui.pages.users.form.labels.department') }}</span>
                <select name="department_id">
                    <option value="">{{ __('ui.common.values.unassigned') }}</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id', $user->department_id) === (string) $department->id)>{{ $department->display_name }}</option>
                    @endforeach
                </select>
                @error('department_id')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>
        @endcan

        <label class="field">
            <span class="field-label">{{ __('ui.pages.users.form.labels.password') }}</span>
            <input type="password" name="password" placeholder="{{ $user->exists ? __('ui.pages.users.form.placeholders.password_edit') : __('ui.pages.users.form.placeholders.password_create') }}" {{ $user->exists ? '' : 'required' }}>
            <span class="field-help">{{ $user->exists ? __('ui.pages.users.form.help.password_edit') : __('ui.pages.users.form.help.password_create') }}</span>
            @error('password')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.users.form.labels.position_uz') }}</span>
            <input type="text" name="position_uz" value="{{ old('position_uz', $user->position_uz) }}" placeholder="{{ __('ui.pages.users.form.placeholders.position_uz') }}">
            @error('position_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.users.form.labels.position_ru') }}</span>
            <input type="text" name="position_ru" value="{{ old('position_ru', $user->position_ru) }}" placeholder="{{ __('ui.pages.users.form.placeholders.position_ru') }}">
            @error('position_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        @can('edit users')
            <label class="checkbox-field field--span-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked($isActive)>
                <span>{{ __('ui.pages.users.form.labels.is_active') }}</span>
            </label>
        @endcan
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('users.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
