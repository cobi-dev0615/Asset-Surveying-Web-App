@extends('layouts.app')
@section('title', 'Tablero')

@section('content')
<div class="subheader" style="margin-bottom:1.25rem;">
    <h1 style="font-size:1.15rem; font-weight:400; color:var(--text);" id="saludo"></h1>
</div>

{{-- Session selector (hidden input for JS) --}}
<input type="hidden" id="sesionSelect" value="{{ $sesionId }}">

{{-- ══════════════════════════════════════════════════════════════
     MODAL: Selección de Inventario
     ══════════════════════════════════════════════════════════════ --}}
<div class="sesion-modal-overlay" id="sesionModalOverlay" style="display:none;">
    <div class="sesion-modal">
        <div class="sesion-modal-header">
            <h3>Selecciona el inventario que deseas consultar</h3>
            <button type="button" class="sesion-modal-close" onclick="closeSesionModal()">&times;</button>
        </div>
        <div class="sesion-modal-body">
            {{-- Empresa dropdown --}}
            <label class="sesion-modal-label">Selecciona la empresa a consultar</label>
            <div class="search-select" id="ssEmpresa">
                <div class="search-select-trigger" onclick="toggleSearchSelect('ssEmpresa')">
                    <span class="search-select-value" data-placeholder="Selecciona una empresa">Selecciona una empresa</span>
                    <span class="search-select-clear" onclick="event.stopPropagation(); clearSearchSelect('ssEmpresa'); clearSearchSelect('ssSucursal');" style="display:none;">&times;</span>
                    <svg class="search-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="search-select-dropdown">
                    <div class="search-select-search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" placeholder="Buscar..." oninput="filterSearchSelect('ssEmpresa', this.value)">
                    </div>
                    <ul class="search-select-options">
                        @foreach($empresas as $emp)
                        <li data-value="{{ $emp->id }}" onclick="selectSearchOption('ssEmpresa', '{{ $emp->id }}', '{{ addslashes($emp->nombre) }}'); loadSucursales({{ $emp->id }})">
                            {{ $emp->nombre }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Sucursal dropdown --}}
            <div style="margin-top:1rem;">
                <div class="search-select" id="ssSucursal">
                    <div class="search-select-trigger" onclick="toggleSearchSelect('ssSucursal')">
                        <span class="search-select-value" data-placeholder="Selecciona una sucursal">Selecciona una sucursal</span>
                        <span class="search-select-clear" onclick="event.stopPropagation(); clearSearchSelect('ssSucursal');" style="display:none;">&times;</span>
                        <svg class="search-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="search-select-dropdown">
                        <div class="search-select-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" placeholder="Buscar..." oninput="filterSearchSelect('ssSucursal', this.value)">
                        </div>
                        <ul class="search-select-options">
                            <li class="search-select-empty">Primero selecciona una empresa</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="sesion-modal-footer">
            <button type="button" class="btn-modal-accept" onclick="acceptSesionModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;"><polyline points="20 6 9 17 4 12"/></svg>
                Aceptar
            </button>
            <button type="button" class="btn-modal-back" onclick="closeSesionModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="15 18 9 12 15 6"/></svg>
                Regresar
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PANEL 1: AVANCE GENERAL DEL INVENTARIO
     ══════════════════════════════════════════════════════════════ --}}
