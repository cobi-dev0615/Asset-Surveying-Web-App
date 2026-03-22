@extends('layouts.app')
@section('title', 'Detalle de Activo Fijo')

@section('content')
<div class="page-header">
    <h2>Detalle de Activo Fijo</h2>
    <div class="page-header-actions">
        <a href="{{ route('activo-fijo-productos.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
    {{-- Info General --}}
    <div class="card">
        <div class="card-header"><span>Información General</span></div>
        <div class="card-body">
            <table style="width:100%;">
                <tr><td style="font-weight:600; width:40%; padding:0.4rem 0;">Código 1</td><td>{{ $producto->codigo_1 ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Código 2 (Tag)</td><td>{{ $producto->codigo_2 ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Código 3</td><td>{{ $producto->codigo_3 ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Tag RFID</td><td>{{ $producto->tag_rfid ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Descripción</td><td>{{ $producto->descripcion }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Categoría</td><td>{{ $producto->categoria_2 ?: $producto->categoria_1 ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Marca</td><td>{{ $producto->marca ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Modelo</td><td>{{ $producto->modelo ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Tipo de Activo</td><td>{{ $producto->tipo_activo ?: '-' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Info Técnica --}}
    <div class="card">
        <div class="card-header"><span>Información Técnica</span></div>
        <div class="card-body">
            <table style="width:100%;">
                <tr><td style="font-weight:600; width:40%; padding:0.4rem 0;">No. Serie</td><td>{{ $producto->n_serie ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Serie Anterior</td><td>{{ $producto->n_serie_anterior ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Serie Nuevo</td><td>{{ $producto->n_serie_nuevo ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Empresa</td><td>{{ $producto->empresa->nombre ?? '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Sucursal</td><td>{{ $producto->inventario->sucursal->nombre ?? $producto->subsidiaria ?: '-' }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Cantidad Teórica</td><td>{{ $producto->cantidad_teorica }}</td></tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Inicio de Servicio</td><td>{{ $producto->fecha_inicio_servicio ?: '-' }}</td></tr>
                <tr>
                    <td style="font-weight:600; padding:0.4rem 0;">Estado</td>
                    <td>
                        @if($producto->no_encontrado)
                            <span class="badge badge-danger">No encontrado</span>
                        @elseif($producto->traspasado)
                            <span class="badge badge-warning">Traspasado</span>
                        @elseif($producto->forzado)
                            <span class="badge badge-info">Forzado</span>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                    </td>
                </tr>
                <tr><td style="font-weight:600; padding:0.4rem 0;">Observaciones</td><td>{{ $producto->observaciones ?: '-' }}</td></tr>
            </table>
        </div>
    </div>
</div>

{{-- Imágenes del Activo --}}
@if($producto->imagen1 || $producto->imagen2 || $producto->imagen3)
<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><span>Imágenes del Activo</span></div>
    <div class="card-body" style="display:flex; gap:1rem; flex-wrap:wrap; padding:1rem 1.5rem;">
        @foreach(['imagen1','imagen2','imagen3'] as $img)
            @if($producto->$img)
                <a href="{{ asset('storage/' . $producto->$img) }}" target="_blank" style="display:block;">
                    <img src="{{ asset('storage/' . $producto->$img) }}" alt="{{ $img }}"
                         style="max-width:280px; max-height:220px; object-fit:cover; border-radius:6px; border:1px solid var(--border); cursor:pointer;">
                </a>
            @endif
        @endforeach
    </div>
</div>
@endif

{{-- Historial de Escaneos --}}
<div class="card">
    <div class="card-header">
        <span>Historial de Escaneos ({{ $registros->count() }})</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sesión</th>
                        <th>Usuario</th>
                        <th>Ubicación</th>
                        <th>Categoría</th>
                        <th>Traspasado</th>
                        <th>Forzado</th>
                        <th>Imágenes</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $reg)
                    <tr>
                        <td>{{ $reg->id }}</td>
                        <td>{{ $reg->inventario_id }}</td>
                        <td>{{ $reg->usuario->nombres ?? '-' }}</td>
                        <td>{{ $reg->nombre_almacen ?: $reg->ubicacion_1 ?: '-' }}</td>
                        <td>{{ $reg->categoria ?: '-' }}</td>
                        <td>
                            @if($reg->traspasado)
                                <span class="badge badge-warning">Sí</span>
                            @else
                                <span style="color:var(--text-light);">No</span>
                            @endif
                        </td>
                        <td>
                            @if($reg->forzado)
                                <span class="badge badge-info">Sí</span>
                            @else
                                <span style="color:var(--text-light);">No</span>
                            @endif
                        </td>
                        <td>
                            @php $hasImgs = $reg->imagen1 || $reg->imagen2 || $reg->imagen3; @endphp
                            @if($hasImgs)
                                <div style="display:flex; gap:0.25rem;">
                                    @foreach(['imagen1','imagen2','imagen3'] as $img)
                                        @if($reg->$img)
                                            <a href="{{ asset('storage/' . $reg->$img) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $reg->$img) }}" alt="{{ $img }}"
                                                     style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid var(--border);">
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <span style="color:var(--text-light);">-</span>
                            @endif
                        </td>
                        <td>{{ $reg->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="table-empty">
                            <div>No hay registros de escaneo para este activo</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
