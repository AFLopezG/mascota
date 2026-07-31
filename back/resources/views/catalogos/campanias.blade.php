@extends('layouts.catalogo')

@section('title', 'Campanias')

@php
    $isEditing = isset($campania);
    $isLocked = $isEditing && $campania->isLocked();
@endphp

@section('content')
<div class="grid">
    <section class="panel">
        <h1>{{ $isEditing ? 'Modificar campania' : 'Registrar campania' }}</h1>
        <p class="muted">Registra las campanias con sus fechas, lugar y tipo. Las campanias vencidas o anuladas quedan en solo lectura.</p>

        @if ($errors->any())
            <div class="errors">
                <strong>Revisa el formulario.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($isLocked)
            <div class="flash" style="background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.35);">
                Esta campania ya finalizo o fue anulada y no puede modificarse.
            </div>
        @endif

        <form method="POST" action="{{ $isEditing ? route('catalogos.campanias.update', $campania) : route('catalogos.campanias.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="field full">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" value="{{ old('nombre', $campania->nombre ?? '') }}" required maxlength="255" {{ $isLocked ? 'disabled' : '' }}>
                </div>

                <div class="field">
                    <label for="fec_ini">Fecha inicio</label>
                    <input id="fec_ini" name="fec_ini" type="date" value="{{ old('fec_ini', isset($campania) ? optional($campania->fec_ini)->format('Y-m-d') : '') }}" required {{ $isLocked ? 'disabled' : '' }}>
                </div>

                <div class="field">
                    <label for="fec_fin">Fecha fin</label>
                    <input id="fec_fin" name="fec_fin" type="date" value="{{ old('fec_fin', isset($campania) ? optional($campania->fec_fin)->format('Y-m-d') : '') }}" required {{ $isLocked ? 'disabled' : '' }}>
                </div>

                <div class="field full">
                    <label for="lugar">Lugar</label>
                    <input id="lugar" name="lugar" value="{{ old('lugar', $campania->lugar ?? '') }}" required maxlength="255" {{ $isLocked ? 'disabled' : '' }}>
                </div>

                <div class="field full">
                    <label for="campania_tipo_id">Tipo de campania</label>
                    <select id="campania_tipo_id" name="campania_tipo_id" required {{ $isLocked ? 'disabled' : '' }}>
                        <option value="">Seleccione una opcion</option>
                        @foreach ($campaniaTipos as $tipo)
                            <option value="{{ $tipo->id }}" @selected(old('campania_tipo_id', $campania->campania_tipo_id ?? '') == $tipo->id)>{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field full">
                    <label for="estado">Estado</label>
                    <input id="estado" name="estado" value="{{ old('estado', $campania->estado ?? '') }}" required maxlength="50" {{ $isLocked ? 'disabled' : '' }}>
                </div>

                <div class="field full">
                    <label for="descripcion">Descripcion</label>
                    <textarea id="descripcion" name="descripcion" maxlength="1000" {{ $isLocked ? 'disabled' : '' }}>{{ old('descripcion', $campania->descripcion ?? '') }}</textarea>
                </div>
            </div>

            <div class="actions">
                @if ($isLocked)
                    <a href="{{ route('catalogos.campanias') }}" class="btn">Volver</a>
                @else
                    <button type="submit" class="btn primary">{{ $isEditing ? 'Actualizar' : 'Guardar' }}</button>
                    @if ($isEditing)
                        <a href="{{ route('catalogos.campanias') }}" class="btn">Cancelar edicion</a>
                    @endif
                @endif
            </div>
        </form>
    </section>

    <section class="panel stack">
        <div>
            <h2>Campanias registradas</h2>
            <p class="muted">Las campanias finalizadas no muestran acciones de edicion.</p>
        </div>

        @forelse ($campanias as $item)
            <div style="display:grid; gap:10px; padding:14px; border:1px solid rgba(148,163,184,.14); border-radius:16px;">
                <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start;">
                    <div>
                        <strong>{{ $item->nombre }}</strong>
                        <div class="muted">{{ $item->campaniaTipo?->nombre ?? 'Sin tipo' }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div>{{ optional($item->fec_ini)->format('Y-m-d') }} a {{ optional($item->fec_fin)->format('Y-m-d') }}</div>
                        <div class="muted">{{ $item->lugar }}</div>
                    </div>
                </div>

                @if (!empty($item->descripcion))
                    <div class="muted">{{ $item->descripcion }}</div>
                @endif

                <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap;">
                    <span class="btn" style="cursor:default;">
                        {{ $item->isAnulada() ? 'Anulada' : ($item->isExpired() ? 'Finalizada' : 'Vigente') }}
                    </span>
                    @if (!$item->isLocked())
                        <div class="actions" style="margin-top:0;">
                            <a class="btn" href="{{ route('catalogos.campanias.edit', $item) }}">Modificar</a>
                            <form method="POST" action="{{ route('catalogos.campanias.anular', $item) }}" onsubmit="return confirm('Anular esta campania?');">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn">Anular</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty">No hay campanias registradas.</div>
        @endforelse
    </section>
</div>
@endsection