<div class="panel">
    <div class="panel-hdr">
        <h2>AVANCE GENERAL DEL INVENTARIO</h2>
        <div class="panel-toolbar">
            <button class="btn btn-sm btn-primary" id="btnRefreshGeneral" onclick="refreshAvanceGeneral()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Actualizar información
            </button>
        </div>
    </div>
    <div class="panel-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; align-items:center;">
            {{-- Left: Summary Table --}}
            <div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ACTIVOS EN CATÁLOGO</th>
                                <th>ACTIVOS ENCONTRADOS</th>
                                <th>ACTIVOS NO ENCONTRADOS</th>
                                <th>PENDIENTES POR CONTAR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:center;"><span id="valCatalogo" style="font-size:1.5rem; font-weight:700;">{{ number_format($avanceGeneral['catalogo']) }}</span></td>
                                <td style="text-align:center;"><span id="valEncontrados" style="font-size:1.5rem; font-weight:700; color:var(--success);">{{ number_format($avanceGeneral['encontrados']) }}</span></td>
                                <td style="text-align:center;"><span id="valNoEncontrados" style="font-size:1.5rem; font-weight:700; color:var(--danger);">{{ number_format($avanceGeneral['no_encontrados']) }}</span></td>
                                <td style="text-align:center;"><span id="valPendientes" style="font-size:1.5rem; font-weight:700; color:var(--info);">{{ number_format($avanceGeneral['pendientes']) }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Right: Donut Chart --}}
            <div style="display:flex; justify-content:center; align-items:center;">
                <div style="position:relative; width:280px; height:280px;">
                    <canvas id="chartAvanceGeneral"></canvas>
                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center;">
                        <div style="font-size:0.72rem; color:var(--text-secondary);">Avance del</div>
                        <div style="font-size:0.72rem; color:var(--text-secondary);">inventario</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PANEL 2: AVANCE POR ÁREA
     ══════════════════════════════════════════════════════════════ --}}
<div class="panel">
    <div class="panel-hdr">
        <h2>AVANCE POR ÁREA</h2>
        <div class="panel-toolbar">
            <button class="btn btn-sm btn-primary" id="btnRefreshArea" onclick="refreshAvanceArea()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Actualizar información
            </button>
        </div>
    </div>
    <div class="panel-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; align-items:start;">
            {{-- Left: Area Table --}}
            <div>
                <div class="table-wrapper">
                    @php $totalArea = collect($avancePorArea)->sum('cantidad'); @endphp
                    <table id="tablaAvanceArea" class="dashboard-table">
                        <thead>
                            <tr>
                                <th style="width:24px;">#</th>
                                <th>DEPARTAMENTO / ALMACÉN</th>
                                <th style="width:110px; text-align:right;">CONTADOS</th>
                                <th style="width:60px; text-align:right;">%</th>
                                <th style="width:120px;"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyArea">
                            @forelse($avancePorArea as $i => $area)
                            @php $pct = $totalArea > 0 ? round(($area['cantidad'] / $totalArea) * 100, 1) : 0; @endphp
                            <tr>
                                <td style="color:var(--text-secondary); font-size:0.75rem;">{{ $i + 1 }}</td>
                                <td>
                                    <span class="color-dot" style="background:{{ $colores[$i % count($colores)] ?? '#999' }};"></span>
                                    {{ $area['area'] }}
                                </td>
                                <td style="text-align:right; font-weight:600;">{{ number_format($area['cantidad']) }}</td>
                                <td style="text-align:right; color:var(--text-secondary); font-size:0.8rem;">{{ $pct }}%</td>
                                <td>
                                    <div class="mini-bar"><div class="mini-bar-fill" style="width:{{ $pct }}%; background:{{ $colores[$i % count($colores)] ?? '#999' }};"></div></div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center; color:var(--text-secondary); padding:1.5rem;">Sin datos</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot id="tfootArea">
                            @if(count($avancePorArea) > 0)
                            <tr class="total-row">
                                <td></td>
                                <td style="font-weight:700;">TOTAL</td>
                                <td style="text-align:right; font-weight:700; color:var(--primary);">{{ number_format($totalArea) }}</td>
                                <td style="text-align:right; font-weight:700; font-size:0.8rem;">100%</td>
                                <td></td>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>
            {{-- Right: Donut Chart --}}
            <div style="display:flex; justify-content:center; align-items:center;">
                <div style="position:relative; width:280px; height:280px;">
                    <canvas id="chartAvanceArea"></canvas>
                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; pointer-events:none;">
                        <div style="font-size:0.82rem; font-weight:600; color:var(--text);">Porcentaje por área</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PANEL 3: ACTIVOS INVENTARIADOS POR CATEGORÍA
     ══════════════════════════════════════════════════════════════ --}}
