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

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value">{{ $inventario->registros_count }}</div>
            <div class="stat-label">Registros</div>
        </div>
        <div class="stat-icon stat-icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value">{{ $inventario->detalles_count }}</div>
            <div class="stat-label">Detalles</div>
        </div>
        <div class="stat-icon stat-icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
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

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header">Información General</div>
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
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Fecha Creación</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Auditor</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->auditor ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Gerente</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->gerente ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Subgerente</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->subgerente ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Comentarios</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $inventario->comentarios ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Registros Sincronizados</div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto ID</th>
                        <th>Cantidad</th>
                        <th>Ubicación</th>
                        <th>Almacén</th>
                        <th>Lote</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventario->registros as $reg)
                    <tr>
                        <td><span class="badge badge-gray">{{ $reg->codigo_1 }}</span></td>
                        <td>{{ $reg->producto_id ?? '-' }}</td>
                        <td style="font-weight:500;">{{ $reg->cantidad }}</td>
                        <td>{{ $reg->ubicacion_1 ?? '-' }}</td>
                        <td>{{ $reg->nombre_almacen ?? '-' }}</td>
                        <td>{{ $reg->lote ?? '-' }}</td>
                        <td>{{ $reg->usuario->nombres ?? '-' }}</td>
                        <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $reg->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="table-empty">
                            <div>No hay registros sincronizados aún</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
