@extends('layouts.app')
@section('title', 'Editar Sesión de Activo Fijo')

@section('content')
<div class="page-header">
    <h2>Editar Sesión #{{ $activo_fijo->id }}</h2>
    <div class="page-header-actions">
        <a href="{{ route('activo-fijo.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">Datos de la Sesión</div>
    <div class="card-body">
        <form method="POST" action="{{ route('activo-fijo.update', $activo_fijo) }}">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Empresa</label>
                    <input type="text" class="form-control" value="{{ $activo_fijo->empresa->nombre ?? '-' }}" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Sucursal</label>
                    <input type="text" class="form-control" value="{{ $activo_fijo->sucursal->nombre ?? '-' }}" disabled>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="status_id">Estado *</label>
                <select id="status_id" name="status_id" class="form-control" required>
                    @foreach($statuses as $st)
                        <option value="{{ $st->id }}" {{ old('status_id', $activo_fijo->status_id) == $st->id ? 'selected' : '' }}>{{ $st->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="comentarios">Comentarios</label>
                <textarea id="comentarios" name="comentarios" class="form-control">{{ old('comentarios', $activo_fijo->comentarios) }}</textarea>
            </div>
            <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Actualizar Sesión</button>
                <a href="{{ route('activo-fijo.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
