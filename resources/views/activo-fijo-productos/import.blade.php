@extends('layouts.app')
@section('title', 'Importar Activos Fijos')

@section('content')
<div class="page-header">
    <h2>Importar Activos Fijos desde Excel</h2>
    <div class="page-header-actions">
        <a href="{{ route('activo-fijo-productos.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="card" style="max-width:600px;">
    <div class="card-header"><span>Cargar Archivo</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('activo-fijo-productos.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Empresa *</label>
                <select name="empresa_id" class="form-control" required>
                    <option value="">Seleccionar empresa</option>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Sesión de Activo Fijo *</label>
                <select name="inventario_id" class="form-control" required>
                    <option value="">Seleccionar sesión</option>
                    @foreach($sesiones as $ses)
                        <option value="{{ $ses->id }}">
                            {{ $ses->empresa->nombre ?? '' }} - {{ $ses->sucursal->nombre ?? $ses->nombre ?? 'Sesión #'.$ses->id }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Archivo Excel o CSV *</label>
                <input type="file" name="archivo" class="form-control" accept=".xlsx,.xls,.csv" required>
                <small style="color:var(--text-secondary);">Formatos: .xlsx, .xls, .csv. Máximo 10MB. Columnas esperadas: codigo_1, codigo_2, descripcion, categoria, marca, modelo, n_serie, cantidad_teorica</small>
            </div>
            <div style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Importar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
