@extends('layouts.catalogo')

@section('title', 'Especies')

@section('content')
<div class="grid">
    <section class="panel">
        <h1>{{ isset($especie) ? 'Modificar especie' : 'Registrar especie' }}</h1>
        <p class="muted">Captura el código y el nombre del catálogo de especies.</p>

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

        <form method="POST" action="{{ isset($especie) ? route('catalogos.especies.update', $especie) : route('catalogos.especies.store') }}">
            @csrf
            @isset($especie)
                @method('PUT')
            @endisset

            <div class="form-grid">
                <div class="field">
                    <label for="codigo">Código</label>
                    <input id="codigo" name="codigo" value="{{ old('codigo', $especie->codigo ?? '') }}" required maxlength="50">
                </div>
                <div class="field">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" value="{{ old('nombre', $especie->nombre ?? '') }}" required maxlength="255">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn primary">{{ isset($especie) ? 'Actualizar' : 'Guardar' }}</button>
                @isset($especie)
                    <a href="{{ route('catalogos.especies') }}" class="btn">Cancelar edición</a>
                @endisset
            </div>
        </form>
    </section>

    <section class="panel stack">
        <div>
            <h2>Especies registradas</h2>
            <p class="muted">Lista simple para entrar a la edición de cada registro.</p>
        </div>

        @forelse ($especies as $item)
            <div style="display:flex; justify-content:space-between; gap:12px; padding:14px; border:1px solid rgba(148,163,184,.14); border-radius:16px;">
                <div>
                    <strong>{{ $item->nombre }}</strong><br>
                    <span class="muted">{{ $item->codigo }}</span>
                </div>
                <a class="btn" href="{{ route('catalogos.especies.edit', $item) }}">Modificar</a>
            </div>
        @empty
            <div class="empty">No hay especies registradas.</div>
        @endforelse
    </section>
</div>
@endsection
