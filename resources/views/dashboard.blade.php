@extends('layouts.app')
@section('title', 'Tablero')

@section('content')
<div class="subheader" style="margin-bottom:1.25rem;">
    <h1 style="font-size:1.15rem; font-weight:400; color:var(--text);" id="saludo"></h1>
</div>

{{-- Session selector --}}
@if($sesiones->count())
<div style="margin-bottom:1.25rem; display:flex; align-items:center; gap:0.75rem;">
    <form method="GET" style="display:flex; align-items:center; gap:0.5rem;">
        <label style="font-size:0.82rem; font-weight:500; white-space:nowrap;">Sesión de inventario:</label>
        <select name="sesion_id" id="sesionSelect" class="form-control" style="width:auto; min-width:300px;" onchange="this.form.submit()">
            @foreach($sesiones as $ses)
                <option value="{{ $ses->id }}" {{ $sesionId == $ses->id ? 'selected' : '' }}>
                    #{{ $ses->id }} - {{ $ses->empresa->nombre ?? '' }} / {{ $ses->sucursal->nombre ?? '' }}
                </option>
            @endforeach
        </select>
    </form>
</div>
@endif

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
                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center;">
                        <div style="font-size:0.72rem; color:var(--text-secondary);">Porcentaje</div>
                        <div style="font-size:0.72rem; color:var(--text-secondary);">por área</div>
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
        <div class="table-wrapper">
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
                legend: { position: 'bottom', labels: { padding: 12, font: { size: 10 }, usePointStyle: true } },
                tooltip: { callbacks: { label: function(ctx) {
                    var total = ctx.dataset.data.reduce(function(a,b){return a+b;}, 0);
                    return ctx.label + ': ' + ctx.parsed + ' (' + ((ctx.parsed/total)*100).toFixed(1) + '%)';
                }}}
            }
        }
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

document.addEventListener('DOMContentLoaded', function() {
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

/* Dashboard table styling */
.dashboard-table { border-collapse: separate; border-spacing: 0; }
.dashboard-table thead th {
    font-size: 0.72rem; font-weight: 600; letter-spacing: 0.03em;
    color: var(--text-secondary); border-bottom: 2px solid #dee2e6;
    padding: 0.5rem 0.6rem; white-space: nowrap;
}
.dashboard-table tbody td {
    padding: 0.45rem 0.6rem; font-size: 0.82rem; border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.dashboard-table tbody tr:hover { background: #f8f9fa; }
.dashboard-table .total-row td {
    border-top: 2px solid #dee2e6; border-bottom: none;
    padding: 0.55rem 0.6rem; background: #f8f9fa;
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
