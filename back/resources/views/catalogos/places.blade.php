@extends('layouts.catalogo')

@section('title', 'Lugares')

@section('content')
<div class="grid">
    <section class="panel">
        <h1>{{ isset($place) ? 'Modificar lugar' : 'Registrar lugar' }}</h1>
        <p class="muted">Catálogo simple para los lugares usados en el registro de vacunación.</p>

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

        <form method="POST" action="{{ isset($place) ? route('catalogos.places.update', $place) : route('catalogos.places.store') }}">
            @csrf
            @isset($place)
                @method('PUT')
            @endisset

            <div class="form-grid">
                <div class="field full">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" value="{{ old('nombre', $place->nombre ?? '') }}" required maxlength="255">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn primary">{{ isset($place) ? 'Actualizar' : 'Guardar' }}</button>
                @isset($place)
                    <a href="{{ route('catalogos.places') }}" class="btn">Cancelar edición</a>
                @endisset
            </div>
        </form>
    </section>

    <section class="panel stack">
        <div>
            <h2>Lugares registrados</h2>
            <p class="muted">Lista de referencia para las campañas y los registros de vacunas.</p>
        </div>

        @forelse ($places as $item)
            <div style="display:flex; justify-content:space-between; gap:12px; padding:14px; border:1px solid rgba(148,163,184,.14); border-radius:16px;">
                <div>
                    <strong>{{ $item->nombre }}</strong>
                </div>
                <a class="btn" href="{{ route('catalogos.places.edit', $item) }}">Modificar</a>
            </div>
        @empty
            <div class="empty">No hay lugares registrados.</div>
        @endforelse
    </section>
</div>
@endsection
