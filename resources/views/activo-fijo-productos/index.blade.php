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
        <button type="button" class="btn btn-catalog-action" style="background:#4CAF50;" onclick="openModal('createModal')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Agregar
        </button>
        <button type="button" class="btn btn-catalog-action" style="background:#2196F3;" id="btnEditar" onclick="editarSeleccionado()" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Editar
        </button>
        <button type="button" class="btn btn-catalog-action" style="background:#f44336;" id="btnEliminar" onclick="eliminarSeleccionado()" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            Eliminar
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
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Search" value="{{ request('buscar') }}" style="min-width:180px;">
                    </div>
                </form>
                @if(request('buscar'))
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

        {{-- Table --}}
        <div class="table-wrapper">
            <table class="tbl-activos" id="tblActivos">
                <colgroup>
                    <col style="width:9%;">
                    <col style="width:8%;">
                    <col style="width:8%;">
                    <col style="width:10%;">
                    <col style="width:9%;">
                    <col style="width:9%;">
                    <col style="width:10%;">
                    <col>
                    <col style="width:7%;">
                    <col style="width:5%;">
                    <col style="width:5%;">
                </colgroup>
                <thead>
                    <tr>
                        @php
                            $columns = [
                                'codigo_1'       => 'N° Activo',
                                'codigo_2'       => 'N° Tag',
                                'codigo_3'       => 'Tag Nuevo',
                                'tag_rfid'       => 'Tag RFID',
                                'n_serie'        => 'N° Serie',
                                'n_serie_nuevo'  => 'Serie Rev.',
                                'categoria_2'    => 'Categoría',
                                'descripcion'    => 'Descripción',
                                'marca'          => 'Marca',
                                'forzado'        => 'Forz.',
                                'traspasado'     => 'Trasp.',
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
                    <tr data-id="{{ $producto->id }}" onclick="selectRow(this, event)" ondblclick="window.location='{{ route('activo-fijo-productos.show', $producto) }}'" style="cursor:pointer;">
                        <td class="cell-truncate" style="font-weight:500;">{{ $producto->codigo_1 }}</td>
                        <td class="cell-truncate">{{ $producto->codigo_2 ?: '' }}</td>
                        <td class="cell-truncate">{{ $producto->codigo_3 ?: '' }}</td>
                        <td class="cell-truncate cell-sm">{{ $producto->tag_rfid ?: '' }}</td>
                        <td class="cell-truncate cell-sm">{{ $producto->n_serie ?: '' }}</td>
                        <td class="cell-truncate cell-sm">{{ $producto->n_serie_nuevo ?: '' }}</td>
                        <td class="cell-truncate">{{ $producto->categoria_2 ?: $producto->categoria_1 ?: '' }}</td>
                        <td class="cell-truncate">{{ $producto->descripcion }}</td>
                        <td class="cell-truncate">{{ $producto->marca ?: '' }}</td>
                        <td style="text-align:center;">
                            @if($producto->forzado)
                                <span class="badge-pill badge-pill-red">SI</span>
                            @else
                                <span class="badge-pill badge-pill-green">NO</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
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

    {{-- Footer --}}
    <div class="card-footer" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <span style="font-size:0.8rem; color:var(--text-secondary);">
            Showing {{ $productos->firstItem() ?? 0 }} to {{ $productos->lastItem() ?? 0 }} of {{ number_format($productos->total()) }} entries
            <span id="selectionCount" style="margin-left:0.75rem; font-weight:600; color:var(--primary); display:none;"></span>
        </span>
        @if($productos->hasPages())
            {{ $productos->links() }}
        @endif
    </div>
</div>

{{-- ═══ MODAL: Registrar producto nuevo ═══ --}}
<div class="modal-backdrop" id="createModal">
    <div class="modal modal-registro">
        <div class="modal-header">
            <h3>Registrar producto nuevo</h3>
            <button type="button" onclick="closeModal('createModal')" class="modal-close-btn">&times;</button>
        </div>
        <form method="POST" action="{{ route('activo-fijo-productos.store') }}">
            @csrf
            <input type="hidden" name="inventario_id" value="{{ $defaultInventarioId }}">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Número de activo *</label>
                    <input type="text" name="codigo_1" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Número de tag</label>
                    <input type="text" name="codigo_2" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Número de tag nuevo</label>
                    <input type="text" name="codigo_3" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Número de serie</label>
                    <input type="text" name="n_serie" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Nuevo número de serie revisado</label>
                    <input type="text" name="n_serie_nuevo" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <input type="hidden" name="categoria_2" id="create_categoria_2_val" value="">
                    <div class="searchable-select" data-target="create_categoria_2_val">
                        <div class="ss-trigger" onclick="toggleSearchSelect(this)">
                            <span class="ss-display">Seleccionar categoría...</span>
                            <svg class="ss-arrow" viewBox="0 0 10 6"><path d="M0 0l5 6 5-6z"/></svg>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" placeholder="" oninput="filterSearchSelect(this)">
                            </div>
                            <div class="ss-options">
                                @foreach($categorias as $cat)
                                    <div class="ss-option" data-value="{{ $cat }}" onclick="pickSearchSelect(this)">{{ $cat }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input type="hidden" name="marca" id="create_marca_val" value="">
                    <div class="searchable-select" data-target="create_marca_val">
                        <div class="ss-trigger" onclick="toggleSearchSelect(this)">
                            <span class="ss-display">Seleccionar marca...</span>
                            <svg class="ss-arrow" viewBox="0 0 10 6"><path d="M0 0l5 6 5-6z"/></svg>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" placeholder="" oninput="filterSearchSelect(this)">
                            </div>
                            <div class="ss-options">
                                @foreach($marcas as $m)
                                    <div class="ss-option" data-value="{{ $m }}" onclick="pickSearchSelect(this)">{{ $m }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción del activo *</label>
                    <input type="text" name="descripcion" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer modal-footer-stacked">
                <button type="submit" class="btn btn-success btn-block">Guardar</button>
                <button type="button" class="btn btn-secondary btn-block" onclick="closeModal('createModal')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL: Editar Activo ═══ --}}
<div class="modal-backdrop" id="editModal">
    <div class="modal modal-registro">
        <div class="modal-header">
            <h3>Editar producto</h3>
            <button type="button" onclick="closeModal('editModal')" class="modal-close-btn">&times;</button>
        </div>
        <form method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Número de activo *</label>
                    <input type="text" name="codigo_1" id="edit_codigo_1" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Número de tag</label>
                    <input type="text" name="codigo_2" id="edit_codigo_2" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Número de tag nuevo</label>
                    <input type="text" name="codigo_3" id="edit_codigo_3" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Número de serie</label>
                    <input type="text" name="n_serie" id="edit_n_serie" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Nuevo número de serie revisado</label>
                    <input type="text" name="n_serie_nuevo" id="edit_n_serie_nuevo" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <input type="hidden" name="categoria_2" id="edit_categoria_2" value="">
                    <div class="searchable-select" data-target="edit_categoria_2">
                        <div class="ss-trigger" onclick="toggleSearchSelect(this)">
                            <span class="ss-display">Seleccionar categoría...</span>
                            <svg class="ss-arrow" viewBox="0 0 10 6"><path d="M0 0l5 6 5-6z"/></svg>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" placeholder="" oninput="filterSearchSelect(this)">
                            </div>
                            <div class="ss-options">
                                @foreach($categorias as $cat)
                                    <div class="ss-option" data-value="{{ $cat }}" onclick="pickSearchSelect(this)">{{ $cat }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input type="hidden" name="marca" id="edit_marca" value="">
                    <div class="searchable-select" data-target="edit_marca">
                        <div class="ss-trigger" onclick="toggleSearchSelect(this)">
                            <span class="ss-display">Seleccionar marca...</span>
                            <svg class="ss-arrow" viewBox="0 0 10 6"><path d="M0 0l5 6 5-6z"/></svg>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" placeholder="" oninput="filterSearchSelect(this)">
                            </div>
                            <div class="ss-options">
                                @foreach($marcas as $m)
                                    <div class="ss-option" data-value="{{ $m }}" onclick="pickSearchSelect(this)">{{ $m }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción del activo *</label>
                    <input type="text" name="descripcion" id="edit_descripcion" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer modal-footer-stacked">
                <button type="submit" class="btn btn-success btn-block">Actualizar</button>
                <button type="button" class="btn btn-secondary btn-block" onclick="closeModal('editModal')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL: Confirmar Eliminación ═══ --}}
<div class="modal-backdrop" id="deleteModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h3>Confirmar eliminación</h3>
            <button type="button" onclick="closeModal('deleteModal')" style="background:none; border:none; font-size:1.3rem; cursor:pointer; color:var(--text-secondary);">&times;</button>
        </div>
        <div class="modal-body" style="text-align:center; padding:1.5rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="#f44336" stroke-width="1.5" style="width:48px; height:48px; margin-bottom:0.75rem;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <p style="font-size:0.9rem; color:var(--text); margin-bottom:0.25rem;">¿Está seguro que desea eliminar este activo?</p>
            <p id="deleteInfo" style="font-size:0.8rem; color:var(--text-secondary);"></p>
        </div>
        <div class="modal-footer" style="justify-content:center; gap:0.75rem;">
            <button type="button" class="btn btn-outline" onclick="closeModal('deleteModal')">Cancelar</button>
            <form method="POST" id="deleteForm" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
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
    .btn-catalog-action:disabled { opacity: 0.45; cursor: not-allowed; box-shadow: none; }
    .btn-catalog-action svg { width: 16px; height: 16px; }

    /* Fixed-layout table */
    .tbl-activos { table-layout: fixed; width: 100%; }
    .tbl-activos td, .tbl-activos th { font-size: 0.78rem; }

    /* Truncate cells */
    .cell-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cell-sm { font-size: 0.74rem; }

    /* Sortable column headers */
    .sortable-th { padding: 0 !important; }
    .sort-link {
        display: flex; align-items: center; gap: 0.2rem;
        padding: 0.5rem 0.45rem;
        color: var(--text); text-decoration: none;
        line-height: 1.2;
    }
    .sort-link:hover { background: #eef1f0; color: var(--text); }
    .sort-arrows {
        display: inline-flex; flex-direction: column; gap: 1px; flex-shrink: 0;
    }
    .sort-arrows svg { width: 7px; height: 4px; fill: #ccc; }
    .sort-arrows svg.active { fill: var(--primary); }
    .sort-active { background: #f0f4f2; }

    /* Badge pills */
    .badge-pill {
        display: inline-block;
        padding: 0.15rem 0.4rem;
        border-radius: 3px;
        font-size: 0.65rem;
        font-weight: 700;
        text-align: center;
    }
    .badge-pill-green { background: #4CAF50; color: #fff; }
    .badge-pill-red { background: #f44336; color: #fff; }

    /* Row selection */
    .tbl-activos tbody tr:hover { background: #e8f0ed !important; }
    .tbl-activos tbody tr.row-selected { background: #1565C0 !important; }
    .tbl-activos tbody tr.row-selected td { color: #fff; }
    .tbl-activos tbody tr.row-selected .badge-pill-green { background: #81C784; }
    .tbl-activos tbody tr.row-selected .badge-pill-red { background: #EF5350; }

    /* Registration modal */
    .modal-registro { max-width: 450px; width: 90%; }
    .modal-registro .modal-header { padding: 1rem 1.25rem; }
    .modal-registro .modal-header h3 { font-size: 1rem; font-weight: 600; }
    .modal-registro .modal-body { padding: 0.75rem 1.25rem; max-height: 65vh; overflow-y: auto; }
    .modal-registro .form-group { margin-bottom: 0.65rem; }
    .modal-registro .form-label { font-size: 0.8rem; margin-bottom: 0.2rem; color: var(--text-secondary); }
    .modal-registro .form-control { font-size: 0.85rem; padding: 0.45rem 0.65rem; }
    .modal-close-btn { background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--text-secondary); line-height: 1; }
    .modal-close-btn:hover { color: var(--text); }
    .modal-footer-stacked { flex-direction: column; gap: 0.5rem; padding: 0.75rem 1.25rem 1rem; }
    .btn-block { width: 100%; display: block; text-align: center; }
    .btn-secondary { background: #6c757d; color: #fff; border: none; padding: 0.55rem 1rem; border-radius: var(--radius); font-weight: 600; cursor: pointer; font-size: 0.85rem; }
    .btn-secondary:hover { background: #5a6268; color: #fff; }

    /* Searchable select dropdown */
    .searchable-select { position: relative; }
    .ss-trigger {
        display: flex; align-items: center; justify-content: space-between;
        padding: 0.45rem 0.65rem; border: 1px solid var(--border);
        border-radius: var(--radius); background: #fff; cursor: pointer;
        font-size: 0.85rem; color: var(--text); min-height: 2.1rem;
    }
    .ss-trigger:hover { border-color: #adb5bd; }
    .ss-display { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
    .ss-display.placeholder { color: #999; }
    .ss-arrow { width: 10px; height: 6px; fill: #999; flex-shrink: 0; margin-left: 0.5rem; transition: transform 0.2s; }
    .searchable-select.open .ss-arrow { transform: rotate(180deg); }
    .ss-dropdown {
        display: none; position: absolute; left: 0; right: 0; top: 100%;
        z-index: 1000; background: #fff; border: 1px solid var(--border);
        border-top: none; border-radius: 0 0 var(--radius) var(--radius);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .searchable-select.open .ss-dropdown { display: block; }
    .ss-search {
        display: flex; align-items: center; gap: 0.4rem;
        padding: 0.5rem 0.65rem; border-bottom: 1px solid var(--border);
    }
    .ss-search svg { width: 16px; height: 16px; color: #999; flex-shrink: 0; }
    .ss-search input {
        border: none; outline: none; font-size: 0.85rem; width: 100%;
        background: transparent; color: var(--text);
    }
    .ss-options { max-height: 200px; overflow-y: auto; }
    .ss-option {
        padding: 0.5rem 0.75rem; font-size: 0.85rem; cursor: pointer;
        color: var(--text); transition: background 0.15s;
    }
    .ss-option:hover { background: #f0f4f2; }
    .ss-option.selected { background: #6b7e6f; color: #fff; }
    .ss-option.hidden { display: none; }
</style>
@endpush

@push('scripts')
<script>
    var selectedRowId = null;
    var selectedRowCode = '';

    function selectRow(tr, e) {
        // Don't select if clicking a link
        if (e && e.target.closest('a')) return;

        var rows = document.querySelectorAll('#tblActivos tbody tr');
        var id = tr.getAttribute('data-id');

        if (selectedRowId === id) {
            // Deselect
            tr.classList.remove('row-selected');
            selectedRowId = null;
            selectedRowCode = '';
        } else {
            // Deselect previous
            rows.forEach(function(r) { r.classList.remove('row-selected'); });
            // Select new
            tr.classList.add('row-selected');
            selectedRowId = id;
            selectedRowCode = tr.cells[0].textContent.trim();
        }
        updateButtons();
    }

    function updateButtons() {
        var hasSelection = selectedRowId !== null;
        document.getElementById('btnEditar').disabled = !hasSelection;
        document.getElementById('btnEliminar').disabled = !hasSelection;

        var counter = document.getElementById('selectionCount');
        if (hasSelection) {
            counter.textContent = '1 row selected';
            counter.style.display = 'inline';
        } else {
            counter.style.display = 'none';
        }
    }

    /* ── Searchable Select ── */
    function toggleSearchSelect(trigger) {
        var ss = trigger.closest('.searchable-select');
        var wasOpen = ss.classList.contains('open');
        // Close all others first
        document.querySelectorAll('.searchable-select.open').forEach(function(el) {
            el.classList.remove('open');
        });
        if (!wasOpen) {
            ss.classList.add('open');
            var input = ss.querySelector('.ss-search input');
            if (input) { input.value = ''; input.focus(); }
            // Reset filter
            ss.querySelectorAll('.ss-option').forEach(function(o) { o.classList.remove('hidden'); });
        }
    }

    function filterSearchSelect(input) {
        var term = input.value.toLowerCase();
        var options = input.closest('.ss-dropdown').querySelectorAll('.ss-option');
        options.forEach(function(o) {
            o.classList.toggle('hidden', o.textContent.toLowerCase().indexOf(term) === -1);
        });
    }

    function pickSearchSelect(optionEl) {
        var ss = optionEl.closest('.searchable-select');
        var targetId = ss.getAttribute('data-target');
        var value = optionEl.getAttribute('data-value');
        var display = ss.querySelector('.ss-display');

        // Update hidden input
        document.getElementById(targetId).value = value;

        // Update display
        display.textContent = optionEl.textContent;
        display.classList.remove('placeholder');

        // Mark selected
        ss.querySelectorAll('.ss-option').forEach(function(o) { o.classList.remove('selected'); });
        optionEl.classList.add('selected');

        // Close
        ss.classList.remove('open');
    }

    function setSearchSelectValue(targetId, value) {
        var hidden = document.getElementById(targetId);
        if (!hidden) return;
        hidden.value = value || '';
        var ss = hidden.nextElementSibling;
        if (!ss || !ss.classList.contains('searchable-select')) return;
        var display = ss.querySelector('.ss-display');
        var options = ss.querySelectorAll('.ss-option');
        var found = false;
        options.forEach(function(o) {
            o.classList.remove('selected');
            if (o.getAttribute('data-value') === value) {
                o.classList.add('selected');
                display.textContent = o.textContent;
                display.classList.remove('placeholder');
                found = true;
            }
        });
        if (!found) {
            display.textContent = display.closest('.searchable-select').querySelector('.ss-trigger').getAttribute('data-placeholder') || (targetId.indexOf('categoria') !== -1 ? 'Seleccionar categoría...' : 'Seleccionar marca...');
            display.classList.add('placeholder');
        }
    }

    // Close searchable selects when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.searchable-select')) {
            document.querySelectorAll('.searchable-select.open').forEach(function(el) {
                el.classList.remove('open');
            });
        }
    });

    function editarSeleccionado() {
        if (!selectedRowId) return;
        // Fetch product data via JSON endpoint
        fetch('/activo-fijo-productos/' + selectedRowId + '/editar', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('editForm').action = '/activo-fijo-productos/' + selectedRowId;
            var fields = ['codigo_1','codigo_2','codigo_3','descripcion','n_serie','n_serie_nuevo'];
            fields.forEach(function(f) {
                var el = document.getElementById('edit_' + f);
                if (el) el.value = data[f] || '';
            });
            // Set searchable select dropdowns
            setSearchSelectValue('edit_categoria_2', data.categoria_2 || '');
            setSearchSelectValue('edit_marca', data.marca || '');
            openModal('editModal');
        })
        .catch(function() {
            showToast('Error al cargar los datos del activo', 'error');
        });
    }

    function eliminarSeleccionado() {
        if (!selectedRowId) return;
        document.getElementById('deleteForm').action = '/activo-fijo-productos/' + selectedRowId;
        document.getElementById('deleteInfo').textContent = 'Activo: ' + selectedRowCode;
        openModal('deleteModal');
    }

    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // Close modals on backdrop click
    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) backdrop.classList.remove('active');
        });
    });

    // Close modals on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.active').forEach(function(m) {
                m.classList.remove('active');
            });
        }
    });

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
