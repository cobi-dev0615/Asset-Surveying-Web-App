@extends('layouts.app')
@section('title', 'Sesión de Activo Fijo #' . $activo_fijo->id)

@section('content')
<div class="page-header">
    <h2>Sesión de Activo Fijo #{{ $activo_fijo->id }}</h2>
    <div class="page-header-actions">
        <a href="{{ route('activo-fijo.edit', $activo_fijo) }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Editar
        </a>
        <a href="{{ route('activo-fijo.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value">{{ number_format($activo_fijo->registros_count) }}</div>
            <div class="stat-label">Activos Registrados</div>
        </div>
        <div class="stat-icon stat-icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value">{{ $activo_fijo->no_encontrados_count }}</div>
            <div class="stat-label">No Encontrados</div>
        </div>
        <div class="stat-icon stat-icon-red">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            @php
                $statusClass = match($activo_fijo->status_id) {
                    1 => 'badge-warning',
                    2 => 'badge-info',
                    3 => 'badge-success',
                    default => 'badge-gray'
                };
            @endphp
            <div><span class="badge {{ $statusClass }}" style="font-size:0.85rem;">{{ $activo_fijo->status->nombre ?? 'N/A' }}</span></div>
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
                <div style="font-weight:500; margin-top:0.2rem;">{{ $activo_fijo->empresa->nombre ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Sucursal</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $activo_fijo->sucursal->nombre ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Creado por</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $activo_fijo->usuario->nombres ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Fecha Creación</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $activo_fijo->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Inicio Conteo</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $activo_fijo->inicio_conteo ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Fin Conteo</div>
                <div style="font-weight:500; margin-top:0.2rem;">{{ $activo_fijo->fin_conteo ?? '-' }}</div>
            </div>
        </div>
        @if($activo_fijo->comentarios)
        <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border);">
            <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase;">Comentarios</div>
            <div style="margin-top:0.2rem;">{{ $activo_fijo->comentarios }}</div>
        </div>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header">Registros de Activos</div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Código 1</th>
                        <th>Descripción</th>
                        <th>N. Serie</th>
                        <th>Tag RFID</th>
                        <th>Ubicación</th>
                        <th>Categoría</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activo_fijo->registros as $reg)
                    <tr>
                        <td><span class="badge badge-gray">{{ $reg->codigo_1 }}</span></td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $reg->descripcion ?? '-' }}</td>
                        <td>{{ $reg->n_serie ?? '-' }}</td>
                        <td>{{ $reg->tag_rfid ?? '-' }}</td>
                        <td>{{ $reg->ubicacion_1 ?? '-' }}</td>
                        <td>{{ $reg->categoria ?? '-' }}</td>
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

@if($activo_fijo->noEncontrados->count())
<div class="card">
    <div class="card-header">Activos No Encontrados</div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID Activo</th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activo_fijo->noEncontrados as $ne)
                    <tr>
                        <td style="font-weight:500;">{{ $ne->activo }}</td>
                        <td>{{ $ne->latitud }}</td>
                        <td>{{ $ne->longitud }}</td>
                        <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $ne->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
