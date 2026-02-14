@extends('layouts.app')
@section('title', 'Nueva Transferencia')

@section('content')
<div class="page-header">
    <h2>Nueva Solicitud de Transferencia</h2>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-header">
        <span>Datos de la transferencia</span>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('transferencias.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="activo">ID del Activo *</label>
                <input type="number" name="activo" id="activo" class="form-control" value="{{ old('activo') }}" required placeholder="Ingrese el ID del activo a transferir">
            </div>

            <div class="form-group">
                <label class="form-label" for="sucursal_origen_id">Sucursal de Origen *</label>
                <select name="sucursal_origen_id" id="sucursal_origen_id" class="form-control" required>
                    <option value="">Seleccionar sucursal de origen...</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ old('sucursal_origen_id') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="sucursal_destino_id">Sucursal de Destino *</label>
                <select name="sucursal_destino_id" id="sucursal_destino_id" class="form-control" required>
                    <option value="">Seleccionar sucursal de destino...</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ old('sucursal_destino_id') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px; height:16px;"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    Solicitar Transferencia
                </button>
                <a href="{{ route('transferencias.solicitadas') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
