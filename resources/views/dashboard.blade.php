<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #08172c;
            --panel: rgba(10, 27, 49, 0.88);
            --panel-border: rgba(150, 180, 220, 0.16);
            --text: #e5eefc;
            --muted: #9fb2d4;
            --primary: #2563eb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.22), transparent 28%),
                linear-gradient(135deg, #071324, #0d2747 50%, #071324);
        }

        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        .topbar,
        .panel {
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 18px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.24);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            margin-bottom: 24px;
        }

        .title {
            margin: 0;
            font-size: 24px;
        }

        .subtitle {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn,
        .btn-secondary {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            text-decoration: none;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }

        .btn {
            background: linear-gradient(90deg, #1d4ed8, #2563eb);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .panel {
            padding: 28px;
        }

        .panel h2 {
            margin: 0 0 10px;
            font-size: 20px;
        }

        .panel p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        @media (max-width: 700px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <section class="topbar">
            <div>
                <h1 class="title">Boshqaruv paneli</h1>
                <p class="subtitle">
                    {{ trim(auth()->user()->first_name.' '.auth()->user()->middle_name.' '.auth()->user()->last_name) }}
                </p>
            </div>

            <div class="actions">
                @if (Route::has('profile.edit'))
                    <a class="btn-secondary" href="{{ route('profile.edit') }}">Profil</a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn">Chiqish</button>
                </form>
            </div>
        </section>

        <section class="panel">
            <h2>Tizimga kirish muvaffaqiyatli bajarildi</h2>
            <p>Autentifikatsiya endi faqat telefon raqami va parol orqali ishlaydi. Bu sahifa vaqtinchalik asosiy kabinet sifatida turibdi.</p>
        </section>
    </div>
</body>
</html>
