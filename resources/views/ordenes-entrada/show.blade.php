@extends('layouts.app')
@section('title', 'Orden de Transferencia #' . $orden->n_orden)

@section('content')
<div class="page-header">
    <h2>Orden de Transferencia #{{ $orden->n_orden }}</h2>
    <div class="page-header-actions">
        <a href="{{ route('ordenes-entrada.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

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

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
    {{-- Datos de la Orden --}}
    <div class="card">
        <div class="card-header"><span>Datos de la Orden</span></div>
        <div class="card-body">
            <table style="width:100%;">
                <tr><td style="font-weight:600; width:40%; padding:0.4rem 0;">Estatus</td><td><span class="badge badge-{{ $color }}">{{ $orden->estatus->nombre_estatus ?? '-' }}</span></td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;"># Orden</td><td>{{ $orden->n_orden }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Motivo</td><td>{{ $orden->motivo }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Comentarios</td><td>{{ $orden->comentarios ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Fecha de Creación</td><td>{{ $orden->created_at?->format('d/m/Y H:i') }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Solicitado por</td><td>{{ $orden->usuario->nombres ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Origen y Destino --}}
    <div class="card">
        <div class="card-header"><span>Origen y Destino</span></div>
        <div class="card-body">
            <table style="width:100%;">
                <tr><td style="font-weight:600; width:40%; padding:0.4rem 0;">Origen</td><td>{{ $orden->inventarioOrigen->empresa->nombre ?? '' }} - {{ $orden->inventarioOrigen->sucursal->nombre ?? $orden->inventarioOrigen->nombre ?? 'Sesión #'.$orden->inventario_origen_id }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Destino</td><td>{{ $orden->inventarioDestino->empresa->nombre ?? '' }} - {{ $orden->inventarioDestino->sucursal->nombre ?? $orden->inventarioDestino->nombre ?? 'Sesión #'.$orden->inventario_destino_id }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Autorizado por</td><td>{{ $orden->autorizador->nombres ?? '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Surtido por</td><td>{{ $orden->surtidor->nombres ?? '-' }}</td></tr>
                @if($orden->fecha_hora_surtido)
                <tr><td style="font-weight:600; padding:0.4rem 0;">Fecha Surtido</td><td>{{ $orden->fecha_hora_surtido->format('d/m/Y H:i') }}</td></tr>
                @endif
                @if($orden->rechazador)
                <tr><td style="font-weight:600; padding:0.4rem 0;">Rechazado por</td><td>{{ $orden->rechazador->nombres }}</td></tr>
                @endif
                @if($orden->cancelador)
                <tr><td style="font-weight:600; padding:0.4rem 0;">Cancelado por</td><td>{{ $orden->cancelador->nombres }}</td></tr>
                @endif
                @if($orden->fecha_hora_cancelacion)
                <tr><td style="font-weight:600; padding:0.4rem 0;">Fecha Cancelación</td><td>{{ $orden->fecha_hora_cancelacion->format('d/m/Y H:i') }}</td></tr>
                @endif
            </table>

            {{-- Action buttons --}}
            @if(!in_array($orden->estatus_id, [3, 4, 5]))
            <div style="display:flex; gap:0.5rem; margin-top:1.5rem; flex-wrap:wrap;">
                @if($orden->estatus_id === 1)
                <form method="POST" action="{{ route('ordenes-entrada.autorizar', $orden) }}" onsubmit="return confirm('¿Autorizar esta orden?')">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">Autorizar</button>
                </form>
                @endif
                @if($orden->estatus_id === 2)
                <form method="POST" action="{{ route('ordenes-entrada.surtir', $orden) }}" onsubmit="return confirm('¿Marcar como surtida? Los activos serán marcados como traspasados.')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">Surtir</button>
                </form>
                @endif
                <form method="POST" action="{{ route('ordenes-entrada.rechazar', $orden) }}" onsubmit="return confirm('¿Rechazar esta orden?')">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background:var(--danger); color:#fff;">Rechazar</button>
                </form>
                <form method="POST" action="{{ route('ordenes-entrada.cancelar', $orden) }}" onsubmit="return confirm('¿Cancelar esta orden?')">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Cancelar</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Detalle de Activos --}}
<div class="card">
    <div class="card-header">
        <span>Activos en la Orden ({{ $orden->detalles->count() }})</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID Registro</th>
                        <th>Código 1</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Ubicación</th>
                        <th>Traspasado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orden->detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->registro_id }}</td>
                        <td><span class="badge badge-gray">{{ $detalle->registro->codigo_1 ?? '-' }}</span></td>
                        <td style="max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $detalle->registro->descripcion ?? '-' }}</td>
                        <td>{{ $detalle->registro->categoria ?? '-' }}</td>
                        <td>{{ $detalle->registro->nombre_almacen ?? $detalle->registro->ubicacion_1 ?? '-' }}</td>
                        <td>
                            @if($detalle->registro?->traspasado)
                                <span class="badge badge-warning">Sí</span>
                            @else
                                <span style="color:var(--text-light);">No</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            <div>No hay activos en esta orden</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
