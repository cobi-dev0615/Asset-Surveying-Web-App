@extends('layouts.app')
@section('title', 'Crear Cuenta')

@push('styles')
<style>
    body { display: flex; justify-content: center; align-items: center; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
    .register-container { width: 100%; max-width: 480px; padding: 2rem; }
    .register-card { background: var(--surface); border-radius: 16px; padding: 2.5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    .register-brand { text-align: center; margin-bottom: 1.5rem; }
    .register-brand img { height: 60px; width: auto; margin-bottom: 0.5rem; }
    .register-brand h1 { font-size: 1.4rem; font-weight: 700; color: var(--text); }
    .register-brand p { color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem; }
    .register-card .btn-primary { width: 100%; padding: 0.7rem; font-size: 0.95rem; margin-top: 0.5rem; }
    .register-footer { text-align: center; margin-top: 1.5rem; color: rgba(255,255,255,0.4); font-size: 0.8rem; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .empresas-list { max-height: 160px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; padding: 0.5rem 0.75rem; }
    .empresas-list label { display: flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0; font-size: 0.85rem; color: var(--text); cursor: pointer; }
    .empresas-list label:hover { color: var(--primary); }
    .empresas-list input[type="checkbox"] { accent-color: var(--primary); }
</style>
@endpush

@section('content')
<div class="register-container">
    <div class="register-card">
        <div class="register-brand">
            <img src="/img/logo-ser-dark.png" alt="SER">
            <h1>SER Inventarios</h1>
            <p>Crear Cuenta</p>
        </div>

        @if($errors->any())
            <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">
                @foreach($errors->all() as $error)
                    <div style="color: #ef4444; font-size: 0.85rem;">{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="usuario">Usuario <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="usuario" name="usuario" class="form-control" value="{{ old('usuario') }}" placeholder="nombre.usuario" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="nombres">Nombre Completo <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="nombres" name="nombres" class="form-control" value="{{ old('nombres') }}" placeholder="Juan Pérez" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email <span style="color:var(--text-light); font-weight:400;">(opcional)</span></label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="correo@ejemplo.com">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña <span style="color:#ef4444;">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 4 caracteres" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar <span style="color:#ef4444;">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repetir contraseña" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Empresas <span style="color:#ef4444;">*</span></label>
                <div class="empresas-list">
                    @foreach($empresas as $empresa)
                        <label>
                            <input type="checkbox" name="empresas[]" value="{{ $empresa->id }}" {{ in_array($empresa->id, old('empresas', [])) ? 'checked' : '' }}>
                            {{ $empresa->nombre }} <span style="color:var(--text-light); font-size:0.75rem;">({{ $empresa->codigo }})</span>
                        </label>
                    @endforeach
                </div>
                <div style="color:var(--text-light); font-size:0.75rem; margin-top:0.25rem;">Selecciona las empresas a las que perteneces</div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Registrarse</button>
        </form>

        <div style="text-align:center; margin-top:1rem;">
            <a href="{{ route('login') }}" style="color:var(--primary); font-size:0.9rem;">¿Ya tienes cuenta? Iniciar Sesión</a>
        </div>
    </div>
    <div class="register-footer">SER Inventarios &copy; {{ date('Y') }}</div>
</div>
@endsection
