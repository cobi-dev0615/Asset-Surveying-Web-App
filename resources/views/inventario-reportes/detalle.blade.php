@extends('layouts.app')
@section('title', 'Reporte a Detalle')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Reporte a detalle
    </h2>
    <div class="page-header-actions">
        <a href="{{ route('inventario-reportes.detalle.export', request()->query()) }}" class="btn btn-catalog-action" style="background:#4CAF50;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar Excel
        </a>
    </div>
</div>

@include('inventario-reportes._toolbar', ['routeName' => 'inventario-reportes.detalle'])

<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper table-scroll-inv">
            <table class="tbl-inv">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => 'cantidad', 'label' => 'Cantidad'],
                                ['key' => 'codigo_1', 'label' => 'Codigo 1'],
                                ['key' => 'codigo_2', 'label' => 'SKU'],
                                ['key' => 'codigo_3', 'label' => 'Codigo 3'],
                                ['key' => null, 'label' => 'Descripcion'],
                                ['key' => 'n_conteo', 'label' => 'Conteo'],
                                ['key' => null, 'label' => 'Precio Venta'],
                                ['key' => null, 'label' => 'Importe'],
                                ['key' => null, 'label' => 'Unidad'],
                                ['key' => null, 'label' => 'Lote'],
                                ['key' => null, 'label' => 'Num. Serie'],
                                ['key' => 'nombre_almacen', 'label' => 'Almacen'],
                                ['key' => 'ubicacion_1', 'label' => 'Ubicacion'],
                                ['key' => 'nombre_usuario', 'label' => 'Usuario'],
                                ['key' => 'fecha_captura', 'label' => 'Fecha'],
                                ['key' => 'hora_captura', 'label' => 'Hora'],
                                ['key' => null, 'label' => 'Forzado'],
                            ];
                        @endphp
                        @foreach($columns as $col)
                        <th class="sortable-th {{ $sort === $col['key'] ? 'sort-active' : '' }}">
                            @if($col['key'])
                                @php $nextDir = ($sort === $col['key'] && $dir === 'asc') ? 'desc' : 'asc'; @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort' => $col['key'], 'dir' => $nextDir, 'page' => 1]) }}" class="sort-link">
                                    {{ $col['label'] }}
                                    <span class="sort-arrows">
                                        <svg class="sort-asc {{ $sort === $col['key'] && $dir === 'asc' ? 'active' : '' }}" viewBox="0 0 10 6"><path d="M5 0L10 6H0z"/></svg>
                                        <svg class="sort-desc {{ $sort === $col['key'] && $dir === 'desc' ? 'active' : '' }}" viewBox="0 0 10 6"><path d="M5 6L0 0h10z"/></svg>
                                    </span>
                                </a>
                            @else
                                <span class="sort-link" style="cursor:default;">{{ $col['label'] }}</span>
                            @endif
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $reg)
                    <tr>
                        <td class="text-danger font-weight-bold text-right">{{ number_format($reg->cantidad, 2) }}</td>
                        <td>{{ $reg->codigo_1 }}</td>
                        <td>{{ $reg->codigo_2 }}</td>
                        <td>{{ $reg->codigo_3 }}</td>
                        <td class="cell-truncate">{{ $reg->producto_descripcion }}</td>
                        <td class="text-center">{{ $reg->n_conteo }}</td>
                        <td class="text-right">{{ number_format($reg->producto_precio_venta, 2) }}</td>
                        <td class="text-right font-weight-bold" style="color:var(--primary);">{{ number_format($reg->cantidad * $reg->producto_precio_venta, 2) }}</td>
                        <td>{{ $reg->unidad_medida }}</td>
                        <td>{{ $reg->lote }}</td>
                        <td>{{ $reg->n_serie }}</td>
                        <td>{{ $reg->nombre_almacen }}</td>
                        <td>{{ $reg->ubicacion_1 }}</td>
                        <td>{{ $reg->nombre_usuario }}</td>
                        <td style="white-space:nowrap;">{{ $reg->fecha_captura ? $reg->fecha_captura->format('Y-m-d') : '' }}</td>
                        <td style="white-space:nowrap;">{{ $reg->hora_captura }}</td>
                        <td class="text-center">
                            @if($reg->forzado)
                                <span style="color:#FF9800; font-weight:700;">Si</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" class="table-empty"><div>No se encontraron registros de inventario</div></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('inventario-reportes._footer', ['registros' => $registros, 'totales' => $totales])
</div>

@include('inventario-reportes._styles')
@endsection
