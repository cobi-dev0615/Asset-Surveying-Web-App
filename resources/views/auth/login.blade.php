@extends('layouts.app')
@section('title', 'Iniciar Sesión')

@push('styles')
<style>
    body { display: flex; justify-content: center; align-items: center; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
    .login-container { width: 100%; max-width: 460px; padding: 2rem; }
    .login-card { background: var(--surface); border-radius: 16px; padding: 2.5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    .login-brand { text-align: center; margin-bottom: 1.5rem; }
    .login-brand img { height: 70px; width: auto; margin-bottom: 0.5rem; }
    .login-brand h1 { font-size: 1.5rem; font-weight: 700; color: var(--text); }
    .login-brand .subtitle { color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.35rem; }
    .login-card .btn-primary { width: 100%; padding: 0.7rem; font-size: 0.95rem; margin-top: 0.5rem; }
    .login-footer { text-align: center; margin-top: 1.5rem; color: rgba(255,255,255,0.4); font-size: 0.8rem; }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-brand">
            <img src="/img/logo-ser-dark.png" alt="SER">
            <h1>SER Inventarios</h1>
            <p class="subtitle">Ingresa tus credenciales para continuar</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" class="form-control" value="{{ old('usuario') }}" placeholder="Ingresa tu usuario" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" style="font-size:0.85rem; color: var(--text-secondary); cursor:pointer;">Recordar sesión</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Iniciar Sesión</button>
        </form>

        <div style="text-align:center; margin-top:1rem;">
            <a href="{{ route('register') }}" style="color:var(--primary); font-size:0.9rem;">¿No tienes cuenta? Regístrate</a>
        </div>
    </div>
    <div class="login-footer">SER Inventarios &copy; {{ date('Y') }}</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @error('usuario')
        showToast(@json($message), 'error');
    @enderror
});
</script>
@endsection
