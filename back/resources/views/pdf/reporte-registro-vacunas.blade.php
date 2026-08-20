<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: letter landscape;
            margin: 22mm 20mm;
        }

        html, body {
            margin: 0.2cm;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 10px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .header__title,
        .header__meta {
            display: table-cell;
            vertical-align: top;
        }

        .header__meta {
            text-align: right;
        }

        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 1.6px;
            color: #0f766e;
            font-size: 9px;
            font-weight: bold;
        }

        h1 {
            margin: 2px 0 4px;
            font-size: 20px;
            line-height: 1.05;
        }

        .subtitle {
            color: #475569;
            line-height: 1.35;
        }

        .meta-box {
            display: inline-block;
            text-align: left;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 8px 10px;
            min-width: 180px;
            background: #f8fafc;
        }

        .meta-box div {
            margin-bottom: 3px;
        }

        .section {
            margin-top: 10px;
        }

        .section__title {
            font-size: 11px;
            font-weight: bold;
            margin: 0 0 6px;
            color: #0f172a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table th,
        .summary-table td,
        .detail-table th,
        .detail-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 7px;
            vertical-align: top;
        }

        .summary-table th,
        .detail-table th {
            background: #0f172a;
            color: #ffffff;
            text-align: left;
            font-size: 9px;
        }

        .summary-table td:last-child,
        .detail-table td:last-child {
            text-align: right;
        }

        .muted {
            color: #64748b;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: bold;
            background: #e2e8f0;
            color: #0f172a;
        }

        .badge--positive {
            background: #dcfce7;
            color: #166534;
        }

        .badge--warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge--accent {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .footer {
            margin-top: 10px;
            font-size: 8px;
            color: #64748b;
            text-align: right;
        }
    </style>
</head>
<body>
@php
    $filters = $filters ?? [];
    $rows = $pdf_rows ?? collect();
    $summary = $summary ?? [];
    $especies = collect($summary['especies'] ?? []);
    $edades = collect($summary['edad'] ?? []);
    $healthCenters = collect($summary['health_centers'] ?? []);
    $fechaDesde = $filters['fecha_desde'] ?? '-';
    $fechaHasta = $filters['fecha_hasta'] ?? '-';
    $generatedAt = $generated_at ?? now()->format('d/m/Y H:i');
@endphp

<div class="header">
    <div class="header__title">
        <div class="eyebrow">Sistema de Informacion Municipal de Canes</div>
        <h1>Reporte de vacunas</h1>
        <div class="subtitle">
            Consolidado del rango seleccionado con totales por especie, edad y centro de salud.
        </div>
    </div>
    <div class="header__meta">
        <div class="meta-box">
            <div><strong>Desde:</strong> {{ $fechaDesde }}</div>
            <div><strong>Hasta:</strong> {{ $fechaHasta }}</div>
            <div><strong>Generado:</strong> {{ $generatedAt }}</div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section__title">Totales por especie</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Especie</th>
                <th style="width: 18%;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($especies as $item)
            <tr>
                <td>{{ $item['nombre'] ?? 'SIN ESPECIE' }}</td>
                <td>{{ $item['cantidad'] ?? 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="muted">Sin registros</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <div class="section__title">Totales por edad</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Edad</th>
                <th style="width: 18%;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($edades as $item)
            <tr>
                <td>{{ $item['valor'] ?? '-' }}</td>
                <td>{{ $item['cantidad'] ?? 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="muted">Sin registros</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <div class="section__title">Totales por centro de salud</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Centro de salud</th>
                <th style="width: 18%;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($healthCenters as $item)
            <tr>
                <td>{{ $item['nombre'] ?? 'SIN CENTRO DE SALUD' }}</td>
                <td>{{ $item['cantidad'] ?? 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="muted">Sin registros</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="footer">
    Reporte generado.
</div>
</body>
</html>
