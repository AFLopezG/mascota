<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Catálogos')</title>
    <style>
        :root {
            --bg: #0f172a;
            --panel: #111827;
            --line: #334155;
            --text: #e5e7eb;
            --muted: #94a3b8;
            --accent: #22c55e;
            --accent-2: #38bdf8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(56,189,248,.18), transparent 28%),
                radial-gradient(circle at top right, rgba(34,197,94,.14), transparent 26%),
                var(--bg);
            color: var(--text);
        }
        a { color: inherit; text-decoration: none; }
        .shell { max-width: 1180px; margin: 0 auto; padding: 28px 18px 48px; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center; gap: 16px;
            padding: 18px 20px; background: rgba(17,24,39,.88); border: 1px solid rgba(148,163,184,.18);
            border-radius: 20px; backdrop-filter: blur(12px);
        }
        .brand { display: grid; gap: 4px; }
        .brand strong { font-size: 1.05rem; letter-spacing: .04em; text-transform: uppercase; }
        .brand span { color: var(--muted); font-size: .92rem; }
        .nav { display: flex; flex-wrap: wrap; gap: 10px; }
        .nav a, .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 14px; border-radius: 999px; border: 1px solid rgba(148,163,184,.22);
            background: rgba(255,255,255,.04); color: var(--text);
        }
        .nav a.active, .btn.primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #08111f; border-color: transparent; font-weight: 700;
        }
        .grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 18px; margin-top: 18px; }
        .panel {
            background: rgba(17,24,39,.9); border: 1px solid rgba(148,163,184,.18);
            border-radius: 20px; padding: 20px;
        }
        .panel h1, .panel h2 { margin: 0 0 10px; }
        .muted { color: var(--muted); }
        .flash { margin: 16px 0 0; padding: 12px 14px; border-radius: 14px; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.3); }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { display: grid; gap: 7px; }
        .field.full { grid-column: 1 / -1; }
        label { font-size: .92rem; color: var(--muted); }
        input, select, textarea {
            width: 100%; border-radius: 14px; border: 1px solid rgba(148,163,184,.22);
            background: rgba(15,23,42,.8); color: var(--text); padding: 12px 14px; outline: none;
        }
        textarea { min-height: 110px; resize: vertical; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px; }
        .stack { display: grid; gap: 14px; }
        .errors { padding: 12px 14px; border-radius: 14px; background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.35); }
        .errors ul { margin: 8px 0 0; padding-left: 18px; }
        .empty { padding: 18px; border: 1px dashed rgba(148,163,184,.24); border-radius: 16px; color: var(--muted); }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="topbar">
            <div class="brand">
                <strong>Catálogos</strong>
                <span>Administración de especies, razas y tipos de campaña</span>
            </div>
            <div class="nav">
                <a href="{{ route('catalogos.especies') }}" class="{{ request()->routeIs('catalogos.especies*') ? 'active' : '' }}">Especies</a>
                <a href="{{ route('catalogos.razas') }}" class="{{ request()->routeIs('catalogos.razas*') ? 'active' : '' }}">Razas</a>
                <a href="{{ route('catalogos.campania-tipos') }}" class="{{ request()->routeIs('catalogos.campania-tipos*') ? 'active' : '' }}">CampaniaTipo</a>
            </div>
        </div>

        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
