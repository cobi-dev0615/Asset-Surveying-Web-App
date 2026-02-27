@extends('layouts.app')
@section('title', 'Catálogo de Activos')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        Catálogo de activos
    </h2>
    <div class="page-header-actions">
        <button type="button" class="btn btn-catalog-action" style="background:#607D8B;" onclick="window.location.reload()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Actualizar tabla
        </button>
        <a href="{{ route('activo-fijo-productos.import.form') }}" class="btn btn-catalog-action" style="background:#9C27B0;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Importar catálogo
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        {{-- Toolbar: search + entries per page --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.85rem 1.25rem; flex-wrap:wrap; gap:0.75rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.5rem; flex:1; flex-wrap:wrap;">
                <form method="GET" id="filterForm" style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="dir" value="{{ $dir }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    @if(request('empresa_id'))
                    <input type="hidden" name="empresa_id" value="{{ request('empresa_id') }}">
                    @endif
                    @if(request('inventario_id'))
                    <input type="hidden" name="inventario_id" value="{{ request('inventario_id') }}">
                    @endif
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Search" value="{{ request('buscar') }}" style="min-width:180px;">
                    </div>
                </form>
                @if(request()->hasAny(['buscar', 'empresa_id', 'inventario_id']))
                    <a href="{{ route('activo-fijo-productos.index', ['per_page' => $perPage]) }}" class="btn btn-ghost btn-sm">Limpiar filtros</a>
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

        {{-- Advanced filters --}}
        <div style="display:flex; align-items:center; gap:0.5rem; padding:0.65rem 1.25rem; background:#fafafa; border-bottom:1px solid var(--border); flex-wrap:wrap;">
            <form method="GET" id="advFilterForm" style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="dir" value="{{ $dir }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                @if(request('buscar'))
                <input type="hidden" name="buscar" value="{{ request('buscar') }}">
                @endif
                <select name="empresa_id" class="form-control" style="width:auto; min-width:170px; font-size:0.8rem; padding:0.35rem 0.5rem;">
                    <option value="">Todas las empresas</option>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" {{ request('empresa_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nombre }}</option>
                    @endforeach
                </select>
                <select name="inventario_id" class="form-control" style="width:auto; min-width:200px; font-size:0.8rem; padding:0.35rem 0.5rem;">
                    <option value="">Todas las sesiones</option>
                    @foreach($sesiones as $ses)
                        <option value="{{ $ses->id }}" {{ request('inventario_id') == $ses->id ? 'selected' : '' }}>
                            {{ $ses->empresa->nombre ?? '' }} - {{ $ses->sucursal->nombre ?? $ses->nombre ?? 'Sesión #'.$ses->id }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        @php
                            $columns = [
                                'codigo_1'       => 'Número de Activo',
                                'codigo_2'       => 'Número de Tag',
                                'codigo_3'       => 'Número de Tag Nuevo',
                                'tag_rfid'       => 'Tag RFID',
                                'n_serie'        => 'Número de Serie',
                                'n_serie_nuevo'  => 'Número de Serie Revisado',
                                'categoria_2'    => 'Categoría',
                                'descripcion'    => 'Descripción',
                                'marca'          => 'Marca',
                                'forzado'        => 'Forzado',
                                'traspasado'     => 'Traspasado',
                            ];
                        @endphp
                        @foreach($columns as $col => $label)
                        @php
                            $isActive = ($sort === $col);
                            $nextDir = ($isActive && $dir === 'asc') ? 'desc' : 'asc';
                        @endphp
                        <th class="sortable-th {{ $isActive ? 'sort-active' : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $col, 'dir' => $nextDir, 'page' => 1]) }}" class="sort-link">
                                {{ $label }}
                                <span class="sort-arrows">
                                    <svg class="sort-asc {{ $isActive && $dir === 'asc' ? 'active' : '' }}" viewBox="0 0 10 6"><path d="M5 0L10 6H0z"/></svg>
                                    <svg class="sort-desc {{ $isActive && $dir === 'desc' ? 'active' : '' }}" viewBox="0 0 10 6"><path d="M5 6L0 0h10z"/></svg>
                                </span>
                            </a>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr onclick="window.location='{{ route('activo-fijo-productos.show', $producto) }}'" style="cursor:pointer;">
                        <td style="font-weight:500;">{{ $producto->codigo_1 }}</td>
                        <td>{{ $producto->codigo_2 ?: '' }}</td>
                        <td>{{ $producto->codigo_3 ?: '' }}</td>
                        <td style="font-size:0.78rem;">{{ $producto->tag_rfid ?: '' }}</td>
                        <td style="font-size:0.78rem;">{{ $producto->n_serie ?: '' }}</td>
                        <td style="font-size:0.78rem;">{{ $producto->n_serie_nuevo ?: '' }}</td>
                        <td>{{ $producto->categoria_2 ?: $producto->categoria_1 ?: '' }}</td>
                        <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $producto->descripcion }}</td>
                        <td>{{ $producto->marca ?: '' }}</td>
                        <td>
                            @if($producto->forzado)
                                <span class="badge-pill badge-pill-red">SI</span>
                            @else
                                <span class="badge-pill badge-pill-green">NO</span>
                            @endif
                        </td>
                        <td>
                            @if($producto->traspasado)
                                <span class="badge-pill badge-pill-red">SI</span>
                            @else
                                <span class="badge-pill badge-pill-green">NO</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                            <div>No se encontraron activos fijos</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Footer: showing X to Y of Z + pagination --}}
    <div class="card-footer" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <span style="font-size:0.8rem; color:var(--text-secondary);">
            Showing {{ $productos->firstItem() ?? 0 }} to {{ $productos->lastItem() ?? 0 }} of {{ number_format($productos->total()) }} entries
        </span>
        @if($productos->hasPages())
            {{ $productos->links() }}
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

    /* Sortable column headers */
    .sortable-th { padding: 0 !important; }
    .sort-link {
        display: flex; align-items: center; gap: 0.3rem;
        padding: 0.65rem 0.85rem;
        color: var(--text); text-decoration: none;
        white-space: nowrap;
    }
    .sort-link:hover { background: #eef1f0; color: var(--text); }
    .sort-arrows {
        display: inline-flex; flex-direction: column; gap: 1px; margin-left: 0.15rem;
    }
    .sort-arrows svg { width: 8px; height: 5px; fill: #ccc; }
    .sort-arrows svg.active { fill: var(--primary); }
    .sort-active { background: #f0f4f2; }

    /* Badge pills for Forzado/Traspasado */
    .badge-pill {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 3px;
        font-size: 0.7rem;
        font-weight: 700;
        text-align: center;
        min-width: 32px;
    }
    .badge-pill-green { background: #4CAF50; color: #fff; }
    .badge-pill-red { background: #f44336; color: #fff; }

    /* Clickable rows */
    table tbody tr:hover { background: #e8f0ed !important; }
</style>
@endpush

@push('scripts')
<script>
    function changePerPage(val) {
        var url = new URL(window.location);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location = url;
    }

    // Submit search on Enter
    document.querySelector('#filterForm input[name="buscar"]').addEventListener('keydown', function(e) {
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
