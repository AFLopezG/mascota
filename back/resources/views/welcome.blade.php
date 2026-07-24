<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MasCota</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at top left, rgba(56,189,248,.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(34,197,94,.14), transparent 28%),
                #0f172a;
            color: #e5e7eb;
        }
        .card {
            width: min(820px, calc(100vw - 32px));
            padding: 32px;
            border-radius: 28px;
            background: rgba(17, 24, 39, 0.88);
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.28);
        }
        h1 { margin: 0 0 8px; font-size: clamp(2rem, 4vw, 3rem); }
        p { margin: 0; color: #94a3b8; line-height: 1.6; }
        .links { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 22px; }
        a {
            color: #0f172a;
            background: linear-gradient(135deg, #22c55e, #38bdf8);
            padding: 12px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Administración de catálogos</h1>
        <p>Ingresa a las vistas para registrar o modificar especies, razas y tipos de campaña.</p>
        <div class="links">
            <a href="{{ route('catalogos.especies') }}">Especies</a>
            <a href="{{ route('catalogos.razas') }}">Razas</a>
            <a href="{{ route('catalogos.campania-tipos') }}">CampaniaTipo</a>
        </div>
    </main>
</body>
</html>