<div class="panel">
    <div class="panel-hdr">
        <h2>ACTIVOS INVENTARIADOS POR CATEGORÍA</h2>
        <div class="panel-toolbar">
            <button class="btn btn-sm btn-primary" id="btnRefreshCategoria" onclick="refreshAvanceCategoria()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Actualizar información
            </button>
        </div>
    </div>
    <div class="panel-body">
        {{-- Bar Chart (full width) --}}
        <div style="width:100%; height:400px; margin-bottom:1.5rem;">
            <canvas id="chartAvanceCategoria"></canvas>
        </div>
        {{-- Category Table --}}
        <div class="table-wrapper" style="max-height:420px; overflow-y:auto;">
            @php $totalCat = collect($avancePorCategoria)->sum('cantidad'); @endphp
            <table id="tablaAvanceCategoria" class="dashboard-table">
                <thead>
                    <tr>
                        <th style="width:24px;">#</th>
                        <th>CATEGORÍA</th>
                        <th style="width:110px; text-align:right;">CONTADOS</th>
                        <th style="width:60px; text-align:right;">%</th>
                        <th style="width:120px;"></th>
                    </tr>
                </thead>
                <tbody id="tbodyCategoria">
                    @forelse($avancePorCategoria as $i => $cat)
                    @php $pct = $totalCat > 0 ? round(($cat['cantidad'] / $totalCat) * 100, 1) : 0; @endphp
                    <tr>
                        <td style="color:var(--text-secondary); font-size:0.75rem;">{{ $i + 1 }}</td>
                        <td>
                            <span class="color-dot" style="background:{{ $colores[$i % count($colores)] ?? '#999' }};"></span>
                            {{ $cat['categoria'] }}
                        </td>
                        <td style="text-align:right; font-weight:600;">{{ number_format($cat['cantidad']) }}</td>
                        <td style="text-align:right; color:var(--text-secondary); font-size:0.8rem;">{{ $pct }}%</td>
                        <td>
                            <div class="mini-bar"><div class="mini-bar-fill" style="width:{{ $pct }}%; background:{{ $colores[$i % count($colores)] ?? '#999' }};"></div></div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:var(--text-secondary); padding:1.5rem;">Sin datos</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot id="tfootCategoria">
                    @if(count($avancePorCategoria) > 0)
                    <tr class="total-row">
                        <td></td>
                        <td style="font-weight:700;">TOTAL</td>
                        <td style="text-align:right; font-weight:700; color:var(--primary);">{{ number_format($totalCat) }}</td>
                        <td style="text-align:right; font-weight:700; font-size:0.8rem;">100%</td>
                        <td></td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
var chartGeneral, chartArea, chartCategoria;
var colores = ['#ff4444','#00C851','#4285F4','#33b5e5','#ffbb33','#aa66cc','#2BBBAD','#2E2E2E','#3F729B','#c51162'];

function getSesionId() {
    var el = document.getElementById('sesionSelect');
    return el ? el.value : '';
}

function fmt(n) {
    return Number(n).toLocaleString('es-MX');
}

// ── Spin animation for refresh buttons ──
function spinBtn(btn, spinning) {
    var svg = btn.querySelector('svg');
    if (spinning) {
        btn.disabled = true;
        svg.style.animation = 'spin 0.8s linear infinite';
    } else {
        btn.disabled = false;
        svg.style.animation = '';
    }
}

