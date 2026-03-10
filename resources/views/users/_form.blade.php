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
            <span class="field-label">Familiya</span>
            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" placeholder="Ibrohimov" required>
            @error('last_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Ism</span>
            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" placeholder="Imron" required>
            @error('first_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Otasining ismi</span>
            <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}" placeholder="Abdulla o'g'li" required>
            @error('middle_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Telefon</span>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="998901234567" required>
            @error('phone')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        @can('edit users')
            <label class="field">
                <span class="field-label">Rol</span>
                <select name="role" required>
                    <option value="">Rolni tanlang</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected($selectedRole === $role)>{{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $role)) }}</option>
                    @endforeach
                </select>
                @error('role')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>

            <label class="field">
                <span class="field-label">Unvon</span>
                <select name="rank_id" required>
                    <option value="">Unvonni tanlang</option>
                    @foreach ($ranks as $rank)
                        <option value="{{ $rank->id }}" @selected((string) old('rank_id', $user->rank_id) === (string) $rank->id)>{{ $rank->name_uz }}</option>
                    @endforeach
                </select>
                @error('rank_id')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>

            <label class="field">
                <span class="field-label">Bo'lim</span>
                <select name="department_id">
                    <option value="">Biriktirilmagan</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id', $user->department_id) === (string) $department->id)>{{ $department->name_uz }}</option>
                    @endforeach
                </select>
                @error('department_id')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>
        @endcan

        <label class="field">
            <span class="field-label">Parol</span>
            <input type="password" name="password" placeholder="{{ $user->exists ? 'Ozgartirish uchun yangi parol kiriting' : 'Kamida 6 ta belgidan iborat' }}" {{ $user->exists ? '' : 'required' }}>
            <span class="field-help">{{ $user->exists ? "Bo'sh qoldirilsa mavjud parol saqlanadi." : "Yangi foydalanuvchi uchun vaqtinchalik parol." }}</span>
            @error('password')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Lavozimi (UZ)</span>
            <input type="text" name="position_uz" value="{{ old('position_uz', $user->position_uz) }}" placeholder="Yetakchi mutaxassis">
            @error('position_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Lavozimi (RU)</span>
            <input type="text" name="position_ru" value="{{ old('position_ru', $user->position_ru) }}" placeholder="Ведущий специалист">
            @error('position_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Lavozimi (KRYL)</span>
            <input type="text" name="position_cryl" value="{{ old('position_cryl', $user->position_cryl) }}" placeholder="Етакчи мутахассис">
            @error('position_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        @can('edit users')
            <label class="checkbox-field field--span-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked($isActive)>
                <span>Foydalanuvchi faol holatda bo'lsin</span>
            </label>
        @endcan
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('users.index') }}">Bekor qilish</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>
