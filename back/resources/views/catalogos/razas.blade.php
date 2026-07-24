@extends('layouts.catalogo')

@section('title', 'Razas')

@section('content')
<div class="grid">
    <section class="panel">
        <h1>{{ isset($raza) ? 'Modificar raza' : 'Registrar raza' }}</h1>
        <p class="muted">Asocia la raza con una especie y completa una descripción opcional.</p>

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

        <form method="POST" action="{{ isset($raza) ? route('catalogos.razas.update', $raza) : route('catalogos.razas.store') }}">
            @csrf
            @isset($raza)
                @method('PUT')
            @endisset

            <div class="form-grid">
                <div class="field">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" value="{{ old('nombre', $raza->nombre ?? '') }}" required maxlength="255">
                </div>
                <div class="field">
                    <label for="especie_id">Especie</label>
                    <select id="especie_id" name="especie_id" required>
                        <option value="">Seleccione una especie</option>
                        @foreach ($especies as $especie)
                            <option value="{{ $especie->id }}" @selected((string) old('especie_id', $raza->especie_id ?? '') === (string) $especie->id)>
                                {{ $especie->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field full">
                    <label for="descrpcion">Descripción</label>
                    <textarea id="descrpcion" name="descrpcion">{{ old('descrpcion', $raza->descrpcion ?? '') }}</textarea>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn primary">{{ isset($raza) ? 'Actualizar' : 'Guardar' }}</button>
                @isset($raza)
                    <a href="{{ route('catalogos.razas') }}" class="btn">Cancelar edición</a>
                @endisset
            </div>
        </form>
    </section>

    <section class="panel stack">
        <div>
            <h2>Razas registradas</h2>
            <p class="muted">Cada registro muestra su especie relacionada.</p>
        </div>

        @forelse ($razas as $item)
            <div style="display:flex; justify-content:space-between; gap:12px; padding:14px; border:1px solid rgba(148,163,184,.14); border-radius:16px;">
                <div>
                    <strong>{{ $item->nombre }}</strong><br>
                    <span class="muted">{{ $item->especie?->nombre ?? 'Sin especie' }}</span>
                </div>
                <a class="btn" href="{{ route('catalogos.razas.edit', $item) }}">Modificar</a>
            </div>
        @empty
            <div class="empty">No hay razas registradas.</div>
        @endforelse
    </section>
</div>
@endsection
