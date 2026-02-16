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
    .login-brand .subtitle { color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.35rem; transition: color 0.3s; }
    .login-card .btn-primary { width: 100%; padding: 0.7rem; font-size: 0.95rem; margin-top: 0.5rem; }
    .login-footer { text-align: center; margin-top: 1.5rem; color: rgba(255,255,255,0.4); font-size: 0.8rem; }

    /* Role Selector */
    .role-selector { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 1.5rem; }
    .role-card {
        display: flex; flex-direction: column; align-items: center; gap: 0.3rem;
        padding: 0.6rem 0.25rem; border-radius: 10px; cursor: pointer;
        border: 2px solid var(--border); background: transparent;
        transition: all 0.25s ease; text-align: center;
    }
    .role-card:hover { border-color: var(--text-light); }
    .role-card.active { border-color: var(--role-color); background: color-mix(in srgb, var(--role-color) 10%, transparent); }
    .role-card .role-icon {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        background: var(--border); transition: background 0.25s;
    }
    .role-card .role-icon svg { width: 18px; height: 18px; stroke: var(--text-light); transition: stroke 0.25s; }
    .role-card.active .role-icon { background: var(--role-color); }
    .role-card.active .role-icon svg { stroke: #fff; }
    .role-card .role-name { font-size: 0.7rem; font-weight: 600; color: var(--text-light); transition: color 0.25s; line-height: 1.2; }
    .role-card.active .role-name { color: var(--role-color); }

    /* Success alert */
    .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; color: #10b981; font-size: 0.85rem; }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-brand">
            <img src="/img/logo-ser-dark.png" alt="SER">
            <h1>SER Inventarios</h1>
            <p class="subtitle" id="loginSubtitle">Selecciona tu rol para continuar</p>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        {{-- Role Selector --}}
        <div class="role-selector">
            <div class="role-card {{ old('rol_tipo', '') === 'super_admin' ? 'active' : '' }}" data-role="super_admin" data-color="#0d6efd" data-label="Panel de Administración" style="--role-color: #0d6efd;" onclick="selectRole(this)">
                <div class="role-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <span class="role-name">Admin</span>
            </div>
            <div class="role-card {{ old('rol_tipo', '') === 'supervisor' ? 'active' : '' }}" data-role="supervisor" data-color="#10b981" data-label="Panel de Supervisión" style="--role-color: #10b981;" onclick="selectRole(this)">
                <div class="role-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>
                </div>
                <span class="role-name">Supervisor</span>
            </div>
            <div class="role-card {{ old('rol_tipo', '') === 'capturista' ? 'active' : '' }}" data-role="capturista" data-color="#f59e0b" data-label="Captura de Datos" style="--role-color: #f59e0b;" onclick="selectRole(this)">
                <div class="role-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="6" y1="8" x2="6" y2="16"/><line x1="10" y1="8" x2="10" y2="16"/><line x1="14" y1="8" x2="14" y2="16"/><line x1="18" y1="8" x2="18" y2="16"/></svg>
                </div>
                <span class="role-name">Capturista</span>
            </div>
            <div class="role-card {{ old('rol_tipo', '') === 'supervisor_invitado' ? 'active' : '' }}" data-role="supervisor_invitado" data-color="#8b5cf6" data-label="Gestión de Traspasos" style="--role-color: #8b5cf6;" onclick="selectRole(this)">
                <div class="role-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                </div>
                <span class="role-name">Invitado</span>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <input type="hidden" name="rol_tipo" id="rolTipoInput" value="{{ old('rol_tipo', '') }}">

            <div class="form-group">
                <label class="form-label" for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" class="form-control" value="{{ old('usuario') }}" placeholder="Ingresa tu usuario" required autofocus>
                @error('usuario')
                    <div class="form-error">{{ $message }}</div>
                @enderror
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

            <button type="submit" class="btn btn-primary btn-lg btn-block" id="loginBtn">Iniciar Sesión</button>
        </form>

        <div style="text-align:center; margin-top:1rem;">
            <a href="{{ route('register') }}" style="color:var(--primary); font-size:0.9rem;">¿No tienes cuenta? Regístrate</a>
        </div>
    </div>
    <div class="login-footer">SER Inventarios &copy; {{ date('Y') }}</div>
</div>

<script>
function selectRole(el) {
    // Remove active from all
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('active'));
    // Activate selected
    el.classList.add('active');
    // Update hidden input
    document.getElementById('rolTipoInput').value = el.dataset.role;
    // Update subtitle
    document.getElementById('loginSubtitle').textContent = el.dataset.label;
    document.getElementById('loginSubtitle').style.color = el.dataset.color;
    // Update button color
    var btn = document.getElementById('loginBtn');
    btn.style.background = el.dataset.color;
    btn.style.borderColor = el.dataset.color;
}

// Apply active state on page load (for old() values)
document.addEventListener('DOMContentLoaded', function() {
    var active = document.querySelector('.role-card.active');
    if (active) {
        document.getElementById('loginSubtitle').textContent = active.dataset.label;
        document.getElementById('loginSubtitle').style.color = active.dataset.color;
        document.getElementById('loginBtn').style.background = active.dataset.color;
        document.getElementById('loginBtn').style.borderColor = active.dataset.color;
    }
});
</script>
@endsection
