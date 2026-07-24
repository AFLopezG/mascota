@extends('layouts.catalogo')

@section('title', 'CampaniaTipo')

@section('content')
<div class="grid">
    <section class="panel">
        <h1>{{ isset($campaniaTipo) ? 'Modificar tipo de campaña' : 'Registrar tipo de campaña' }}</h1>
        <p class="muted">Usa este catálogo para clasificar las campañas desde el backend.</p>

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

        <form method="POST" action="{{ isset($campaniaTipo) ? route('catalogos.campania-tipos.update', $campaniaTipo) : route('catalogos.campania-tipos.store') }}">
            @csrf
            @isset($campaniaTipo)
                @method('PUT')
            @endisset

            <div class="form-grid">
                <div class="field full">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" value="{{ old('nombre', $campaniaTipo->nombre ?? '') }}" required maxlength="255">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn primary">{{ isset($campaniaTipo) ? 'Actualizar' : 'Guardar' }}</button>
                @isset($campaniaTipo)
                    <a href="{{ route('catalogos.campania-tipos') }}" class="btn">Cancelar edición</a>
                @endisset
            </div>
        </form>
    </section>

    <section class="panel stack">
        <div>
            <h2>Tipos registrados</h2>
            <p class="muted">Administración rápida del catálogo.</p>
        </div>

        @forelse ($campaniaTipos as $item)
            <div style="display:flex; justify-content:space-between; gap:12px; padding:14px; border:1px solid rgba(148,163,184,.14); border-radius:16px;">
                <div>
                    <strong>{{ $item->nombre }}</strong>
                </div>
                <a class="btn" href="{{ route('catalogos.campania-tipos.edit', $item) }}">Modificar</a>
            </div>
        @empty
            <div class="empty">No hay tipos de campaña registrados.</div>
        @endforelse
    </section>
</div>
@endsection
