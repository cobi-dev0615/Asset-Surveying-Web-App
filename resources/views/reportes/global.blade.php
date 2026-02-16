@extends('layouts.app')
@section('title', 'Reporte Global')

@section('content')
<div class="page-header">
    <h2>Reporte Global</h2>
</div>

<div class="stats-grid" style="margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Total Sesiones</span>
            <span class="stat-value">{{ number_format($totalSesiones) }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8f5e9; color:var(--success);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Finalizadas</span>
            <span class="stat-value">{{ number_format($finalizadas) }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#e3f2fd; color:var(--info);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Total Registros</span>
            <span class="stat-value">{{ number_format($totalRegistros) }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fce4ec; color:var(--danger);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">No Encontrados</span>
            <span class="stat-value">{{ number_format($totalNoEncontrados) }}</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span>Sesiones de Activo Fijo</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="toolbar" style="padding:1rem 1.5rem;">
            <div class="toolbar-left">
                <form method="GET" style="display:flex; gap:0.5rem; flex:1; flex-wrap:wrap;">
                    <select name="empresa_id" class="form-control" style="width:auto; min-width:180px;">
                        <option value="">Todas las empresas</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->id }}" {{ request('empresa_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="status_id" class="form-control" style="width:auto; min-width:160px;">
                        <option value="">Todos los estados</option>
                        <option value="1" {{ request('status_id') == 1 ? 'selected' : '' }}>PENDIENTE POR INICIAR</option>
                        <option value="2" {{ request('status_id') == 2 ? 'selected' : '' }}>INICIADO</option>
                        <option value="3" {{ request('status_id') == 3 ? 'selected' : '' }}>FINALIZADO</option>
                    </select>
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request()->hasAny(['empresa_id', 'status_id']))
                        <a href="{{ route('reportes.global') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
            <div class="toolbar-right">
                <a href="{{ route('reportes.global.export', request()->query()) }}" class="btn btn-success btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Exportar Excel
                </a>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Empresa</th>
                        <th>Sucursal</th>
                        <th>Creador</th>
                        <th>Registros</th>
                        <th>No Encontrados</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sesiones as $sesion)
                    <tr>
                        <td>{{ $sesion->id }}</td>
                        <td>{{ $sesion->empresa->nombre ?? '-' }}</td>
                        <td>{{ $sesion->sucursal->nombre ?? '-' }}</td>
                        <td>{{ $sesion->usuario->nombres ?? '-' }}</td>
                        <td><span class="badge badge-primary">{{ number_format($sesion->registros_count) }}</span></td>
                        <td><span class="badge badge-danger">{{ $sesion->no_encontrados_count }}</span></td>
                        <td>
                            @php
                                $statusClass = match($sesion->status_id) {
                                    1 => 'badge-warning',
                                    2 => 'badge-info',
                                    3 => 'badge-success',
                                    default => 'badge-gray'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $sesion->status->nombre ?? 'N/A' }}</span>
                        </td>
                        <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $sesion->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                            <div>No se encontraron sesiones</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sesiones->hasPages())
    <div class="card-footer">
        {{ $sesiones->links() }}
    </div>
    @endif
</div>
@endsection
