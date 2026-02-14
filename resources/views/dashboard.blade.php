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
        <select name="sesion_id" class="form-control" style="width:auto; min-width:300px;" onchange="this.form.submit()">
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
            <button class="btn btn-sm btn-primary" onclick="location.reload()">
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
                                <td style="text-align:center;"><span style="font-size:1.5rem; font-weight:700;">{{ number_format($avanceGeneral['catalogo']) }}</span></td>
                                <td style="text-align:center;"><span style="font-size:1.5rem; font-weight:700; color:var(--success);">{{ number_format($avanceGeneral['encontrados']) }}</span></td>
                                <td style="text-align:center;"><span style="font-size:1.5rem; font-weight:700; color:var(--danger);">{{ number_format($avanceGeneral['no_encontrados']) }}</span></td>
                                <td style="text-align:center;"><span style="font-size:1.5rem; font-weight:700; color:var(--info);">{{ number_format($avanceGeneral['pendientes']) }}</span></td>
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
            <button class="btn btn-sm btn-primary" onclick="location.reload()">
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
                    <table id="tablaAvanceArea">
                        <thead>
                            <tr>
                                <th>DEPARTAMENTO / ALMACÉN</th>
                                <th>ACTIVOS CONTADOS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalArea = 0; @endphp
                            @forelse($avancePorArea as $area)
                            <tr>
                                <td>{{ $area['area'] }}</td>
                                <td style="text-align:center;">{{ number_format($area['cantidad']) }}</td>
                            </tr>
                            @php $totalArea += $area['cantidad']; @endphp
                            @empty
                            <tr>
                                <td colspan="2" style="text-align:center; color:var(--text-secondary); padding:1.5rem;">Sin datos</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($avancePorArea) > 0)
                        <tfoot>
                            <tr style="background:#f0f0f0; font-weight:700;">
                                <td>TOTAL</td>
                                <td style="text-align:center; color:var(--primary);">{{ number_format($totalArea) }}</td>
                            </tr>
                        </tfoot>
                        @endif
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
            <button class="btn btn-sm btn-primary" onclick="location.reload()">
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
            <table id="tablaAvanceCategoria">
                <thead>
                    <tr>
                        <th>CATEGORÍA</th>
                        <th>CONTADOS</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalCat = 0; @endphp
                    @forelse($avancePorCategoria as $cat)
                    <tr>
                        <td>{{ $cat['categoria'] }}</td>
                        <td style="text-align:center;">{{ number_format($cat['cantidad']) }}</td>
                    </tr>
                    @php $totalCat += $cat['cantidad']; @endphp
                    @empty
                    <tr>
                        <td colspan="2" style="text-align:center; color:var(--text-secondary); padding:1.5rem;">Sin datos</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($avancePorCategoria) > 0)
                <tfoot>
                    <tr style="background:#f0f0f0; font-weight:700;">
                        <td>TOTAL</td>
                        <td style="text-align:center; color:var(--primary);">{{ number_format($totalCat) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Greeting based on time of day ──
    const hora = new Date().getHours();
    let saludo = '';
    if (hora < 12) saludo = '&#9728; Buen día';
    else if (hora <= 19) saludo = '&#9728;&#65039; Buena tarde';
    else saludo = '&#127769; Buena noche';

    const fecha = new Date().toLocaleDateString('es-MX', { day:'2-digit', month:'2-digit', year:'numeric' });
    document.getElementById('saludo').innerHTML = saludo + ' {{ $user->nombres }} <small style="color:var(--text-secondary); font-size:0.82rem;">Estos son los reportes generales del inventario al día de hoy ' + fecha + '.</small>';

    const colores = ['#ff4444','#00C851','#4285F4','#33b5e5','#ffbb33','#aa66cc','#2BBBAD','#2E2E2E','#3F729B','#c51162'];

    // ── Chart 1: Avance General (Donut) ──
    const ctx1 = document.getElementById('chartAvanceGeneral');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['ACTIVOS PENDIENTES POR CONTAR', 'ENCONTRADOS', 'NO ENCONTRADOS'],
                datasets: [{
                    data: [{{ $avanceGeneral['pct_pendientes'] }}, {{ $avanceGeneral['pct_encontrados'] }}, {{ $avanceGeneral['pct_no_encontrados'] }}],
                    backgroundColor: ['#0d47a1', '#007E33', '#9933CC'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 15, font: { size: 11 }, usePointStyle: true } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ctx.label + ': ' + ctx.parsed.toFixed(1) + '%'; }
                        }
                    }
                }
            }
        });
    }

    // ── Chart 2: Avance por Área (Donut) ──
    const areaLabels = @json(array_column($avancePorArea, 'area'));
    const areaValues = @json(array_column($avancePorArea, 'cantidad'));
    const ctx2 = document.getElementById('chartAvanceArea');
    if (ctx2 && areaLabels.length > 0) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: areaLabels,
                datasets: [{
                    data: areaValues,
                    backgroundColor: colores.slice(0, areaLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, font: { size: 10 }, usePointStyle: true } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                const pct = ((ctx.parsed / total) * 100).toFixed(1);
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // ── Chart 3: Avance por Categoría (Bar) ──
    const catLabels = @json(array_column($avancePorCategoria, 'categoria'));
    const catValues = @json(array_column($avancePorCategoria, 'cantidad'));
    const ctx3 = document.getElementById('chartAvanceCategoria');
    if (ctx3 && catLabels.length > 0) {
        const catColors = catLabels.map((_, i) => colores[i % colores.length]);
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Activos contados',
                    data: catValues,
                    backgroundColor: catColors,
                    borderRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                    y: { beginAtZero: true, grid: { color: '#eee' } }
                }
            }
        });
    }
});
</script>
@endpush
