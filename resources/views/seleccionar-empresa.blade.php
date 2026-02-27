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
            background: #f0f0f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .selection-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
        .selection-header {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
        }
        .selection-header h2 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
        }
        .selection-close {
            background: none; border: none;
            color: #999; font-size: 1.3rem;
            cursor: pointer; line-height: 1;
        }
        .selection-close:hover { color: #333; }
        .selection-body {
            padding: 1.5rem;
        }
        .selection-label {
            font-size: 0.82rem;
            color: #666;
            margin-bottom: 0.75rem;
            display: block;
        }
        .selection-field {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.88rem;
            color: #333;
            background: #fff;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            padding-right: 2rem;
            cursor: pointer;
            margin-bottom: 1rem;
        }
        .selection-field:focus {
            outline: none;
            border-color: #2bc381;
            box-shadow: 0 0 0 3px rgba(43,195,129,0.15);
        }
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
            padding: 0.75rem;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            background: #2bc381;
            color: #fff;
        }
        .btn-accept:hover { background: #25a96f; }
        .btn-accept:disabled { background: #a0d9bf; cursor: not-allowed; }
        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            background: #888;
            color: #fff;
        }
        .btn-back:hover { background: #6b6b6b; }
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

            <label class="selection-label">Selecciona la empresa a consultar</label>
            <form method="POST" action="/seleccionar-empresa" id="selectionForm">
                @csrf
                <select class="selection-field" name="empresa_id" id="empresaSelect" required>
                    <option value="">Selecciona una empresa</option>
                    @foreach($empresas as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                    @endforeach
                </select>

                <select class="selection-field" name="sucursal_id" id="sucursalSelect">
                    <option value="">Selecciona una sucursal</option>
                </select>
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
                    Regresar
                </button>
            </form>
        </div>
    </div>

    <script>
        var empresaSelect = document.getElementById('empresaSelect');
        var sucursalSelect = document.getElementById('sucursalSelect');
        var btnAccept = document.getElementById('btnAccept');

        empresaSelect.addEventListener('change', function() {
            btnAccept.disabled = !this.value;
            sucursalSelect.innerHTML = '<option value="">Cargando...</option>';

            if (!this.value) {
                sucursalSelect.innerHTML = '<option value="">Selecciona una sucursal</option>';
                return;
            }

            fetch('/sucursales-por-empresa/' + this.value)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var html = '<option value="">Todas las sucursales</option>';
                    data.forEach(function(s) {
                        html += '<option value="' + s.id + '">' + (s.codigo ? s.codigo + ' - ' : '') + s.nombre + '</option>';
                    });
                    sucursalSelect.innerHTML = html;
                })
                .catch(function() {
                    sucursalSelect.innerHTML = '<option value="">Todas las sucursales</option>';
                });
        });
    </script>
</body>
</html>
