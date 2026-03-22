@extends('layouts.app')
@section('title', 'Importar Productos')

@section('content')
<div class="page-header">
    <h2>Importar Productos desde Excel</h2>
    <div class="page-header-actions">
        <a href="{{ route('productos.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">Importar Archivo</div>
    <div class="card-body">
        <form method="POST" action="{{ route('productos.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label" for="empresa_id">Empresa *</label>
                <select id="empresa_id" name="empresa_id" class="form-control" required>
                    <option value="">Seleccionar empresa...</option>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" {{ old('empresa_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nombre }}</option>
                    @endforeach
                </select>
                @error('empresa_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="archivo">Archivo Excel (.xlsx, .xls, .csv) *</label>
                <input type="file" id="archivo" name="archivo" class="form-control" accept=".xlsx,.xls,.csv" required>
                @error('archivo') <div class="form-error">{{ $message }}</div> @enderror
                <div class="form-hint">Máximo 10 MB. El archivo debe tener encabezados en la primera fila.</div>
            </div>

            <div class="alert alert-info" style="margin-top:1rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <div>
                    <strong>Columnas esperadas:</strong> codigo_1 (o codigo/sku), descripcion (o nombre), codigo_2, codigo_3, marca, modelo, categoria, subcategoria, precio_compra, precio_venta, cantidad_teorica (o existencia), unidad_medida (o unidad).
                </div>
            </div>

            <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Importar Productos
                </button>
                <a href="{{ route('productos.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
