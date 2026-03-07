<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #09182d;
            --panel: rgba(11, 28, 51, 0.9);
            --panel-border: rgba(166, 190, 225, 0.14);
            --text: #e5eefc;
            --muted: #9eb3d8;
            --input-bg: rgba(255, 255, 255, 0.04);
            --input-border: rgba(160, 184, 220, 0.16);
            --primary: #2563eb;
            --danger: #dc2626;
            --success: #22c55e;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.18), transparent 24%),
                linear-gradient(135deg, #061222, #0c2442 55%, #061222);
        }

        .page {
            max-width: 960px;
            margin: 0 auto;
            padding: 28px 18px 40px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
        }

        .topbar a {
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
        }

        .status {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #bbf7d0;
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.2);
        }

        .panel h2 {
            margin: 0 0 14px;
            font-size: 20px;
        }

        .fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            color: var(--muted);
        }

        input {
            width: 100%;
            height: 44px;
            border-radius: 10px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text);
            padding: 0 12px;
            outline: none;
        }

        .error {
            margin-top: 6px;
            font-size: 12px;
            color: #fca5a5;
        }

        .actions {
            margin-top: 16px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            border-radius: 10px;
            padding: 12px 16px;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(90deg, #1d4ed8, #2563eb);
        }

        .btn-danger {
            background: linear-gradient(90deg, #b91c1c, #dc2626);
        }

        @media (max-width: 700px) {
            .fields {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <h1>Profil</h1>
            <a href="{{ route('dashboard') }}">Dashboardga qaytish</a>
        </div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <div class="grid">
            <section class="panel">
                <h2>Profil ma'lumotlari</h2>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="fields">
                        <div>
                            <label for="first_name">Ism</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="middle_name">Otasining ismi</label>
                            <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name', $user->middle_name) }}" required>
                            @error('middle_name')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name">Familiya</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" required>
                            @error('last_name')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="phone">Telefon</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" required>
                            @error('phone')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Saqlash</button>
                    </div>
                </form>
            </section>

            <section class="panel">
                <h2>Parolni yangilash</h2>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="fields">
                        <div class="field-full">
                            <label for="current_password">Joriy parol</label>
                            <input id="current_password" name="current_password" type="password" required>
                            @error('current_password', 'updatePassword')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password">Yangi parol</label>
                            <input id="password" name="password" type="password" required>
                            @error('password', 'updatePassword')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation">Parolni tasdiqlang</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required>
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Parolni yangilash</button>
                    </div>
                </form>
            </section>

            <section class="panel">
                <h2>Akkauntni o'chirish</h2>

                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <div class="fields">
                        <div class="field-full">
                            <label for="delete_password">Parol</label>
                            <input id="delete_password" name="password" type="password" required>
                            @error('password', 'userDeletion')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-danger">Akkauntni o'chirish</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</body>
</html>