// ══════════════════════════════════════════════════════════
// REFRESH PANEL 1: Avance General
// ══════════════════════════════════════════════════════════
function refreshAvanceGeneral() {
    var btn = document.getElementById('btnRefreshGeneral');
    spinBtn(btn, true);
    fetch('/dashboard/avance-general?sesion_id=' + getSesionId())
        .then(function(r) { return r.json(); })
        .then(function(d) {
            document.getElementById('valCatalogo').textContent = fmt(d.catalogo);
            document.getElementById('valEncontrados').textContent = fmt(d.encontrados);
            document.getElementById('valNoEncontrados').textContent = fmt(d.no_encontrados);
            document.getElementById('valPendientes').textContent = fmt(d.pendientes);

            if (chartGeneral) {
                chartGeneral.data.datasets[0].data = [d.pct_pendientes, d.pct_encontrados, d.pct_no_encontrados];
                chartGeneral.update();
            }
            showToast('Avance general actualizado', 'success');
        })
        .catch(function() { showToast('Error al actualizar', 'error'); })
        .finally(function() { spinBtn(btn, false); });
}

// ══════════════════════════════════════════════════════════
// REFRESH PANEL 2: Avance por Área
// ══════════════════════════════════════════════════════════
function refreshAvanceArea() {
    var btn = document.getElementById('btnRefreshArea');
    spinBtn(btn, true);
    fetch('/dashboard/avance-area?sesion_id=' + getSesionId())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            // Update table
            var tbody = document.getElementById('tbodyArea');
            var tfoot = document.getElementById('tfootArea');
            var total = 0;

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--text-secondary); padding:1.5rem;">Sin datos</td></tr>';
                tfoot.innerHTML = '';
            } else {
                data.forEach(function(r) { total += r.cantidad; });
                var rows = '';
                data.forEach(function(r, i) {
                    var pct = total > 0 ? ((r.cantidad / total) * 100).toFixed(1) : 0;
                    var c = colores[i % colores.length];
                    rows += '<tr>'
                        + '<td style="color:var(--text-secondary);font-size:0.75rem;">' + (i+1) + '</td>'
                        + '<td><span class="color-dot" style="background:' + c + ';"></span>' + r.area + '</td>'
                        + '<td style="text-align:right;font-weight:600;">' + fmt(r.cantidad) + '</td>'
                        + '<td style="text-align:right;color:var(--text-secondary);font-size:0.8rem;">' + pct + '%</td>'
                        + '<td><div class="mini-bar"><div class="mini-bar-fill" style="width:' + pct + '%;background:' + c + ';"></div></div></td>'
                        + '</tr>';
                });
                tbody.innerHTML = rows;
                tfoot.innerHTML = '<tr class="total-row"><td></td><td style="font-weight:700;">TOTAL</td><td style="text-align:right;font-weight:700;color:var(--primary);">' + fmt(total) + '</td><td style="text-align:right;font-weight:700;font-size:0.8rem;">100%</td><td></td></tr>';
            }

            // Update chart
            var labels = data.map(function(r) { return r.area; });
            var values = data.map(function(r) { return r.cantidad; });
            if (chartArea) {
                chartArea.data.labels = labels;
                chartArea.data.datasets[0].data = values;
                chartArea.data.datasets[0].backgroundColor = colores.slice(0, labels.length);
                chartArea.update();
            } else if (labels.length > 0) {
                chartArea = createAreaChart(labels, values);
            }
            showToast('Avance por área actualizado', 'success');
        })
        .catch(function() { showToast('Error al actualizar', 'error'); })
        .finally(function() { spinBtn(btn, false); });
}

