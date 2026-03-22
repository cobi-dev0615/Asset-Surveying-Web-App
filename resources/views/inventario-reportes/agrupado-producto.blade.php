@extends('layouts.app')
@section('title', 'Agrupado por Producto')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Agrupado por producto
    </h2>
    <div class="page-header-actions">
        <a href="{{ route('inventario-reportes.agrupado-producto.export', request()->query()) }}" class="btn btn-catalog-action" style="background:#4CAF50;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar Excel
        </a>
    </div>
</div>

@include('inventario-reportes._toolbar', ['routeName' => 'inventario-reportes.agrupado-producto'])

<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper table-scroll-inv">
            <table class="tbl-inv" id="tblData">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => 'cantidad_total', 'label' => 'Cantidad'],
                                ['key' => 'codigo_1', 'label' => 'Codigo 1'],
                                ['key' => 'codigo_2', 'label' => 'SKU'],
                                ['key' => 'descripcion', 'label' => 'Descripcion'],
                                ['key' => 'precio_venta', 'label' => 'Precio Venta'],
                                ['key' => 'importe', 'label' => 'Importe'],
                            ];
                        @endphp
                        @foreach($columns as $col)
                        <th class="sortable-th {{ $sort === $col['key'] ? 'sort-active' : '' }}">
                            @php $nextDir = ($sort === $col['key'] && $dir === 'asc') ? 'desc' : 'asc'; @endphp
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $col['key'], 'dir' => $nextDir, 'page' => 1]) }}" class="sort-link">
                                {{ $col['label'] }}
                                <span class="sort-arrows">
                                    <svg class="sort-asc {{ $sort === $col['key'] && $dir === 'asc' ? 'active' : '' }}" viewBox="0 0 10 6"><path d="M5 0L10 6H0z"/></svg>
                                    <svg class="sort-desc {{ $sort === $col['key'] && $dir === 'desc' ? 'active' : '' }}" viewBox="0 0 10 6"><path d="M5 6L0 0h10z"/></svg>
                                </span>
                            </a>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $reg)
                    <tr>
                        <td class="text-danger font-weight-bold text-right">{{ number_format($reg->cantidad_total, 2) }}</td>
                        <td>{{ $reg->codigo_1 }}</td>
                        <td>{{ $reg->codigo_2 }}</td>
                        <td class="cell-truncate">{{ $reg->descripcion }}</td>
                        <td class="text-right">{{ number_format($reg->precio_venta, 2) }}</td>
                        <td class="text-right font-weight-bold" style="color:var(--primary);">{{ number_format($reg->importe, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            <div>No se encontraron registros de inventario</div>
                        </td>
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
