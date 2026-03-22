@extends('layouts.app')
@section('title', 'Inventario: ' . $inventario->nombre)

@section('content')
<div class="page-header">
    <h2>{{ $inventario->nombre }}</h2>
    <div class="page-header-actions">
        <a href="{{ route('inventarios.edit', $inventario) }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Editar
        </a>
        <a href="{{ route('inventarios.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

{{-- Stat Cards --}}
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value">{{ number_format($stats->total_capturas ?? 0) }}</div>
            <div class="stat-label">Capturas</div>
        </div>
        <div class="stat-icon stat-icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value">{{ number_format($stats->productos_unicos ?? 0) }}</div>
            <div class="stat-label">Productos</div>
        </div>
        <div class="stat-icon stat-icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value">{{ number_format($stats->conteo_total ?? 0, 0) }}</div>
            <div class="stat-label">Conteo Total</div>
        </div>
        <div class="stat-icon stat-icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value">{{ number_format($stats->almacenes ?? 0) }}</div>
            <div class="stat-label">Almacenes</div>
        </div>
        <div class="stat-icon stat-icon-orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14m6-14v14m6-14v14m6-14v14M6 3h12l3 4H3l3-4z"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value">{{ number_format($stats->usuarios_activos ?? 0) }}</div>
            <div class="stat-label">Usuarios</div>
        </div>
        <div class="stat-icon stat-icon-cyan">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            @php
                $statusClass = match($inventario->status_id) {
                    1 => 'badge-warning',
                    2 => 'badge-info',
                    3 => 'badge-success',
                    default => 'badge-gray'
                };
            @endphp
            <div><span class="badge {{ $statusClass }}" style="font-size:0.85rem;">{{ $inventario->status->nombre ?? 'N/A' }}</span></div>
            <div class="stat-label" style="margin-top:0.5rem;">Estado</div>
        </div>
    </div>
</div>

{{-- Info General --}}
<div class="card" style="margin-bottom:1rem;">
    <div class="card-header">Informacion General</div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Empresa</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->empresa->nombre ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Sucursal</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->sucursal->nombre ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Creado por</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->usuario->nombres ?? $inventario->nombre_usuario ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Fecha Creacion</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->created_at->format('d/m/Y H:i') }}</div>
            </div>
            @if($stats->primera_captura)
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Primera Captura</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $stats->primera_captura }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Ultima Captura</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $stats->ultima_captura }}</div>
            </div>
            @endif
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Auditor</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->auditor ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Comentarios</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->comentarios ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem;">
    {{-- Per-user breakdown --}}
    <div class="card">
        <div class="card-header">Capturas por Usuario</div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th style="text-align:right;">Capturas</th>
                            <th style="text-align:right;">Cantidad</th>
                            <th>Primera</th>
                            <th>Ultima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($porUsuario as $u)
                        <tr>
                            <td style="font-weight:500;">{{ $u->nombre_usuario ?? 'Sin nombre' }}</td>
                            <td style="text-align:right; font-weight:600; color:var(--primary);">{{ number_format($u->capturas) }}</td>
                            <td style="text-align:right;">{{ number_format($u->cantidad_total, 0) }}</td>
                            <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $u->primera }}</td>
                            <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $u->ultima }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="table-empty"><div>Sin capturas</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Per-warehouse breakdown --}}
    <div class="card">
        <div class="card-header">Capturas por Almacen</div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper" style="max-height:300px; overflow-y:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Almacen</th>
                            <th style="text-align:right;">Capturas</th>
                            <th style="text-align:right;">Cantidad</th>
                            <th style="text-align:right;">Productos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($porAlmacen as $a)
                        <tr>
                            <td style="font-weight:500;">{{ $a->nombre_almacen ?? 'Sin nombre' }}</td>
                            <td style="text-align:right; font-weight:600; color:var(--primary);">{{ number_format($a->capturas) }}</td>
                            <td style="text-align:right;">{{ number_format($a->cantidad_total, 0) }}</td>
                            <td style="text-align:right;">{{ number_format($a->productos) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="table-empty"><div>Sin capturas</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Recent activity --}}
<div class="card">
    <div class="card-header">Actividad Reciente (ultimas 20 capturas)</div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper" style="max-height:400px; overflow-y:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Cantidad</th>
                        <th>Almacen</th>
                        <th>Ubicacion</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Forzado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actividad as $reg)
                    <tr>
                        <td><span class="badge badge-gray">{{ $reg->codigo_1 }}</span></td>
                        <td style="font-weight:500;">{{ $reg->cantidad }}</td>
                        <td>{{ $reg->nombre_almacen ?? '-' }}</td>
                        <td>{{ $reg->ubicacion_1 ?? '-' }}</td>
                        <td>{{ $reg->nombre_usuario ?? '-' }}</td>
                        <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $reg->fecha_captura }}</td>
                        <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $reg->hora_captura }}</td>
                        <td>
                            @if($reg->forzado)
                                <span style="color:#FF9800; font-weight:700; font-size:0.75rem;">FORZADO</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="table-empty">
                            <div>No hay registros capturados aun</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
    .stat-icon-purple { background: rgba(156, 39, 176, 0.1); color: #9C27B0; }
    .stat-icon-orange { background: rgba(255, 152, 0, 0.1); color: #FF9800; }
    .stat-icon-cyan { background: rgba(0, 188, 212, 0.1); color: #00BCD4; }
</style>
@endpush
@endsection
