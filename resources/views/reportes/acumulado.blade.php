@extends('layouts.app')
@section('title', 'Reporte Acumulado de Activos')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        Reporte acumulado de activos
    </h2>
    <div class="page-header-actions">
        <button type="button" class="btn btn-catalog-action" style="background:#607D8B;" onclick="window.location.reload()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Actualizar tabla
        </button>
        <a href="{{ route('reportes.acumulado.export', request()->query()) }}" class="btn btn-catalog-action" style="background:#4CAF50;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar a Excel
        </a>
        <a href="{{ route('reportes.acumulado.print', request()->query()) }}" target="_blank" class="btn btn-catalog-action" style="background:#78909C;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Exportar a PDF
        </a>
    </div>
</div>

{{-- Info banner --}}
<div style="background:#fff3cd; border:1px solid #ffc107; border-radius:var(--radius); padding:0.65rem 1rem; margin-bottom:1rem; font-size:0.82rem; color:#856404;">
    <strong>Información:</strong> Solamente se muestra la información de las sucursales cuya auditoría ya ha sido iniciada o finalizada.
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        {{-- Filters --}}
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:0.75rem; padding:1rem 1.25rem; border-bottom:1px solid var(--border);">
            <div>
                <label style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:0.25rem;">Filtrar por sucursal</label>
                <select class="form-control" style="font-size:0.82rem;" onchange="applyFilter('sucursal_id', this.value)">
                    <option value="">Todas las sucursales</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>{{ $suc->codigo ? $suc->codigo . ' - ' : '' }}{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:0.25rem;">Filtrar por categoría</label>
                <select class="form-control" style="font-size:0.82rem;" onchange="applyFilter('categoria', this.value)">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:0.25rem;">Filtrar por marca</label>
                <select class="form-control" style="font-size:0.82rem;" onchange="applyFilter('marca', this.value)">
                    <option value="">Todas las marcas</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m }}" {{ request('marca') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:0.25rem;">Filtrar por estatus</label>
                <select class="form-control" style="font-size:0.82rem;" onchange="applyFilter('estatus', this.value)">
                    <option value="">Todos los estatus</option>
                    <option value="ENCONTRADO" {{ request('estatus') == 'ENCONTRADO' ? 'selected' : '' }}>ENCONTRADO</option>
                    <option value="AGREGADO" {{ request('estatus') == 'AGREGADO' ? 'selected' : '' }}>AGREGADO</option>
                    <option value="TRASPASO" {{ request('estatus') == 'TRASPASO' ? 'selected' : '' }}>TRASPASO</option>
                    <option value="SOLICITADO" {{ request('estatus') == 'SOLICITADO' ? 'selected' : '' }}>SOLICITADO</option>
                </select>
            </div>
        </div>

        {{-- Search + entries --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1.25rem; flex-wrap:wrap; gap:0.75rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search" value="{{ request('buscar') }}" style="min-width:180px;">
                </div>
                @if(request()->hasAny(['buscar', 'sucursal_id', 'categoria', 'marca', 'estatus']))
                    <a href="{{ route('reportes.acumulado', ['per_page' => $perPage]) }}" class="btn btn-ghost btn-sm">Limpiar filtros</a>
                @endif
            </div>
            <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.82rem; color:var(--text-secondary);">
                Show
                <select class="form-control" style="width:auto; padding:0.3rem 0.5rem; font-size:0.82rem;" onchange="changePerPage(this.value)">
                    @foreach([10,25,50,100] as $pp)
                    <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                </select>
                entries
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrapper">
            <table class="tbl-acum" id="tblAcum">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => null, 'label' => 'Sucursal'],
                                ['key' => 'nombre_almacen', 'label' => 'Área/Departamento'],
                                ['key' => 'codigo_1', 'label' => 'Número de Activo'],
                                ['key' => 'descripcion', 'label' => 'Descripción'],
                                ['key' => 'codigo_2', 'label' => 'Número de Tag'],
                                ['key' => 'codigo_3', 'label' => 'Número de Tag Nuevo'],
                                ['key' => 'tag_rfid', 'label' => 'Tag RFID'],
                                ['key' => 'n_serie', 'label' => 'Número de Serie'],
                                ['key' => 'n_serie_nuevo', 'label' => 'Número de Serie Revisado'],
                                ['key' => null, 'label' => 'Marca'],
                                ['key' => 'categoria', 'label' => 'Categoría'],
                                ['key' => null, 'label' => 'Estatus'],
                                ['key' => null, 'label' => 'Observaciones'],
                            ];
                        @endphp
                        @foreach($columns as $col)
                        <th class="sortable-th {{ $sort === $col['key'] ? 'sort-active' : '' }}">
                            @if($col['key'])
                                @php
                                    $nextDir = ($sort === $col['key'] && $dir === 'asc') ? 'desc' : 'asc';
                                @endphp
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
                        $status = 'ENCONTRADO';
                        $statusClass = 'badge-status-found';
                        if ($reg->forzado) { $status = 'AGREGADO'; $statusClass = 'badge-status-added'; }
                        elseif ($reg->traspasado) { $status = 'TRASPASO'; $statusClass = 'badge-status-transferred'; }
                        elseif ($reg->solicitado) { $status = 'SOLICITADO'; $statusClass = 'badge-status-requested'; }
                    @endphp
                    <tr>
                        <td class="cell-truncate" style="max-width:160px;">{{ $reg->inventario->sucursal->nombre ?? '' }}</td>
                        <td class="cell-truncate">{{ $reg->nombre_almacen ?? '' }}</td>
                        <td style="font-weight:500;">{{ $reg->codigo_1 ?? '' }}</td>
                        <td class="cell-truncate" style="max-width:200px;">{{ $reg->descripcion ?: ($reg->producto->descripcion ?? '') }}</td>
                        <td>{{ $reg->codigo_2 ?? '' }}</td>
                        <td>{{ $reg->codigo_3 ?? '' }}</td>
                        <td class="cell-sm">{{ $reg->tag_rfid ?? '' }}</td>
                        <td>{{ $reg->n_serie ?? '' }}</td>
                        <td>{{ $reg->n_serie_nuevo ?? '' }}</td>
                        <td class="cell-truncate">{{ $reg->producto->marca ?? '' }}</td>
                        <td class="cell-truncate">{{ $reg->categoria ?: ($reg->producto->categoria_2 ?? '') }}</td>
                        <td><span class="badge-status {{ $statusClass }}">{{ $status }}</span></td>
                        <td class="cell-truncate cell-sm" style="max-width:180px;">{{ $reg->observaciones ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                            <div>No se encontraron registros</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Footer --}}
    <div class="card-footer" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <span style="font-size:0.8rem; color:var(--text-secondary);">
            Showing {{ $registros->firstItem() ?? 0 }} to {{ $registros->lastItem() ?? 0 }} of {{ number_format($registros->total()) }} entries
        </span>
        @if($registros->hasPages())
            {{ $registros->links() }}
        @endif
    </div>
