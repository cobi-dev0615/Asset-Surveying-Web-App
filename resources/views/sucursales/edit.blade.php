@extends('layouts.app')
@section('title', 'Editar Sucursal')

@section('content')
<div class="page-header">
    <h2>Editar Sucursal: {{ $sucursal->nombre }}</h2>
    <div class="page-header-actions">
        <a href="{{ route('sucursales.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">Datos de la Sucursal</div>
    <div class="card-body">
        <form method="POST" action="{{ route('sucursales.update', $sucursal) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label" for="empresa_id">Empresa *</label>
                <select id="empresa_id" name="empresa_id" class="form-control" required>
                    <option value="">Seleccionar empresa...</option>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" {{ old('empresa_id', $sucursal->empresa_id) == $emp->id ? 'selected' : '' }}>{{ $emp->nombre }}</option>
                    @endforeach
                </select>
                @error('empresa_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="codigo">Código *</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" value="{{ old('codigo', $sucursal->codigo) }}" required>
                    @error('codigo') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre', $sucursal->nombre) }}" required>
                    @error('nombre') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="ciudad">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad" class="form-control" value="{{ old('ciudad', $sucursal->ciudad) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" value="{{ old('direccion', $sucursal->direccion) }}">
                </div>
            </div>
            <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Actualizar Sucursal</button>
                <a href="{{ route('sucursales.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