// ══════════════════════════════════════════════════════════
// REFRESH PANEL 3: Avance por Categoría
// ══════════════════════════════════════════════════════════
function refreshAvanceCategoria() {
    var btn = document.getElementById('btnRefreshCategoria');
    spinBtn(btn, true);
    fetch('/dashboard/avance-categoria?sesion_id=' + getSesionId())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            // Update table
            var tbody = document.getElementById('tbodyCategoria');
            var tfoot = document.getElementById('tfootCategoria');
            var total = 0;

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--text-secondary); padding:1.5rem;">Sin datos</td></tr>';
                tfoot.innerHTML = '';
            } else {
                data.forEach(function(r) { total += r.cantidad; });
                var rows = '';
                data.forEach(function(r, i) {
                    var pct = total > 0 ? ((r.cantidad / total) * 100).toFixed(1) : 0;
                    var c = colores[i % colores.length];
                    rows += '<tr>'
                        + '<td style="color:var(--text-secondary);font-size:0.75rem;">' + (i+1) + '</td>'
                        + '<td><span class="color-dot" style="background:' + c + ';"></span>' + r.categoria + '</td>'
                        + '<td style="text-align:right;font-weight:600;">' + fmt(r.cantidad) + '</td>'
                        + '<td style="text-align:right;color:var(--text-secondary);font-size:0.8rem;">' + pct + '%</td>'
                        + '<td><div class="mini-bar"><div class="mini-bar-fill" style="width:' + pct + '%;background:' + c + ';"></div></div></td>'
                        + '</tr>';
                });
                tbody.innerHTML = rows;
                tfoot.innerHTML = '<tr class="total-row"><td></td><td style="font-weight:700;">TOTAL</td><td style="text-align:right;font-weight:700;color:var(--primary);">' + fmt(total) + '</td><td style="text-align:right;font-weight:700;font-size:0.8rem;">100%</td><td></td></tr>';
            }

            // Update chart
            var labels = data.map(function(r) { return r.categoria; });
            var values = data.map(function(r) { return r.cantidad; });
            if (chartCategoria) {
                chartCategoria.data.labels = labels;
                chartCategoria.data.datasets[0].data = values;
                chartCategoria.data.datasets[0].backgroundColor = labels.map(function(_, i) { return colores[i % colores.length]; });
                chartCategoria.update();
            } else if (labels.length > 0) {
                chartCategoria = createCategoriaChart(labels, values);
            }
            showToast('Avance por categoría actualizado', 'success');
        })
        .catch(function() { showToast('Error al actualizar', 'error'); })
        .finally(function() { spinBtn(btn, false); });
}

// ── Chart creation helpers ──

// Plugin: draw percentage labels on doughnut segments
var doughnutLabelsPlugin = {
    id: 'doughnutLabels',
    afterDraw: function(chart) {
        var ctx = chart.ctx;
        var dataset = chart.data.datasets[0];
        var meta = chart.getDatasetMeta(0);
        var total = dataset.data.reduce(function(a, b) { return a + b; }, 0);
        if (total === 0) return;

        ctx.save();
        ctx.font = 'bold 11px sans-serif';
        ctx.fillStyle = '#fff';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        meta.data.forEach(function(arc, i) {
            var pct = ((dataset.data[i] / total) * 100);
            if (pct < 3) return; // skip tiny slices
            var props = arc.getProps(['x', 'y', 'startAngle', 'endAngle', 'innerRadius', 'outerRadius']);
            var midAngle = (props.startAngle + props.endAngle) / 2;
            var midRadius = (props.innerRadius + props.outerRadius) / 2;
            var x = props.x + Math.cos(midAngle) * midRadius;
            var y = props.y + Math.sin(midAngle) * midRadius;
            ctx.fillText(pct.toFixed(1) + '%', x, y);
        });
        ctx.restore();
    }
};

function createAreaChart(labels, values) {
    return new Chart(document.getElementById('chartAvanceArea'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: values, backgroundColor: colores.slice(0, labels.length), borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '55%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function(ctx) {
                    var total = ctx.dataset.data.reduce(function(a,b){return a+b;}, 0);
                    return ctx.label + ': ' + ctx.parsed + ' (' + ((ctx.parsed/total)*100).toFixed(1) + '%)';
                }}}
            }
        },
        plugins: [doughnutLabelsPlugin]
    });
}

function createCategoriaChart(labels, values) {
    return new Chart(document.getElementById('chartAvanceCategoria'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'Activos contados', data: values, backgroundColor: labels.map(function(_,i){return colores[i%colores.length];}), borderRadius: 3 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                y: { beginAtZero: true, grid: { color: '#eee' } }
            }
        }
    });
}

// ══════════════════════════════════════════════════════════
// SESSION SELECTION MODAL
// ══════════════════════════════════════════════════════════
var _ssValues = {}; // stores selected values per search-select id

