@extends('layouts.app')
@section('title', 'Nuevo Usuario')

@section('content')
<div class="page-header">
    <h2>Nuevo Usuario</h2>
    <div class="page-header-actions">
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">Datos del Usuario</div>
    <div class="card-body">
        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="usuario">Usuario *</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" value="{{ old('usuario') }}" required>
                    @error('usuario') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="nombres">Nombre Completo *</label>
                    <input type="text" id="nombres" name="nombres" class="form-control" value="{{ old('nombres') }}" required>
                    @error('nombres') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}">
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    @error('password') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="rol_id">Rol *</label>
                    <select id="rol_id" name="rol_id" class="form-control" required>
                        <option value="">Seleccionar rol...</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    @error('rol_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="expiracion_sesion">Expiración de Sesión</label>
                    <input type="date" id="expiracion_sesion" name="expiracion_sesion" class="form-control" value="{{ old('expiracion_sesion') }}">
                    <div class="form-hint">Dejar vacío para acceso sin expiración</div>
                </div>
            </div>
            <div class="form-row" style="margin-bottom:1rem;">
                <div class="form-group">
                    <div class="form-check">
                        <input type="hidden" name="acceso_web" value="0">
                        <input type="checkbox" id="acceso_web" name="acceso_web" value="1" {{ old('acceso_web', true) ? 'checked' : '' }}>
                        <label for="acceso_web" style="cursor:pointer;">Acceso Web</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input type="hidden" name="acceso_app" value="0">
                        <input type="checkbox" id="acceso_app" name="acceso_app" value="1" {{ old('acceso_app', true) ? 'checked' : '' }}>
                        <label for="acceso_app" style="cursor:pointer;">Acceso App Móvil</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Empresas Asignadas</label>
                <div style="max-height:200px; overflow-y:auto; border:1px solid var(--border); border-radius:var(--radius); padding:0.75rem;">
                    @foreach($empresas as $emp)
                    <div class="form-check" style="margin-bottom:0.35rem;">
                        <input type="checkbox" name="empresas[]" value="{{ $emp->id }}" id="emp_{{ $emp->id }}" {{ in_array($emp->id, old('empresas', [])) ? 'checked' : '' }}>
                        <label for="emp_{{ $emp->id }}" style="font-size:0.85rem; cursor:pointer;">{{ $emp->nombre }} <span style="color:var(--text-secondary);">({{ $emp->codigo }})</span></label>
                    </div>
                    @endforeach
                    @if($empresas->isEmpty())
                        <p style="color:var(--text-secondary); font-size:0.85rem;">No hay empresas registradas</p>
                    @endif
                </div>
            </div>

            <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
