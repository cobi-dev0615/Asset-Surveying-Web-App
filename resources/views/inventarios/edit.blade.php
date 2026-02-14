@extends('layouts.app')
@section('title', 'Editar Inventario')

@section('content')
<div class="page-header">
    <h2>Editar Inventario: {{ $inventario->nombre }}</h2>
    <div class="page-header-actions">
        <a href="{{ route('inventarios.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">Datos del Inventario</div>
    <div class="card-body">
        <form method="POST" action="{{ route('inventarios.update', $inventario) }}">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Empresa</label>
                    <input type="text" class="form-control" value="{{ $inventario->empresa->nombre ?? '-' }}" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Sucursal</label>
                    <input type="text" class="form-control" value="{{ $inventario->sucursal->nombre ?? '-' }}" disabled>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre del Inventario *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre', $inventario->nombre) }}" required>
                    @error('nombre') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="status_id">Estado *</label>
                    <select id="status_id" name="status_id" class="form-control" required>
                        @foreach($statuses as $st)
                            <option value="{{ $st->id }}" {{ old('status_id', $inventario->status_id) == $st->id ? 'selected' : '' }}>{{ $st->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row" style="grid-template-columns: repeat(3, 1fr);">
                <div class="form-group">
                    <label class="form-label" for="auditor">Auditor</label>
                    <input type="text" id="auditor" name="auditor" class="form-control" value="{{ old('auditor', $inventario->auditor) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="gerente">Gerente</label>
                    <input type="text" id="gerente" name="gerente" class="form-control" value="{{ old('gerente', $inventario->gerente) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="subgerente">Subgerente</label>
                    <input type="text" id="subgerente" name="subgerente" class="form-control" value="{{ old('subgerente', $inventario->subgerente) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="comentarios">Comentarios</label>
                <textarea id="comentarios" name="comentarios" class="form-control">{{ old('comentarios', $inventario->comentarios) }}</textarea>
            </div>
            <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Actualizar Inventario</button>
                <a href="{{ route('inventarios.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
