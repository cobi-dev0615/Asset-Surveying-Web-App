@extends('layouts.app')
@section('title', 'Nueva Orden de Transferencia')

@section('content')
<div class="page-header">
    <h2>Nueva Orden de Transferencia</h2>
    <div class="page-header-actions">
        <a href="{{ route('ordenes-entrada.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-header"><span>Datos de la Orden</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('ordenes-entrada.store') }}" id="ordenForm">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>Sesión Origen *</label>
                    <select name="inventario_origen_id" id="inventarioOrigen" class="form-control" required>
                        <option value="">Seleccionar origen</option>
                        @foreach($sesiones as $ses)
                            <option value="{{ $ses->id }}" {{ old('inventario_origen_id') == $ses->id ? 'selected' : '' }}>
                                {{ $ses->empresa->nombre ?? '' }} - {{ $ses->sucursal->nombre ?? $ses->nombre ?? 'Sesión #'.$ses->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Sesión Destino *</label>
                    <select name="inventario_destino_id" class="form-control" required>
                        <option value="">Seleccionar destino</option>
                        @foreach($sesiones as $ses)
                            <option value="{{ $ses->id }}" {{ old('inventario_destino_id') == $ses->id ? 'selected' : '' }}>
                                {{ $ses->empresa->nombre ?? '' }} - {{ $ses->sucursal->nombre ?? $ses->nombre ?? 'Sesión #'.$ses->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Motivo *</label>
                <select name="motivo" class="form-control" required>
                    <option value="">Seleccionar motivo</option>
                    <option value="Mantenimiento" {{ old('motivo') == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    <option value="Daño en nuestro equipo" {{ old('motivo') == 'Daño en nuestro equipo' ? 'selected' : '' }}>Daño en equipo</option>
                    <option value="Reubicación" {{ old('motivo') == 'Reubicación' ? 'selected' : '' }}>Reubicación</option>
                    <option value="Otro" {{ old('motivo') == 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label>Comentarios</label>
                <textarea name="comentarios" class="form-control" rows="3" placeholder="Comentarios adicionales...">{{ old('comentarios') }}</textarea>
            </div>

            <div class="form-group">
                <label>Activos a transferir *</label>
                <small style="display:block; color:var(--text-secondary); margin-bottom:0.5rem;">
                    Seleccione la sesión origen primero. Ingrese los IDs de registro separados por coma.
                </small>
                <div id="registrosContainer">
                    <div style="display:flex; gap:0.5rem; align-items:center; margin-bottom:0.5rem;">
                        <input type="number" name="registros[]" class="form-control" placeholder="ID de registro" required style="flex:1;">
                        <button type="button" class="btn btn-sm btn-outline" onclick="agregarRegistro()">+</button>
                    </div>
                </div>
            </div>

            <div style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
                    Crear Orden
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function agregarRegistro() {
    const container = document.getElementById('registrosContainer');
    const div = document.createElement('div');
    div.style.cssText = 'display:flex; gap:0.5rem; align-items:center; margin-bottom:0.5rem;';
    div.innerHTML = `
        <input type="number" name="registros[]" class="form-control" placeholder="ID de registro" required style="flex:1;">
        <button type="button" class="btn btn-sm btn-ghost" style="color:var(--danger);" onclick="this.parentElement.remove()">×</button>
    `;
    container.appendChild(div);
}
</script>
@endpush
@endsection