function openSesionModal() {
    document.getElementById('sesionModalOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeSesionModal() {
    document.getElementById('sesionModalOverlay').style.display = 'none';
    document.body.style.overflow = '';
}

function toggleSearchSelect(id) {
    var el = document.getElementById(id);
    var wasOpen = el.classList.contains('open');
    // Close all other dropdowns first
    document.querySelectorAll('.search-select.open').forEach(function(s) { s.classList.remove('open'); });
    if (!wasOpen) {
        el.classList.add('open');
        var input = el.querySelector('.search-select-search input');
        if (input) { input.value = ''; filterSearchSelect(id, ''); setTimeout(function() { input.focus(); }, 50); }
    }
}

function filterSearchSelect(id, term) {
    var el = document.getElementById(id);
    var items = el.querySelectorAll('.search-select-options li:not(.search-select-empty)');
    var lower = term.toLowerCase();
    var visible = 0;
    items.forEach(function(li) {
        var match = li.textContent.toLowerCase().indexOf(lower) !== -1;
        li.classList.toggle('hidden', !match);
        if (match) visible++;
    });
    // Show/hide empty message
    var empty = el.querySelector('.search-select-empty');
    if (empty) empty.style.display = visible === 0 ? '' : 'none';
}

function selectSearchOption(id, value, label) {
    var el = document.getElementById(id);
    var display = el.querySelector('.search-select-value');
    var clearBtn = el.querySelector('.search-select-clear');
    display.textContent = label;
    display.classList.remove('placeholder');
    clearBtn.style.display = '';
    el.classList.remove('open');
    _ssValues[id] = value;
    // Mark active
    el.querySelectorAll('.search-select-options li').forEach(function(li) {
        li.classList.toggle('active', li.getAttribute('data-value') === String(value));
    });
}

function clearSearchSelect(id) {
    var el = document.getElementById(id);
    var display = el.querySelector('.search-select-value');
    var clearBtn = el.querySelector('.search-select-clear');
    display.textContent = display.getAttribute('data-placeholder');
    display.classList.add('placeholder');
    clearBtn.style.display = 'none';
    delete _ssValues[id];
    el.querySelectorAll('.search-select-options li.active').forEach(function(li) { li.classList.remove('active'); });
    // If clearing empresa, reset sucursal options
    if (id === 'ssEmpresa') {
        var sucList = document.querySelector('#ssSucursal .search-select-options');
        sucList.innerHTML = '<li class="search-select-empty">Primero selecciona una empresa</li>';
    }
}

function loadSucursales(empresaId) {
    var sucEl = document.getElementById('ssSucursal');
    var sucList = sucEl.querySelector('.search-select-options');
    // Reset sucursal selection
    clearSearchSelect('ssSucursal');
    sucList.innerHTML = '<li class="search-select-empty">Cargando...</li>';

    fetch('/sucursales-por-empresa/' + empresaId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.length === 0) {
                sucList.innerHTML = '<li class="search-select-empty">No hay sucursales</li>';
                return;
            }
            var html = '';
            data.forEach(function(s) {
                var label = (s.codigo ? s.codigo + ' - ' : '') + s.nombre;
                html += '<li data-value="' + s.id + '" onclick="selectSearchOption(\'ssSucursal\', \'' + s.id + '\', \'' + label.replace(/'/g, "\\'") + '\')">' + label + '</li>';
            });
            sucList.innerHTML = html;
        })
        .catch(function() {
            sucList.innerHTML = '<li class="search-select-empty">Error al cargar</li>';
        });
}

