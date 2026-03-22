<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Seleccionar Empresa - SER Inventarios</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .selection-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 440px;
            overflow: visible;
        }
        .selection-header {
            background: #111827;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-radius: 12px 12px 0 0;
        }
        .selection-header .logo {
            width: 36px; height: 36px;
            background: #2bc381;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .selection-header .logo svg { width: 20px; height: 20px; }
        .selection-header h2 {
            font-size: 0.92rem;
            font-weight: 600;
            color: #fff;
            flex: 1;
            line-height: 1.3;
        }
        .selection-close {
            background: none; border: none;
            color: rgba(255,255,255,0.5); font-size: 1.4rem;
            cursor: pointer; line-height: 1;
            padding: 0.25rem;
            transition: color 0.2s;
        }
        .selection-close:hover { color: #fff; }

        .selection-body { padding: 1.5rem; }

        .field-group { margin-bottom: 1rem; position: relative; }
        .field-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
            display: block;
        }

        /* Custom searchable select */
        .ss-wrapper { position: relative; }
        .ss-trigger {
            width: 100%;
            padding: 0.7rem 2.2rem 0.7rem 0.85rem;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.88rem;
            color: #333;
            background: #fff;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            min-height: 42px;
            position: relative;
        }
        .ss-trigger:hover { border-color: #bbb; }
        .ss-trigger.open {
            border-color: #2bc381;
            box-shadow: 0 0 0 3px rgba(43,195,129,0.15);
            border-radius: 8px 8px 0 0;
        }
        .ss-trigger .ss-placeholder { color: #aaa; }
        .ss-trigger .ss-value { color: #333; font-weight: 500; }
        .ss-trigger .ss-arrow {
            position: absolute;
            right: 0.75rem; top: 50%;
            transform: translateY(-50%);
            width: 12px; height: 12px;
            color: #999;
            transition: transform 0.2s;
        }
        .ss-trigger.open .ss-arrow { transform: translateY(-50%) rotate(180deg); }

        .ss-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0; right: 0;
            background: #fff;
            border: 1.5px solid #2bc381;
            border-top: none;
            border-radius: 0 0 8px 8px;
            z-index: 100;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .ss-dropdown.open { display: block; }

        .ss-search {
            padding: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .ss-search input {
            width: 100%;
            padding: 0.55rem 0.75rem 0.55rem 2rem;
            border: 1px solid #e8e8e8;
            border-radius: 6px;
            font-size: 0.82rem;
            color: #333;
            background: #f9f9f9;
            outline: none;
            transition: border-color 0.2s;
        }
        .ss-search input:focus {
            border-color: #2bc381;
            background: #fff;
        }
        .ss-search-icon {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            width: 14px; height: 14px;
            color: #aaa;
            pointer-events: none;
        }
        .ss-search { position: relative; }
        .ss-search-icon {
            position: absolute;
            left: 1.1rem; top: 50%;
            transform: translateY(-50%);
        }

        .ss-options {
            max-height: 220px;
            overflow-y: auto;
            padding: 0.25rem 0;
        }
        .ss-options::-webkit-scrollbar { width: 6px; }
        .ss-options::-webkit-scrollbar-track { background: transparent; }
        .ss-options::-webkit-scrollbar-thumb { background: #ddd; border-radius: 3px; }
        .ss-options::-webkit-scrollbar-thumb:hover { background: #bbb; }

        .ss-option {
            padding: 0.55rem 0.85rem;
            font-size: 0.83rem;
            color: #444;
            cursor: pointer;
            transition: background 0.1s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ss-option:hover { background: #f0faf5; color: #1a7a4e; }
        .ss-option.selected { background: #e8f5e9; color: #1a7a4e; font-weight: 600; }
        .ss-option.ss-all { color: #2bc381; font-weight: 500; border-bottom: 1px solid #f0f0f0; }
        .ss-option.hidden { display: none; }
        .ss-empty {
            padding: 1rem;
            text-align: center;
            color: #aaa;
            font-size: 0.82rem;
            font-style: italic;
            display: none;
        }

        .ss-count {
            padding: 0.35rem 0.85rem;
            font-size: 0.72rem;
            color: #aaa;
            text-align: right;
            border-top: 1px solid #f0f0f0;
        }

        /* Loading state */
        .ss-trigger.loading .ss-placeholder { color: #bbb; }
        .ss-trigger.disabled { opacity: 0.5; pointer-events: none; background: #fafafa; }

        .selection-footer {
            padding: 0 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .btn-accept {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            background: #2bc381;
            color: #fff;
        }
        .btn-accept:hover { background: #25a96f; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(43,195,129,0.3); }
        .btn-accept:disabled { background: #d0d0d0; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.7rem;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            color: #999;
        }
        .btn-back:hover { background: #f5f5f5; color: #666; }
        .error-msg {
            background: #fce4ec;
            color: #b71c1c;
            padding: 0.6rem 0.85rem;
            border-radius: 6px;
            font-size: 0.82rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="selection-card">
        <div class="selection-header">
            <div class="logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <h2>Selecciona el inventario que deseas consultar</h2>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="selection-close" title="Cerrar sesión">&times;</button>
            </form>
        </div>

        <div class="selection-body">
            @if($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/seleccionar-empresa" id="selectionForm">
                @csrf
                <input type="hidden" name="empresa_id" id="empresaVal">
                <input type="hidden" name="sucursal_id" id="sucursalVal">

                {{-- Empresa select --}}
                <div class="field-group">
                    <label class="field-label">Empresa</label>
                    <div class="ss-wrapper" id="empresaSS">
                        <div class="ss-trigger" onclick="toggleSS('empresaSS')">
                            <span class="ss-placeholder">Selecciona una empresa</span>
                            <svg class="ss-arrow" viewBox="0 0 12 12"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search">
                                <svg class="ss-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" placeholder="Buscar empresa..." oninput="filterSS('empresaSS', this.value)">
                            </div>
                            <div class="ss-options">
                                @foreach($empresas as $emp)
                                <div class="ss-option" data-value="{{ $emp->id }}" onclick="pickEmpresa(this)">{{ $emp->nombre }}</div>
                                @endforeach
                            </div>
                            <div class="ss-empty">Sin resultados</div>
                        </div>
                    </div>
                </div>

                {{-- Sucursal select --}}
                <div class="field-group">
                    <label class="field-label">Sucursal</label>
                    <div class="ss-wrapper" id="sucursalSS">
                        <div class="ss-trigger disabled">
                            <span class="ss-placeholder">Primero selecciona una empresa</span>
                            <svg class="ss-arrow" viewBox="0 0 12 12"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search">
                                <svg class="ss-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" placeholder="Buscar sucursal..." oninput="filterSS('sucursalSS', this.value)">
                            </div>
                            <div class="ss-options"></div>
                            <div class="ss-empty">Sin resultados</div>
                            <div class="ss-count"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="selection-footer">
            <button type="submit" form="selectionForm" class="btn-accept" id="btnAccept" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                Aceptar
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-back">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>

    <script>
    // ── Searchable Select Logic ──
    function toggleSS(id) {
        var wrapper = document.getElementById(id);
        var trigger = wrapper.querySelector('.ss-trigger');
        var dropdown = wrapper.querySelector('.ss-dropdown');

        if (trigger.classList.contains('disabled')) return;

        var isOpen = trigger.classList.contains('open');
        closeAllSS();

        if (!isOpen) {
            trigger.classList.add('open');
            dropdown.classList.add('open');
            var input = dropdown.querySelector('input');
            if (input) { input.value = ''; filterSS(id, ''); setTimeout(function() { input.focus(); }, 50); }
        }
    }

    function closeAllSS() {
        document.querySelectorAll('.ss-wrapper').forEach(function(w) {
            w.querySelector('.ss-trigger').classList.remove('open');
            w.querySelector('.ss-dropdown').classList.remove('open');
        });
    }

    function filterSS(id, term) {
        var wrapper = document.getElementById(id);
        var options = wrapper.querySelectorAll('.ss-option');
        var empty = wrapper.querySelector('.ss-empty');
        var lowerTerm = term.toLowerCase();
        var visible = 0;

        options.forEach(function(opt) {
            var text = opt.textContent.toLowerCase();
            if (text.indexOf(lowerTerm) !== -1) {
                opt.classList.remove('hidden');
                visible++;
            } else {
                opt.classList.add('hidden');
            }
        });

        empty.style.display = visible === 0 ? 'block' : 'none';
    }

    function pickEmpresa(opt) {
        var wrapper = document.getElementById('empresaSS');
        var trigger = wrapper.querySelector('.ss-trigger');
        var hiddenInput = document.getElementById('empresaVal');

        wrapper.querySelectorAll('.ss-option.selected').forEach(function(o) { o.classList.remove('selected'); });
        opt.classList.add('selected');

        trigger.innerHTML = '<span class="ss-value">' + opt.textContent + '</span>' +
            '<svg class="ss-arrow" viewBox="0 0 12 12"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
        hiddenInput.value = opt.dataset.value;

        closeAllSS();
        document.getElementById('btnAccept').disabled = false;

        // Load sucursales
        loadSucursales(opt.dataset.value);
    }

    function pickSucursal(opt) {
        var wrapper = document.getElementById('sucursalSS');
        var trigger = wrapper.querySelector('.ss-trigger');
        var hiddenInput = document.getElementById('sucursalVal');

        wrapper.querySelectorAll('.ss-option.selected').forEach(function(o) { o.classList.remove('selected'); });
        opt.classList.add('selected');

        trigger.innerHTML = '<span class="ss-value">' + opt.textContent + '</span>' +
            '<svg class="ss-arrow" viewBox="0 0 12 12"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
        hiddenInput.value = opt.dataset.value;

        closeAllSS();
    }

    function loadSucursales(empresaId) {
        var wrapper = document.getElementById('sucursalSS');
        var trigger = wrapper.querySelector('.ss-trigger');
        var optionsDiv = wrapper.querySelector('.ss-options');
        var countDiv = wrapper.querySelector('.ss-count');

        // Show loading
        trigger.classList.remove('disabled');
        trigger.classList.add('loading');
        trigger.innerHTML = '<span class="ss-placeholder">Cargando sucursales...</span>' +
            '<svg class="ss-arrow" viewBox="0 0 12 12"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
        trigger.setAttribute('onclick', "toggleSS('sucursalSS')");
        document.getElementById('sucursalVal').value = '';

        fetch('/sucursales-por-empresa/' + empresaId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                trigger.classList.remove('loading');
                var html = '<div class="ss-option ss-all" data-value="" onclick="pickSucursal(this)">Todas las sucursales</div>';
                data.forEach(function(s) {
                    var label = (s.codigo ? s.codigo + ' - ' : '') + s.nombre;
                    html += '<div class="ss-option" data-value="' + s.id + '" onclick="pickSucursal(this)">' + label + '</div>';
                });
                optionsDiv.innerHTML = html;
                countDiv.textContent = data.length + ' sucursales';

                trigger.innerHTML = '<span class="ss-placeholder">Todas las sucursales</span>' +
                    '<svg class="ss-arrow" viewBox="0 0 12 12"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
            })
            .catch(function() {
                trigger.classList.remove('loading');
                optionsDiv.innerHTML = '<div class="ss-option ss-all" data-value="" onclick="pickSucursal(this)">Todas las sucursales</div>';
                countDiv.textContent = '';
                trigger.innerHTML = '<span class="ss-placeholder">Todas las sucursales</span>' +
                    '<svg class="ss-arrow" viewBox="0 0 12 12"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
            });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.ss-wrapper')) { closeAllSS(); }
    });
    </script>
</body>
</html>
