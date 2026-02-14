@extends('layouts.app')
@section('title', 'Órdenes de Transferencia')

@section('content')
<div class="page-header">
    <h2>Órdenes de Transferencia</h2>
    <div class="page-header-actions">
        <a href="{{ route('ordenes-entrada.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nueva Orden
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span>{{ number_format($ordenes->total()) }} órdenes</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="toolbar" style="padding:1rem 1.5rem;">
            <div class="toolbar-left">
                <form method="GET" style="display:flex; gap:0.5rem; flex:1; flex-wrap:wrap;">
                    <select name="estatus_id" class="form-control" style="width:auto; min-width:180px;">
                        <option value="">Todos los estatus</option>
                        @foreach($estatuses as $est)
                            <option value="{{ $est->id }}" {{ request('estatus_id') == $est->id ? 'selected' : '' }}>{{ $est->nombre_estatus }}</option>
                        @endforeach
                    </select>
                    <div class="search-box" style="flex:1; max-width:300px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por # orden o motivo..." value="{{ request('buscar') }}">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request()->hasAny(['buscar', 'estatus_id']))
                        <a href="{{ route('ordenes-entrada.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th># Orden</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Motivo</th>
                        <th style="text-align:center;">Activos</th>
                        <th>Estatus</th>
                        <th>Solicitado por</th>
                        <th>Fecha</th>
                        <th style="width:80px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $orden)
                    <tr>
                        <td><span class="badge badge-gray">{{ $orden->n_orden }}</span></td>
                        <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $orden->inventarioOrigen->sucursal->nombre ?? $orden->inventarioOrigen->nombre ?? 'Sesión #'.$orden->inventario_origen_id }}
                        </td>
                        <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $orden->inventarioDestino->sucursal->nombre ?? $orden->inventarioDestino->nombre ?? 'Sesión #'.$orden->inventario_destino_id }}
                        </td>
                        <td>{{ $orden->motivo }}</td>
                        <td style="text-align:center;">{{ $orden->detalles_count }}</td>
                        <td>
                            @php
                                $color = match($orden->estatus_id) {
                                    1 => 'warning',
                                    2 => 'info',
                                    3 => 'danger',
                                    4 => 'success',
                                    5 => 'gray',
                                    default => 'gray',
                                };
                            @endphp
                            <span class="badge badge-{{ $color }}">{{ $orden->estatus->nombre_estatus ?? '-' }}</span>
                        </td>
                        <td>{{ $orden->usuario->nombres ?? '-' }}</td>
                        <td>{{ $orden->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('ordenes-entrada.show', $orden) }}" class="btn btn-sm btn-outline" title="Ver detalle">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            <div>No se encontraron órdenes de transferencia</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ordenes->hasPages())
    <div class="card-footer">
        {{ $ordenes->links() }}
    </div>
    @endif
</div>
@endsection
