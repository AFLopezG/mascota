<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: letter portrait;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(14,165,233,.12), transparent 24%),
                radial-gradient(circle at top right, rgba(15,118,110,.10), transparent 22%),
                radial-gradient(circle at bottom left, rgba(59,130,246,.06), transparent 20%),
                linear-gradient(180deg, #f8fafc 0%, #ffffff 46%, #eef4ff 100%);
        }

        .sheet {
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            padding: 6mm 3mm 0mm 0;
            position: relative;
        }

        .sheet::before,
        .sheet::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            opacity: .35;
        }

        .sheet::before {
            width: 70mm;
            height: 70mm;
            top: -5mm;
            left: -5mm;
            background: rgba(14,165,233,.10);
        }

        .sheet::after {
            width: 50mm;
            height: 50mm;
            right: -5mm;
            bottom: -5mm;
            background: rgba(15,118,110,.08);
        }

        .cards {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 2.5mm 0;
            position: relative;
            z-index: 1;
        }

        .card-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: center;
        }

        .card {
            width: 87mm;
            height: 52mm;
            display: inline-block;
            box-sizing: border-box;
            border-radius: 3.4mm;
            overflow: hidden;
            border: 0.35mm solid #c0ceda;
            background: #ffffff;
            position: relative;
            padding: 2.8mm;
            box-shadow:
                0 8px 18px rgba(15,23,42,.10),
                inset 0 0 0 0.2mm rgba(255,255,255,.8);
        }

        .card--front {
            background:
                linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,250,252,.98)),
                linear-gradient(135deg, #ffffff 0%, #eef6ff 56%, #f5fdff 100%);
        }

        .card--back {
            background:
                radial-gradient(circle at top right, rgba(14,165,233,.10), transparent 28%),
                radial-gradient(circle at bottom left, rgba(16,185,129,.08), transparent 20%),
                linear-gradient(180deg, #ffffff 0%, #eef2ff 100%);
        }

        .card__top {
            display: table;
            width: 100%;
            margin-bottom: 1.2mm;
        }

        .card__top-left,
        .card__top-right {
            display: table-cell;
            vertical-align: top;
        }

        .card__top-right {
            text-align: right;
        }

        .card__label {
            font-size: 4.2px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #0f766e;
        }

        .card__title {
            font-size: 7.4px;
            font-weight: bold;
            line-height: 1.1;
        }

        .card__badge {
            display: inline-block;
            padding: 0.6mm 2mm;
            border-radius: 999px;
            background: #dbeafe;
            font-size: 5px;
            font-weight: bold;
        }

        .front-layout {
            display: table;
            width: 100%;
        }

        .front-photo {
            display: table-cell;
            width: 21mm;
            vertical-align: top;
        }

        .front-photo__box {
            width: 19mm;
            height: 27mm;
            border-radius: 2mm;
            overflow: hidden;
            background: #e2e8f0;
            border: 0.25mm solid #cbd5e1;
        }

        .front-photo__box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .front-photo__empty {
            width: 100%;
            height: 100%;
            display: table;
            text-align: center;
            color: #475569;
            font-size: 8px;
        }

        .front-photo__empty span {
            display: table-cell;
            vertical-align: middle;
            padding: 3mm;
        }

        .front-details {
            display: table-cell;
            vertical-align: top;
            padding-left: 1.7mm;
        }

        .front-row {
            margin-bottom: 0.7mm;
            padding: 0.75mm 0.9mm;
            border-radius: 1.2mm;
            background: #f8fafc;
            border: 0.2mm solid #e2e8f0;
            font-size: 4.8px;
            line-height: 1.15;
        }

        .front-row strong {
            display: block;
            font-size: 5.8px;
            margin-top: 0.25mm;
        }

        .owner {
            margin-top: 0.8mm;
            padding: 1mm 1.6mm;
            border-radius: 1.5mm;
            background: linear-gradient(135deg, #0f766e 0%, #0ea5e9 100%);
            color: #ffffff;
            font-size: 4.6px;
        }

        .owner__label {
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 4px;
            color: rgba(255,255,255,.78);
        }

        .owner__name {
            font-size: 6px;
            font-weight: bold;
            margin-top: 0.25mm;
        }

        .owner__meta {
            margin-top: 0.2mm;
            font-size: 4.2px;
            line-height: 1.25;
        }

        .back-qr {
            display: table;
            width: 100%;
            padding: 1.4mm;
            border-radius: 2mm;
            background:
                linear-gradient(135deg, rgba(255,255,255,.98), rgba(248,250,252,.95)),
                radial-gradient(circle at top left, rgba(14,165,233,.06), transparent 38%),
                radial-gradient(circle at bottom right, rgba(16,185,129,.05), transparent 34%);
            border: 0.2mm solid #d6e4f1;
            box-sizing: border-box;
            margin-bottom: 3.2mm;
        }

        .back-qr__img {
            display: table-cell;
            width: 18mm;
            vertical-align: middle;
        }

        .back-qr__img img {
            width: 26mm;
            height: 26mm;
            display: block;
        }

        .back-qr__text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 2.2mm;
            font-size: 4.6px;
            line-height: 1.25;
        }

        .back-owner {
            display: grid;
            gap: 1mm;
        }

        .back-owner__label {
            font-size: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f766e;
        }

        .back-owner__name {
            font-size: 6.4px;
            font-weight: bold;
            color: #0f172a;
        }

        .back-owner__meta {
            font-size: 4.6px;
            color: #475569;
        }
    </style>
</head>
<body>
@php
    $mascota = $mascota ?? [];
    $persona = $persona ?? [];
    $nombrePropietario = trim(($persona['nombre'] ?? '') . ' ' . ($persona['paterno'] ?? '') . ' ' . ($persona['materno'] ?? ''));
    $nombrePropietario = $nombrePropietario !== '' ? $nombrePropietario : '-';
    $telefonoPropietario = trim((string) ($persona['telefono'] ?? ''));
    if ($telefonoPropietario === '') {
        $telefonoPropietario = trim((string) ($persona['celular'] ?? ''));
    }
    $telefonoPropietario = $telefonoPropietario !== '' ? $telefonoPropietario : '-';
@endphp

<div class="sheet">
    <div class="cards">
        <div class="card-cell">
            <div class="card card--front">
                <div class="card__top">
                    <div class="card__top-left">
                        <div class="card__label">Gobierno Autonomo Municipal de Oruro</div>
                        <div class="card__title">Unidad de Zoonosis</div>
                    </div>
                    <div class="card__top-right">
                        <div class="card__badge">{{ $mascota['codigo'] ?? '-' }}</div>
                    </div>
                </div>
                
                <div class="front-layout">
                    <div class="front-photo">
                        <div class="front-photo__box">
                            @if(!empty($mascota['fotoUrl']))
                                <img src="{{ $mascota['fotoUrl'] }}" alt="Foto de mascota">
                            @else
                                <div class="front-photo__empty">
                                    <span>Sin foto</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="front-details">
                        <div class="front-row">
                            Nombre
                            <strong>{{ $mascota['nombre'] ?? '-' }}</strong>
                        </div>
                        <div class="front-row">
                            Especie
                            <strong>{{ $mascota['especie'] ?? '-' }}</strong>
                        </div>
                        <div class="front-row">
                            Raza
                            <strong>{{ $mascota['raza'] ?? '-' }}</strong>
                        </div>
                        <div class="front-row">
                            Color
                            <strong>{{ trim(($mascota['color_principal'] ?? '') . ' / ' . ($mascota['color_secundario'] ?? '')) ?: '-' }}</strong>
                        </div>
                        <div class="front-row">
                            Tamano
                            <strong>{{ $mascota['tamano'] ?? '-' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="owner">
                    <div class="owner__label">Propietario</div>
                    <div class="owner__name">{{ $nombrePropietario }}</div>
                    <div class="owner__meta">
                        Tel/Cel: {{ $telefonoPropietario }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-cell">
            <div class="card card--back">
                <div class="card__top">

                </div>

                <div class="back-qr">
                    <div class="back-qr__img">
                        @if(!empty($mascota['qrSrc']))
                            <img src="{{ $mascota['qrSrc'] }}" alt="QR de mascota">
                        @endif
                    </div>
                    <div class="back-qr__text">
                        <div class="back-owner">
                            <div class="back-owner__label">Propietario</div>
                            <div class="back-owner__name">{{ $nombrePropietario }}</div>
                            <div class="back-owner__meta">Tel/Cel: {{ $telefonoPropietario }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
