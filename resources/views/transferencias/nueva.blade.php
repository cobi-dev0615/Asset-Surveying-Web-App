@extends('layouts.app')
@section('title', 'Solicitud de Transferencia')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        Solicitud de transferencia
    </h2>
</div>

{{-- Info banner --}}
<div style="background:#e8f4fd; border-left:4px solid #2196F3; border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.82rem; color:#1565C0;">
    <strong>Información:</strong><br>
    Selecciona la sucursal de origen de los activos que deseas transferir y agrégalos desde el catálogo.<br>
    Al guardar, los activos serán marcados como "SOLICITADO" y aparecerán en la lista de transferencias solicitadas.
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:1rem;">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('transferencias.store') }}" id="transferenciaForm">
    @csrf

    <div class="card" style="margin-bottom:1rem;">
        <div class="card-body">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                {{-- Sucursal de origen --}}
                <div>
                    <label class="form-label" style="font-weight:600;">Sucursal de origen *</label>
                    <p style="font-size:0.75rem; color:#e91e63; margin:0 0 0.35rem;">Selecciona la sucursal de donde se tomarán los activos</p>
                    <select name="sucursal_origen_id" id="sucursalOrigen" class="form-control" required>
                        <option value="">Seleccionar sucursal...</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}">{{ $suc->codigo ? $suc->codigo . ' - ' : '' }}{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Motivo --}}
                <div>
                    <label class="form-label" style="font-weight:600;">Motivo</label>
                    <p style="font-size:0.75rem; color:var(--text-secondary); margin:0 0 0.35rem;">Selecciona o captura un motivo</p>
                    <select id="motivoSelect" class="form-control" onchange="toggleMotivoCustom()">
                        <option value="">Selecciona un motivo...</option>
                        <option value="Reasignación">Reasignación</option>
                        <option value="Reparación">Reparación</option>
                        <option value="Préstamo">Préstamo</option>
                        <option value="Baja">Baja</option>
                        <option value="Reubicación">Reubicación</option>
                        <option value="__otro__">Otro (capturar)</option>
                    </select>
                    <input type="text" id="motivoCustom" class="form-control" placeholder="Captura el motivo..." style="display:none; margin-top:0.35rem;">
                    <input type="hidden" name="motivo" id="motivoHidden">
                </div>
            </div>

            {{-- Catálogo de activos --}}
            <div style="display:flex; align-items:flex-end; gap:0.75rem; margin-bottom:0.5rem;">
                <div style="flex:1;">
                    <label class="form-label" style="font-weight:600;">Catálogo de activos</label>
                    <p style="font-size:0.75rem; color:var(--text-secondary); margin:0 0 0.35rem;">Busca y selecciona activos de la sucursal de origen</p>
                    <div style="display:flex; gap:0.5rem;">
                        <input type="text" id="buscarActivo" class="form-control" placeholder="Buscar por código, descripción o serie..." style="flex:1;" disabled>
                        <button type="button" id="btnBuscar" class="btn btn-outline" onclick="buscarActivos()" disabled style="white-space:nowrap;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            Buscar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search results dropdown --}}
            <div id="resultadosContainer" style="display:none; border:1px solid var(--border); border-radius:var(--radius); max-height:250px; overflow-y:auto; margin-bottom:0.5rem; background:#fff;">
                <table class="tbl-resultados" style="width:100%; font-size:0.78rem;">
                    <thead>
                        <tr style="background:#f5f5f5;">
                            <th style="padding:0.35rem 0.5rem; width:40px;"></th>
                            <th style="padding:0.35rem 0.5rem;">N° Activo</th>
                            <th style="padding:0.35rem 0.5rem;">Descripción</th>
                            <th style="padding:0.35rem 0.5rem;">N° Serie</th>
                            <th style="padding:0.35rem 0.5rem;">Categoría</th>
                        </tr>
                    </thead>
                    <tbody id="resultadosBody"></tbody>
                </table>
            </div>
            <div id="resultadosLoading" style="display:none; text-align:center; padding:1rem; font-size:0.82rem; color:var(--text-secondary);">
                Buscando activos...
            </div>
        </div>
    </div>

    {{-- Selected assets table --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between;">
            <span>Activos seleccionados para transferencia</span>
            <span id="contadorActivos" style="font-size:0.78rem; color:var(--text-secondary);">0 activos</span>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper">
                <table class="tbl-transfer" id="tblTransfer">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Número de Activo</th>
                            <th>Descripción</th>
                            <th>Número de Tag</th>
                            <th>Número de Tag Nuevo</th>
                            <th>Número de Serie</th>
                            <th>Número de Serie Revisado</th>
                            <th>Categoría</th>
                            <th>Marca</th>
                            <th>Sucursal de Origen</th>
                            <th style="width:70px;">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody id="activosBody">
                        <tr id="emptyRow">
                            <td colspan="11" class="table-empty">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:32px; height:32px; margin-bottom:0.3rem;"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                <div>Selecciona activos del catálogo para agregarlos a esta transferencia</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Comentarios --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-body">
            <label class="form-label" style="font-weight:600;">Comentarios</label>
            <textarea name="comentarios" class="form-control" rows="3" placeholder="Comentarios adicionales sobre la transferencia (opcional)..." style="resize:vertical;"></textarea>
        </div>
    </div>

    {{-- Action buttons --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:2rem;">
        <button type="submit" id="btnGuardar" class="btn" style="background:#4CAF50; color:#fff; padding:0.7rem; font-size:0.9rem; font-weight:600; border:none; border-radius:var(--radius); cursor:pointer;" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px; height:18px; vertical-align:middle; margin-right:0.3rem;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Guardar
        </button>
        <a href="{{ route('transferencias.solicitadas') }}" class="btn" style="background:#FFC107; color:#333; padding:0.7rem; font-size:0.9rem; font-weight:600; border:none; border-radius:var(--radius); text-align:center; text-decoration:none; display:block;">
            Cancelar
        </a>
    </div>

    {{-- Hidden inputs for activos --}}
    <div id="hiddenInputs"></div>
</form>

@push('styles')
<style>
    .tbl-transfer { table-layout: auto; width: 100%; }
    .tbl-transfer td, .tbl-transfer th { font-size: 0.76rem; padding: 0.4rem 0.5rem; white-space: nowrap; }
    .tbl-transfer th { background: var(--bg-secondary); font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.02em; }
    .tbl-transfer tbody tr:hover { background: #e8f0ed !important; }

    .tbl-resultados td, .tbl-resultados th { font-size: 0.76rem; padding: 0.35rem 0.5rem; }
    .tbl-resultados tbody tr { cursor: pointer; }
    .tbl-resultados tbody tr:hover { background: #e8f0ed !important; }
    .tbl-resultados tbody tr.selected { background: #c8e6c9 !important; }

    .btn-remove {
        background: #f44336; color: #fff; border: none; border-radius: 3px;
        padding: 0.25rem 0.5rem; font-size: 0.72rem; cursor: pointer; font-weight: 600;
    }
    .btn-remove:hover { background: #d32f2f; }

    .img-thumb {
        width: 36px; height: 36px; object-fit: cover; border-radius: 3px; cursor: pointer;
        border: 1px solid var(--border);
    }
    .img-placeholder {
        width: 36px; height: 36px; background: #f0f0f0; border-radius: 3px;
        display: flex; align-items: center; justify-content: center;
        color: #ccc; font-size: 0.65rem;
    }
</style>
@endpush

@push('scripts')
<script>
    var selectedActivos = {};
    var sucursalNombre = '';

    // Enable/disable search when sucursal changes
    document.getElementById('sucursalOrigen').addEventListener('change', function() {
        var val = this.value;
        var buscarInput = document.getElementById('buscarActivo');
        var btnBuscar = document.getElementById('btnBuscar');

        if (val) {
            buscarInput.disabled = false;
            btnBuscar.disabled = false;
            sucursalNombre = this.options[this.selectedIndex].text;
            // Clear previous results
            document.getElementById('resultadosContainer').style.display = 'none';
            document.getElementById('resultadosBody').innerHTML = '';
            buscarInput.value = '';
            // Auto-search to show available assets
            buscarActivos();
        } else {
            buscarInput.disabled = true;
            btnBuscar.disabled = true;
            sucursalNombre = '';
            document.getElementById('resultadosContainer').style.display = 'none';
        }
    });

    // Search on Enter in the search field
    document.getElementById('buscarActivo').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarActivos();
        }
    });

    function buscarActivos() {
        var sucursalId = document.getElementById('sucursalOrigen').value;
        if (!sucursalId) return;

        var buscar = document.getElementById('buscarActivo').value;
        var url = '{{ route("transferencias.activos") }}?sucursal_id=' + sucursalId;
        if (buscar) url += '&buscar=' + encodeURIComponent(buscar);

        document.getElementById('resultadosLoading').style.display = 'block';
        document.getElementById('resultadosContainer').style.display = 'none';

        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('resultadosLoading').style.display = 'none';
                var body = document.getElementById('resultadosBody');
                body.innerHTML = '';

                if (data.length === 0) {
                    body.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:1rem; color:var(--text-secondary);">No se encontraron activos disponibles</td></tr>';
                } else {
                    data.forEach(function(a) {
                        var isSelected = selectedActivos[a.id] ? true : false;
                        var tr = document.createElement('tr');
                        tr.className = isSelected ? 'selected' : '';
                        tr.setAttribute('data-id', a.id);
                        tr.onclick = function() { toggleActivo(a); };
                        tr.innerHTML =
                            '<td style="text-align:center;"><input type="checkbox" ' + (isSelected ? 'checked' : '') + ' onclick="event.stopPropagation(); toggleActivo(' + JSON.stringify(a).replace(/"/g, '&quot;') + ');"></td>' +
                            '<td style="font-weight:500;">' + escHtml(a.codigo_1) + '</td>' +
                            '<td style="max-width:200px; overflow:hidden; text-overflow:ellipsis;">' + escHtml(a.descripcion) + '</td>' +
                            '<td>' + escHtml(a.n_serie) + '</td>' +
                            '<td>' + escHtml(a.categoria) + '</td>';
                        body.appendChild(tr);
                    });
                }

                document.getElementById('resultadosContainer').style.display = 'block';
            })
            .catch(function() {
                document.getElementById('resultadosLoading').style.display = 'none';
            });
    }

    function toggleActivo(activo) {
        if (selectedActivos[activo.id]) {
            // Remove
            delete selectedActivos[activo.id];
        } else {
            // Add
            selectedActivos[activo.id] = activo;
        }
        renderTable();
        renderHiddenInputs();
        updateResultadosSelection();
        updateContador();
    }

    function removeActivo(id) {
        delete selectedActivos[id];
        renderTable();
        renderHiddenInputs();
        updateResultadosSelection();
        updateContador();
    }

    function renderTable() {
        var body = document.getElementById('activosBody');
        var keys = Object.keys(selectedActivos);

        if (keys.length === 0) {
            body.innerHTML = '<tr id="emptyRow"><td colspan="11" class="table-empty">' +
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:32px; height:32px; margin-bottom:0.3rem;"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>' +
                '<div>Selecciona activos del catálogo para agregarlos a esta transferencia</div></td></tr>';
            document.getElementById('btnGuardar').disabled = true;
            return;
        }

        document.getElementById('btnGuardar').disabled = false;
        body.innerHTML = '';

        keys.forEach(function(id) {
            var a = selectedActivos[id];
            var tr = document.createElement('tr');

            var imgCell = '';
            if (a.imagen1) {
                imgCell = '<img src="/storage/fotos/activos/' + escHtml(a.imagen1) + '" class="img-thumb" onerror="this.outerHTML=\'<div class=img-placeholder>—</div>\'">';
            } else {
                imgCell = '<div class="img-placeholder">—</div>';
            }

            tr.innerHTML =
                '<td>' + imgCell + '</td>' +
                '<td style="font-weight:500;">' + escHtml(a.codigo_1) + '</td>' +
                '<td style="max-width:200px; overflow:hidden; text-overflow:ellipsis;">' + escHtml(a.descripcion) + '</td>' +
                '<td>' + escHtml(a.codigo_2) + '</td>' +
                '<td>' + escHtml(a.codigo_3) + '</td>' +
                '<td>' + escHtml(a.n_serie) + '</td>' +
                '<td>' + escHtml(a.n_serie_nuevo) + '</td>' +
                '<td>' + escHtml(a.categoria) + '</td>' +
                '<td>' + escHtml(a.marca) + '</td>' +
                '<td style="max-width:150px; overflow:hidden; text-overflow:ellipsis;">' + escHtml(sucursalNombre) + '</td>' +
                '<td><button type="button" class="btn-remove" onclick="removeActivo(' + id + ')">Eliminar</button></td>';

            body.appendChild(tr);
        });
    }

    function renderHiddenInputs() {
        var container = document.getElementById('hiddenInputs');
        container.innerHTML = '';
        Object.keys(selectedActivos).forEach(function(id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'activos[]';
            input.value = id;
            container.appendChild(input);
        });
    }

    function updateResultadosSelection() {
        var rows = document.querySelectorAll('#resultadosBody tr[data-id]');
        rows.forEach(function(row) {
            var id = row.getAttribute('data-id');
            var cb = row.querySelector('input[type=checkbox]');
            if (selectedActivos[id]) {
                row.className = 'selected';
                if (cb) cb.checked = true;
            } else {
                row.className = '';
                if (cb) cb.checked = false;
            }
        });
    }

    function updateContador() {
        var count = Object.keys(selectedActivos).length;
        document.getElementById('contadorActivos').textContent = count + ' activo' + (count !== 1 ? 's' : '');
    }

    // Motivo toggle
    function toggleMotivoCustom() {
        var sel = document.getElementById('motivoSelect');
        var custom = document.getElementById('motivoCustom');
        if (sel.value === '__otro__') {
            custom.style.display = 'block';
            custom.focus();
        } else {
            custom.style.display = 'none';
            custom.value = '';
        }
    }

    // Set motivo hidden input on submit
    document.getElementById('transferenciaForm').addEventListener('submit', function(e) {
        var sel = document.getElementById('motivoSelect');
        var custom = document.getElementById('motivoCustom');
        var hidden = document.getElementById('motivoHidden');

        if (sel.value === '__otro__') {
            hidden.value = custom.value;
        } else {
            hidden.value = sel.value;
        }

        if (Object.keys(selectedActivos).length === 0) {
            e.preventDefault();
            alert('Debes seleccionar al menos un activo para transferir.');
        }
    });

    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
</script>
@endpush
@endsection