</div>

@push('styles')
<style>
    /* Action buttons */
    .btn-catalog-action {
        display: inline-flex; align-items: center; gap: 0.45rem;
        padding: 0.5rem 0.9rem; border: none; border-radius: var(--radius);
        font-size: 0.8rem; font-weight: 600; color: #fff;
        cursor: pointer; transition: var(--transition);
        text-decoration: none;
    }
    .btn-catalog-action:hover { opacity: 0.88; color: #fff; box-shadow: var(--shadow); }
    .btn-catalog-action svg { width: 16px; height: 16px; }

    /* Table */
    .tbl-acum { table-layout: auto; width: 100%; }
    .tbl-acum td, .tbl-acum th { font-size: 0.76rem; padding: 0.4rem 0.5rem; }
    .cell-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px; }
    .cell-sm { font-size: 0.72rem; }

    /* Sortable headers */
    .sortable-th { padding: 0 !important; }
    .sort-link {
        display: flex; align-items: center; gap: 0.2rem;
        padding: 0.45rem 0.4rem; color: var(--text); text-decoration: none; line-height: 1.2;
        font-size: 0.76rem; white-space: nowrap;
    }
    .sort-link:hover { background: #eef1f0; color: var(--text); }
    .sort-arrows { display: inline-flex; flex-direction: column; gap: 1px; flex-shrink: 0; }
    .sort-arrows svg { width: 7px; height: 4px; fill: #ccc; }
    .sort-arrows svg.active { fill: var(--primary); }
    .sort-active { background: #f0f4f2; }

    /* Status badges */
    .badge-status {
        display: inline-block; padding: 0.2rem 0.5rem; border-radius: 3px;
        font-size: 0.68rem; font-weight: 700; white-space: nowrap; text-transform: uppercase;
    }
    .badge-status-found { background: #4CAF50; color: #fff; }
    .badge-status-added { background: #FF9800; color: #fff; }
    .badge-status-transferred { background: #ffc107; color: #333; }
    .badge-status-requested { background: #9C27B0; color: #fff; }

    /* Row hover */
    .tbl-acum tbody tr:hover { background: #e8f0ed !important; }
</style>
@endpush

@push('scripts')
<script>
    function applyFilter(name, value) {
        var url = new URL(window.location);
        if (value) {
            url.searchParams.set(name, value);
        } else {
            url.searchParams.delete(name);
        }
        url.searchParams.set('page', '1');
        window.location = url;
    }

    function changePerPage(val) {
        var url = new URL(window.location);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location = url;
    }

    // Search on Enter
    document.getElementById('searchInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var url = new URL(window.location);
            url.searchParams.set('buscar', this.value);
            url.searchParams.set('page', '1');
            window.location = url;
        }
    });
</script>
@endpush
@endsection
