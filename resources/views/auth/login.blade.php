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

    /* Role Select */
    .role-select-wrapper { position: relative; margin-bottom: 1.5rem; }
    .role-select-btn {
        width: 100%; display: flex; align-items: center; gap: 0.75rem;
        padding: 0.65rem 1rem; border-radius: 10px; cursor: pointer;
        border: 2px solid var(--border); background: var(--surface);
        transition: all 0.25s ease; color: var(--text);
    }
    .role-select-btn:hover { border-color: var(--text-light); }
    .role-select-btn.selected { border-color: var(--role-color); }
    .role-select-btn .role-icon-box {
        width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: var(--border); transition: background 0.25s;
    }
    .role-select-btn.selected .role-icon-box { background: var(--role-color); }
    .role-select-btn .role-icon-box svg { width: 18px; height: 18px; stroke: var(--text-light); transition: stroke 0.25s; }
    .role-select-btn.selected .role-icon-box svg { stroke: #fff; }
    .role-select-btn .role-text { flex: 1; text-align: left; }
    .role-select-btn .role-text .role-name { font-size: 0.9rem; font-weight: 600; line-height: 1.2; }
    .role-select-btn .role-text .role-desc { font-size: 0.75rem; color: var(--text-secondary); line-height: 1.2; margin-top: 1px; }
    .role-select-btn .role-chevron { flex-shrink: 0; transition: transform 0.25s; }
    .role-select-btn .role-chevron svg { width: 18px; height: 18px; stroke: var(--text-light); }
    .role-select-wrapper.open .role-chevron { transform: rotate(180deg); }
    .role-dropdown {
        position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 50;
        background: var(--surface); border: 2px solid var(--border); border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden;
        display: none; animation: dropdownFade 0.2s ease;
    }
    .role-select-wrapper.open .role-dropdown { display: block; }
    @keyframes dropdownFade { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
    .role-option {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.6rem 1rem; cursor: pointer; transition: background 0.15s;
    }
    .role-option:hover { background: rgba(255,255,255,0.05); }
    .role-option .role-icon-box {
        width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .role-option .role-icon-box svg { width: 18px; height: 18px; stroke: #fff; }
    .role-option .role-text { flex: 1; }
    .role-option .role-text .role-name { font-size: 0.9rem; font-weight: 600; color: var(--text); line-height: 1.2; }
    .role-option .role-text .role-desc { font-size: 0.75rem; color: var(--text-secondary); line-height: 1.2; margin-top: 1px; }

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

        {{-- Role Select --}}
        <div class="role-select-wrapper" id="roleWrapper">
            <div class="role-select-btn" id="roleBtn" onclick="toggleDropdown()">
                <div class="role-icon-box" id="roleBtnIcon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="role-text">
                    <div class="role-name" id="roleBtnName">Selecciona tu rol</div>
                    <div class="role-desc" id="roleBtnDesc">Haz clic para elegir</div>
                </div>
                <div class="role-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
            </div>
            <div class="role-dropdown" id="roleDropdown">
                <div class="role-option" data-role="super_admin" data-color="#0d6efd" data-name="Admin" data-desc="Panel de Administración" data-icon="shield" onclick="pickRole(this)">
                    <div class="role-icon-box" style="background:#0d6efd;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div class="role-text">
                        <div class="role-name">Admin</div>
                        <div class="role-desc">Panel de Administración</div>
                    </div>
                </div>
                <div class="role-option" data-role="supervisor" data-color="#10b981" data-name="Supervisor" data-desc="Panel de Supervisión" data-icon="clipboard" onclick="pickRole(this)">
                    <div class="role-icon-box" style="background:#10b981;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>
                    </div>
                    <div class="role-text">
                        <div class="role-name">Supervisor</div>
                        <div class="role-desc">Panel de Supervisión</div>
                    </div>
                </div>
                <div class="role-option" data-role="capturista" data-color="#f59e0b" data-name="Capturista" data-desc="Captura de Datos" data-icon="barcode" onclick="pickRole(this)">
                    <div class="role-icon-box" style="background:#f59e0b;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="6" y1="8" x2="6" y2="16"/><line x1="10" y1="8" x2="10" y2="16"/><line x1="14" y1="8" x2="14" y2="16"/><line x1="18" y1="8" x2="18" y2="16"/></svg>
                    </div>
                    <div class="role-text">
                        <div class="role-name">Capturista</div>
                        <div class="role-desc">Captura de Datos</div>
                    </div>
                </div>
                <div class="role-option" data-role="supervisor_invitado" data-color="#8b5cf6" data-name="Invitado" data-desc="Gestión de Traspasos" data-icon="transfer" onclick="pickRole(this)">
                    <div class="role-icon-box" style="background:#8b5cf6;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    </div>
                    <div class="role-text">
                        <div class="role-name">Invitado</div>
                        <div class="role-desc">Gestión de Traspasos</div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <input type="hidden" name="rol_tipo" id="rolTipoInput" value="{{ old('rol_tipo', '') }}">

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

            <button type="submit" class="btn btn-primary btn-lg btn-block" id="loginBtn">Iniciar Sesión</button>
        </form>

        <div style="text-align:center; margin-top:1rem;">
            <a href="{{ route('register') }}" style="color:var(--primary); font-size:0.9rem;">¿No tienes cuenta? Regístrate</a>
        </div>
    </div>
    <div class="login-footer">SER Inventarios &copy; {{ date('Y') }}</div>
</div>

<script>
function toggleDropdown() {
    document.getElementById('roleWrapper').classList.toggle('open');
}

function pickRole(el) {
    var role = el.dataset.role;
    var color = el.dataset.color;
    var name = el.dataset.name;
    var desc = el.dataset.desc;

    // Update hidden input
    document.getElementById('rolTipoInput').value = role;

    // Update button display
    var btn = document.getElementById('roleBtn');
    btn.classList.add('selected');
    btn.style.setProperty('--role-color', color);
    document.getElementById('roleBtnName').textContent = name;
    document.getElementById('roleBtnDesc').textContent = desc;

    // Copy the icon SVG from the selected option
    var iconSvg = el.querySelector('.role-icon-box').innerHTML;
    var btnIcon = document.getElementById('roleBtnIcon');
    btnIcon.innerHTML = iconSvg;
    btnIcon.style.background = color;
    btnIcon.querySelectorAll('svg').forEach(function(s) { s.style.stroke = '#fff'; });

    // Update subtitle
    document.getElementById('loginSubtitle').textContent = desc;
    document.getElementById('loginSubtitle').style.color = color;

    // Update login button color
    var loginBtn = document.getElementById('loginBtn');
    loginBtn.style.background = color;
    loginBtn.style.borderColor = color;

    // Close dropdown
    document.getElementById('roleWrapper').classList.remove('open');
}

// Form submit validation — uses global showToast() from layout
document.querySelector('form').addEventListener('submit', function(e) {
    if (!document.getElementById('rolTipoInput').value) {
        e.preventDefault();
        showToast('Selecciona tu rol antes de iniciar sesión', 'warning');
    }
});

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('roleWrapper');
    if (!wrapper.contains(e.target)) {
        wrapper.classList.remove('open');
    }
});

// Restore selection on page load (for old() values) + show login errors as toast
document.addEventListener('DOMContentLoaded', function() {
    var saved = document.getElementById('rolTipoInput').value;
    if (saved) {
        var opt = document.querySelector('.role-option[data-role="' + saved + '"]');
        if (opt) pickRole(opt);
    }
    @error('usuario')
        showToast(@json($message), 'error');
    @enderror
});
</script>
@endsection
