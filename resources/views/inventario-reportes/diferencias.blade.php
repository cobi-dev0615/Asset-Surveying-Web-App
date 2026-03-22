@extends('layouts.app')
@section('title', 'Reporte de Diferencias')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Reporte de diferencias
    </h2>
    <div class="page-header-actions">
        <a href="{{ route('inventario-reportes.diferencias.export', request()->query()) }}" class="btn btn-catalog-action" style="background:#4CAF50;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar Excel
        </a>
    </div>
</div>

@include('inventario-reportes._toolbar', ['routeName' => 'inventario-reportes.diferencias'])

<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper table-scroll-inv">
            <table class="tbl-inv">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => 'codigo_1', 'label' => 'Codigo 1'],
                                ['key' => 'codigo_2', 'label' => 'SKU'],
                                ['key' => 'descripcion', 'label' => 'Descripcion'],
                                ['key' => 'precio_venta', 'label' => 'Precio Venta'],
                                ['key' => 'cantidad_teorica', 'label' => 'Cant. Teorica'],
                                ['key' => 'cantidad_real', 'label' => 'Cant. Real'],
                                ['key' => 'diferencia_cantidad', 'label' => 'Diferencia'],
                                ['key' => null, 'label' => 'Importe Teorico'],
                                ['key' => null, 'label' => 'Importe Real'],
                                ['key' => 'diferencia_importe', 'label' => 'Dif. Importe'],
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
                    @php
                        $diff = $reg->diferencia_cantidad;
                        $rowClass = $diff < 0 ? 'row-negative' : ($diff == 0 ? 'row-zero' : 'row-positive');
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>{{ $reg->codigo_1 }}</td>
                        <td>{{ $reg->codigo_2 }}</td>
                        <td class="cell-truncate">{{ $reg->descripcion }}</td>
                        <td class="text-right">{{ number_format($reg->precio_venta, 2) }}</td>
                        <td class="text-right">{{ number_format($reg->cantidad_teorica, 2) }}</td>
                        <td class="text-right" style="color:var(--primary); font-weight:600;">{{ number_format($reg->cantidad_real, 2) }}</td>
                        <td class="text-right font-weight-bold {{ $diff < 0 ? 'text-danger' : ($diff > 0 ? '' : '') }}" style="{{ $diff > 0 ? 'color:#2e7d32;' : '' }}">{{ number_format($diff, 2) }}</td>
                        <td class="text-right">{{ number_format($reg->cantidad_teorica * $reg->precio_venta, 2) }}</td>
                        <td class="text-right">{{ number_format($reg->cantidad_real * $reg->precio_venta, 2) }}</td>
                        <td class="text-right font-weight-bold {{ $diff < 0 ? 'text-danger' : '' }}" style="{{ $diff > 0 ? 'color:#2e7d32;' : '' }}">{{ number_format($reg->diferencia_importe, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="table-empty"><div>No se encontraron diferencias de inventario</div></td>
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