function acceptSesionModal() {
    var empresaId = _ssValues['ssEmpresa'];
    var sucursalId = _ssValues['ssSucursal'];
    if (!empresaId) {
        showToast('Selecciona una empresa', 'warning');
        return;
    }
    // Find matching sessions
    var params = '?empresa_id=' + empresaId;
    if (sucursalId) params += '&sucursal_id=' + sucursalId;

    fetch('/dashboard/sesiones' + params)
        .then(function(r) { return r.json(); })
        .then(function(sessions) {
            if (sessions.length === 0) {
                showToast('No se encontraron sesiones de inventario para esta selección', 'warning');
                return;
            }
            // Redirect to dashboard with the first (most recent) session
            window.location.href = '/dashboard?sesion_id=' + sessions[0].id;
        })
        .catch(function() { showToast('Error al buscar sesiones', 'error'); });
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
    var overlay = document.getElementById('sesionModalOverlay');
    if (e.target === overlay) closeSesionModal();
});
// Close open dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-select')) {
        document.querySelectorAll('.search-select.open').forEach(function(s) { s.classList.remove('open'); });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // ── Auto-open modal if no session selected ──
    @if(!$sesionActual && $empresas->count() > 0)
        openSesionModal();
    @endif

    // ── Greeting based on time of day ──
    var hora = new Date().getHours();
    var saludo = hora < 12 ? '&#9728; Buen día' : (hora <= 19 ? '&#9728;&#65039; Buena tarde' : '&#127769; Buena noche');
    var fecha = new Date().toLocaleDateString('es-MX', { day:'2-digit', month:'2-digit', year:'numeric' });
    document.getElementById('saludo').innerHTML = saludo + ' {{ $user->nombres }} <small style="color:var(--text-secondary); font-size:0.82rem;">Estos son los reportes generales del inventario al día de hoy ' + fecha + '.</small>';

    // ── Chart 1: Avance General (Donut) ──
    var ctx1 = document.getElementById('chartAvanceGeneral');
    if (ctx1) {
        chartGeneral = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['ACTIVOS PENDIENTES POR CONTAR', 'ENCONTRADOS', 'NO ENCONTRADOS'],
                datasets: [{
                    data: [{{ $avanceGeneral['pct_pendientes'] }}, {{ $avanceGeneral['pct_encontrados'] }}, {{ $avanceGeneral['pct_no_encontrados'] }}],
                    backgroundColor: ['#0d47a1', '#007E33', '#9933CC'],
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '55%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 15, font: { size: 11 }, usePointStyle: true } },
                    tooltip: { callbacks: { label: function(ctx) { return ctx.label + ': ' + ctx.parsed.toFixed(1) + '%'; } } }
                }
            }
        });
    }

    // ── Chart 2: Avance por Área (Donut) ──
    var areaLabels = @json(array_column($avancePorArea, 'area'));
    var areaValues = @json(array_column($avancePorArea, 'cantidad'));
    if (areaLabels.length > 0) {
        chartArea = createAreaChart(areaLabels, areaValues);
    }

    // ── Chart 3: Avance por Categoría (Bar) ──
    var catLabels = @json(array_column($avancePorCategoria, 'categoria'));
    var catValues = @json(array_column($avancePorCategoria, 'cantidad'));
    if (catLabels.length > 0) {
        chartCategoria = createCategoriaChart(catLabels, catValues);
    }
});
</script>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes modalFadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes modalSlideIn { from { opacity:0; transform:translateY(-30px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }

/* ── Session Selection Modal ── */
.sesion-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    animation: modalFadeIn 0.2s ease;
}
.sesion-modal {
    background: #fff; border-radius: 8px; width: 520px; max-width: 92vw;
    max-height: 90vh; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalSlideIn 0.25s ease;
}
.sesion-modal-header {
    padding: 1.25rem 1.5rem 1rem; display: flex; align-items: center; justify-content: space-between;
}
.sesion-modal-header h3 {
    margin: 0; font-size: 1rem; font-weight: 600; color: #333; text-align: center; flex: 1;
}
.sesion-modal-close {
    background: none; border: none; font-size: 1.5rem; color: #999; cursor: pointer;
    line-height: 1; padding: 0; margin-left: 0.5rem;
}
.sesion-modal-close:hover { color: #333; }
.sesion-modal-body { padding: 0 1.5rem 1.25rem; }
.sesion-modal-label { font-size: 0.82rem; font-weight: 500; color: #555; margin-bottom: 0.5rem; display: block; }
.sesion-modal-footer { padding: 0 1.5rem 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; }

.btn-modal-accept {
    width: 100%; padding: 0.7rem; border: none; border-radius: 6px;
    background: #2dce89; color: #fff; font-size: 0.9rem; font-weight: 600;
    cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
    transition: background 0.15s;
}
.btn-modal-accept:hover { background: #26b578; }
.btn-modal-back {
    width: 100%; padding: 0.7rem; border: none; border-radius: 6px;
    background: #6c757d; color: #fff; font-size: 0.9rem; font-weight: 500;
    cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
    transition: background 0.15s;
}
.btn-modal-back:hover { background: #5a6268; }

/* ── Searchable Select Component ── */
.search-select { position: relative; }
.search-select-trigger {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.75rem;
    border: 1px solid #ccc; border-radius: 6px; cursor: pointer; background: #fff;
    transition: border-color 0.15s;
}
.search-select-trigger:hover { border-color: #999; }
.search-select.open .search-select-trigger { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(13,110,253,0.15); }
.search-select-value { flex: 1; font-size: 0.88rem; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.search-select-value.placeholder { color: #999; }
.search-select-clear {
    background: none; border: none; font-size: 1.2rem; color: #b066cc; cursor: pointer;
    padding: 0 2px; line-height: 1;
}
.search-select-clear:hover { color: #8844aa; }
.search-select-arrow { flex-shrink: 0; color: #999; transition: transform 0.2s; }
.search-select.open .search-select-arrow { transform: rotate(180deg); }

.search-select-dropdown {
    display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: #fff; border: 1px solid #ddd; border-radius: 6px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 10; overflow: hidden;
}
.search-select.open .search-select-dropdown { display: block; }

.search-select-search {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.75rem;
    border-bottom: 1px solid #eee;
}
.search-select-search svg { color: #999; flex-shrink: 0; }
.search-select-search input {
    border: none; outline: none; width: 100%; font-size: 0.85rem; padding: 0.15rem 0;
    background: transparent;
}
.search-select-options {
    list-style: none; margin: 0; padding: 0; max-height: 220px; overflow-y: auto;
}
.search-select-options li {
    padding: 0.55rem 0.75rem; font-size: 0.85rem; cursor: pointer; transition: background 0.1s;
}
.search-select-options li:hover { background: #f0f0f0; }
.search-select-options li.active { background: #6c8c7c; color: #fff; }
.search-select-options li.hidden { display: none; }
.search-select-options li.search-select-empty {
    color: #999; cursor: default; font-style: italic; text-align: center; padding: 1rem;
}
.search-select-options li.search-select-empty:hover { background: transparent; }

/* Dashboard table styling */
.dashboard-table { border-collapse: separate; border-spacing: 0; }
.dashboard-table thead th {
    font-size: 0.72rem; font-weight: 600; letter-spacing: 0.03em;
    color: var(--text-secondary); border-bottom: 2px solid #dee2e6;
    padding: 0.5rem 0.6rem; white-space: nowrap;
    position: sticky; top: 0; z-index: 1; background: #fff;
}
.dashboard-table tbody td {
    padding: 0.45rem 0.6rem; font-size: 0.82rem; border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.dashboard-table tbody tr:hover { background: #f8f9fa; }
.dashboard-table .total-row td {
    border-top: 2px solid #dee2e6; border-bottom: none;
    padding: 0.55rem 0.6rem; background: #f8f9fa;
    position: sticky; bottom: 0; z-index: 1;
}

/* Color dot indicator */
.color-dot {
    display: inline-block; width: 8px; height: 8px; border-radius: 50%;
    margin-right: 6px; vertical-align: middle; flex-shrink: 0;
}

/* Mini progress bar */
.mini-bar {
    width: 100%; height: 6px; background: #eee; border-radius: 3px; overflow: hidden;
}
.mini-bar-fill {
    height: 100%; border-radius: 3px; transition: width 0.4s ease;
}
</style>
@endpush
