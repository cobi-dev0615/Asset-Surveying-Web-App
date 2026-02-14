@extends('layouts.app')
@section('title', 'Nueva Sesión de Activo Fijo')

@section('content')
<div class="page-header">
    <h2>Nueva Sesión de Activo Fijo</h2>
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
        <form method="POST" action="{{ route('activo-fijo.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="empresa_id">Empresa *</label>
                    <select id="empresa_id" name="empresa_id" class="form-control" required onchange="cargarSucursalesAF(this.value)">
                        <option value="">Seleccionar empresa...</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->id }}" {{ old('empresa_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nombre }}</option>
                        @endforeach
                    </select>
                    @error('empresa_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="sucursal_id">Sucursal *</label>
                    <select id="sucursal_id" name="sucursal_id" class="form-control" required>
                        <option value="">Seleccione primero una empresa</option>
                    </select>
                    @error('sucursal_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="status_id">Estado *</label>
                <select id="status_id" name="status_id" class="form-control" required>
                    @foreach($statuses as $st)
                        <option value="{{ $st->id }}" {{ old('status_id', 1) == $st->id ? 'selected' : '' }}>{{ $st->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="comentarios">Comentarios</label>
                <textarea id="comentarios" name="comentarios" class="form-control">{{ old('comentarios') }}</textarea>
            </div>
            <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Crear Sesión</button>
                <a href="{{ route('activo-fijo.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function cargarSucursalesAF(empresaId) {
    const select = document.getElementById('sucursal_id');
    select.innerHTML = '<option value="">Cargando...</option>';
    if (!empresaId) { select.innerHTML = '<option value="">Seleccione primero una empresa</option>'; return; }
    fetch('/sucursales-por-empresa/' + empresaId)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">Seleccionar sucursal...</option>';
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.nombre + ' (' + s.codigo + ')';
                select.appendChild(opt);
            });
        });
}
</script>
@endpush
@endsection
