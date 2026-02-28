@extends('layouts.app')
@section('title', 'Reporte de Conteo')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Reporte de conteo
    </h2>
    <div class="page-header-actions">
        <button type="button" class="btn btn-catalog-action" style="background:#607D8B;" onclick="window.location.reload()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Actualizar tabla
        </button>
        <button type="button" class="btn btn-catalog-action" style="background:#FF9800;" onclick="toggleDuplicados()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="8" height="8" rx="1"/><rect x="14" y="2" width="8" height="8" rx="1"/><rect x="2" y="14" width="8" height="8" rx="1"/><rect x="14" y="14" width="8" height="8" rx="1"/></svg>
            Mostrar datos duplicados
        </button>
        <a href="{{ route('reportes.conteo.export', array_merge(request()->query(), ['con_imagenes' => 1])) }}" class="btn btn-catalog-action" style="background:#4CAF50;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar a Excel con imagenes
        </a>
        <a href="{{ route('reportes.conteo.export', array_merge(request()->query(), ['con_imagenes' => 0])) }}" class="btn btn-catalog-action" style="background:#43A047;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar a Excel sin imagenes
        </a>
        <a href="{{ route('reportes.conteo.print', array_merge(request()->query(), ['con_imagenes' => 1])) }}" target="_blank" class="btn btn-catalog-action" style="background:#00897B;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Exportar a PDF con imagenes
        </a>
        <a href="{{ route('reportes.conteo.print', array_merge(request()->query(), ['con_imagenes' => 0])) }}" target="_blank" class="btn btn-catalog-action" style="background:#78909C;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Exportar a PDF sin imagenes
        </a>
        <button type="button" class="btn btn-catalog-action" style="background:#2196F3;" id="btnEditar" onclick="editarSeleccionado()" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Editar
        </button>
        <button type="button" class="btn btn-catalog-action" style="background:#f44336;" id="btnEliminar" onclick="eliminarSeleccionado()" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            Eliminar
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        {{-- Toolbar: search + session filter + entries per page + column toggle --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.85rem 1.25rem; flex-wrap:wrap; gap:0.75rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.5rem; flex:1; flex-wrap:wrap;">
                <form method="GET" id="filterForm" style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="dir" value="{{ $dir }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    @if(request('duplicados'))
                        <input type="hidden" name="duplicados" value="{{ request('duplicados') }}">
                    @endif
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Search" value="{{ request('buscar') }}" style="min-width:180px;">
                    </div>
                    <select name="inventario_id" class="form-control" style="width:auto; min-width:160px; font-size:0.82rem;" onchange="this.form.submit()">
                        <option value="">Todas las sesiones</option>
                        @foreach($sesiones as $ses)
                            <option value="{{ $ses->id }}" {{ request('inventario_id') == $ses->id ? 'selected' : '' }}>Sesión #{{ $ses->id }}</option>
                        @endforeach
                    </select>
                </form>
                @if(request()->hasAny(['buscar', 'inventario_id', 'duplicados']))
                    <a href="{{ route('reportes.conteo', ['per_page' => $perPage]) }}" class="btn btn-ghost btn-sm">Limpiar filtros</a>
                @endif
            </div>
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.82rem; color:var(--text-secondary);">
                    Show
                    <select class="form-control" style="width:auto; padding:0.3rem 0.5rem; font-size:0.82rem;" onchange="changePerPage(this.value)">
                        @foreach([10,25,50,100] as $pp)
                        <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                    entries
                </div>
                <div style="position:relative;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="toggleColumnMenu()" style="font-size:0.78rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 3v18M3 12h18"/></svg>
                        Ocultar columnas
                    </button>
                    <div id="columnMenu" class="column-menu" style="display:none;">
                        @php
                            $allCols = [
                                'col-activo' => 'Número de Activo',
                                'col-serie' => 'Número de Serie',
                                'col-serie-rev' => 'Serie Revisado',
                                'col-tag' => 'Número de Tag',
                                'col-tag-nuevo' => 'Tag Nuevo',
                                'col-rfid' => 'Tag RFID',
                                'col-categoria' => 'Categoría',
                                'col-descripcion' => 'Descripción',
                                'col-marca' => 'Marca',
                                'col-unidades' => 'Unidades',
                                'col-depto' => 'Depto/Área',
                                'col-estatus' => 'Estatus',
                                'col-comentarios' => 'Comentarios',
                                'col-usuario' => 'Usuario',
                                'col-fecha' => 'Fecha Hora',
                                'col-ubicacion' => 'Ubicación',
                                'col-imagenes' => 'Imágenes',
                            ];
                        @endphp
                        @foreach($allCols as $colClass => $colLabel)
                            <label class="column-toggle-item">
                                <input type="checkbox" checked onchange="toggleColumn('{{ $colClass }}', this.checked)">
                                {{ $colLabel }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrapper">
            <table class="tbl-conteo" id="tblConteo">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => 'codigo_1', 'label' => 'Número de Activo', 'class' => 'col-activo'],
                                ['key' => 'n_serie', 'label' => 'Número de Serie', 'class' => 'col-serie'],
                                ['key' => 'n_serie_nuevo', 'label' => 'Serie Revisado', 'class' => 'col-serie-rev'],
                                ['key' => 'codigo_2', 'label' => 'Número de Tag', 'class' => 'col-tag'],
                                ['key' => 'codigo_3', 'label' => 'Tag Nuevo', 'class' => 'col-tag-nuevo'],
                                ['key' => 'tag_rfid', 'label' => 'Tag RFID', 'class' => 'col-rfid'],
                                ['key' => 'categoria', 'label' => 'Categoría', 'class' => 'col-categoria'],
                                ['key' => 'descripcion', 'label' => 'Descripción', 'class' => 'col-descripcion'],
                                ['key' => null, 'label' => 'Marca', 'class' => 'col-marca'],
                                ['key' => null, 'label' => 'Unidades', 'class' => 'col-unidades'],
                                ['key' => 'nombre_almacen', 'label' => 'Departamento/Área', 'class' => 'col-depto'],
                                ['key' => null, 'label' => 'Estatus', 'class' => 'col-estatus'],
                                ['key' => null, 'label' => 'Comentarios', 'class' => 'col-comentarios'],
                                ['key' => null, 'label' => 'Usuario', 'class' => 'col-usuario'],
                                ['key' => 'created_at', 'label' => 'Fecha Hora', 'class' => 'col-fecha'],
                                ['key' => 'ubicacion_1', 'label' => 'Ubicación', 'class' => 'col-ubicacion'],
                                ['key' => null, 'label' => 'Imágenes', 'class' => 'col-imagenes'],
                            ];
                        @endphp
                        @foreach($columns as $col)
                        <th class="sortable-th {{ $col['class'] }} {{ $sort === $col['key'] ? 'sort-active' : '' }}">
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
                        elseif ($reg->traspasado) { $status = 'TRASPASADO'; $statusClass = 'badge-status-transferred'; }
                        elseif ($reg->solicitado) { $status = 'SOLICITADO'; $statusClass = 'badge-status-requested'; }
                    @endphp
                    <tr data-id="{{ $reg->id }}" onclick="selectRow(this, event)" style="cursor:pointer;">
                        <td class="cell-truncate col-activo">{{ $reg->codigo_1 ?? '' }}</td>
                        <td class="cell-truncate col-serie">{{ $reg->n_serie ?? '' }}</td>
                        <td class="cell-truncate col-serie-rev">{{ $reg->n_serie_nuevo ?? '' }}</td>
                        <td class="cell-truncate col-tag">{{ $reg->codigo_2 ?? '' }}</td>
                        <td class="cell-truncate col-tag-nuevo">{{ $reg->codigo_3 ?? '' }}</td>
                        <td class="cell-truncate col-rfid cell-sm">{{ $reg->tag_rfid ?? '' }}</td>
                        <td class="cell-truncate col-categoria">{{ $reg->categoria ?: ($reg->producto->categoria_2 ?? '') }}</td>
                        <td class="cell-truncate col-descripcion">{{ $reg->descripcion ?: ($reg->producto->descripcion ?? '') }}</td>
                        <td class="cell-truncate col-marca">{{ $reg->producto->marca ?? '' }}</td>
                        <td class="col-unidades" style="text-align:center;">{{ $reg->producto->cantidad_teorica ?? 1 }}</td>
                        <td class="cell-truncate col-depto">{{ $reg->nombre_almacen ?? '' }}</td>
                        <td class="col-estatus"><span class="badge-status {{ $statusClass }}">{{ $status }}</span></td>
                        <td class="cell-truncate col-comentarios cell-sm">{{ $reg->observaciones ?? '' }}</td>
                        <td class="cell-truncate col-usuario">{{ $reg->usuario->nombres ?? '' }}</td>
                        <td class="col-fecha cell-sm" style="white-space:nowrap;">{{ $reg->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td class="cell-truncate col-ubicacion">{{ $reg->ubicacion_1 ?? '' }}</td>
                        <td class="col-imagenes">
                            @if($reg->imagen1)
                                <img src="{{ asset('storage/fotos/activos/' . $reg->imagen1) }}" class="img-thumb" onclick="event.stopPropagation(); openImageModal('{{ asset('storage/fotos/activos/' . $reg->imagen1) }}')" onerror="this.style.display='none'">
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <div>No se encontraron registros de conteo</div>
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
            <span id="selectionCount" style="margin-left:0.75rem; font-weight:600; color:var(--primary); display:none;"></span>
        </span>
        @if($registros->hasPages())
            {{ $registros->links() }}
        @endif
    </div>
</div>

{{-- ═══ MODAL: Editar Registro ═══ --}}
<div class="modal-backdrop" id="editModal">
    <div class="modal modal-registro">
        <div class="modal-header">
            <h3>Editar registro</h3>
            <button type="button" onclick="closeModal('editModal')" class="modal-close-btn">&times;</button>
        </div>
        <form method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Número de activo</label>
                    <input type="text" name="codigo_1" id="edit_codigo_1" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Número de tag</label>
                    <input type="text" name="codigo_2" id="edit_codigo_2" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Tag nuevo</label>
                    <input type="text" name="codigo_3" id="edit_codigo_3" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Tag RFID</label>
                    <input type="text" name="tag_rfid" id="edit_tag_rfid" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Número de serie</label>
                    <input type="text" name="n_serie" id="edit_n_serie" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Serie revisado</label>
                    <input type="text" name="n_serie_nuevo" id="edit_n_serie_nuevo" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <input type="text" name="categoria" id="edit_categoria" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" id="edit_descripcion" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Departamento/Área</label>
                    <input type="text" name="nombre_almacen" id="edit_nombre_almacen" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Ubicación</label>
                    <input type="text" name="ubicacion_1" id="edit_ubicacion_1" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Comentarios</label>
                    <textarea name="observaciones" id="edit_observaciones" class="form-control" rows="2"></textarea>
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
            <p style="font-size:0.9rem; color:var(--text); margin-bottom:0.25rem;">¿Está seguro que desea eliminar este registro?</p>
            <p id="deleteInfo" style="font-size:0.8rem; color:var(--text-secondary);"></p>
        </div>
        <div class="modal-footer" style="justify-content:center; gap:0.75rem;">
            <button type="button" class="btn btn-outline" onclick="closeModal('deleteModal')">Cancelar</button>
            <form method="POST" id="deleteForm" action="{{ route('reportes.conteo.delete') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="ids[]" id="deleteId" value="">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

{{-- ═══ MODAL: Image Viewer ═══ --}}
<div class="modal-backdrop" id="imageModal" onclick="if(event.target===this) closeModal('imageModal')">
    <div style="display:flex; align-items:center; justify-content:center; height:100%;">
        <img id="imageModalImg" src="" style="max-width:90vw; max-height:85vh; border-radius:8px; box-shadow:0 8px 40px rgba(0,0,0,0.5);">
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

    /* Table */
    .tbl-conteo { table-layout: auto; width: 100%; }
    .tbl-conteo td, .tbl-conteo th { font-size: 0.76rem; padding: 0.4rem 0.5rem; }
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
    .badge-status-transferred { background: #2196F3; color: #fff; }
    .badge-status-requested { background: #9C27B0; color: #fff; }

    /* Image thumbnail */
    .img-thumb {
        width: 36px; height: 36px; object-fit: cover; border-radius: 3px;
        cursor: pointer; border: 1px solid var(--border); transition: transform 0.15s;
    }
    .img-thumb:hover { transform: scale(1.15); }

    /* Row selection */
    .tbl-conteo tbody tr:hover { background: #e8f0ed !important; }
    .tbl-conteo tbody tr.row-selected { background: #1565C0 !important; }
    .tbl-conteo tbody tr.row-selected td { color: #fff; }
    .tbl-conteo tbody tr.row-selected .badge-status { opacity: 0.9; }

    /* Column menu */
    .column-menu {
        position: absolute; right: 0; top: 100%; z-index: 100;
        background: #fff; border: 1px solid var(--border); border-radius: var(--radius);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12); padding: 0.5rem 0; min-width: 200px;
        max-height: 400px; overflow-y: auto;
    }
    .column-toggle-item {
        display: flex; align-items: center; gap: 0.4rem;
        padding: 0.35rem 0.75rem; font-size: 0.8rem; cursor: pointer;
        color: var(--text); transition: background 0.15s;
    }
    .column-toggle-item:hover { background: #f5f5f5; }
    .column-toggle-item input { accent-color: var(--primary); }

    /* Modal styles */
    .modal-registro { max-width: 480px; width: 90%; }
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
</style>
@endpush

@push('scripts')
<script>
    var selectedRowId = null;
    var selectedRowCode = '';

    function selectRow(tr, e) {
        if (e && e.target.closest('a')) return;
        if (e && e.target.closest('.img-thumb')) return;

        var rows = document.querySelectorAll('#tblConteo tbody tr');
        var id = tr.getAttribute('data-id');

        if (selectedRowId === id) {
            tr.classList.remove('row-selected');
            selectedRowId = null;
            selectedRowCode = '';
        } else {
            rows.forEach(function(r) { r.classList.remove('row-selected'); });
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

    function editarSeleccionado() {
        if (!selectedRowId) return;
        fetch('/reportes/conteo/' + selectedRowId + '/editar', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('editForm').action = '/reportes/conteo/' + selectedRowId + '/editar';
            var fields = ['codigo_1','codigo_2','codigo_3','tag_rfid','n_serie','n_serie_nuevo','categoria','descripcion','nombre_almacen','ubicacion_1','observaciones'];
            fields.forEach(function(f) {
                var el = document.getElementById('edit_' + f);
                if (el) el.value = data[f] || '';
            });
            openModal('editModal');
        })
        .catch(function() { alert('Error al cargar los datos del registro'); });
    }

    function eliminarSeleccionado() {
        if (!selectedRowId) return;
        document.getElementById('deleteId').value = selectedRowId;
        document.getElementById('deleteInfo').textContent = 'Activo: ' + selectedRowCode;
        openModal('deleteModal');
    }

    function toggleDuplicados() {
        var url = new URL(window.location);
        if (url.searchParams.get('duplicados') === '1') {
            url.searchParams.delete('duplicados');
        } else {
            url.searchParams.set('duplicados', '1');
            url.searchParams.set('page', '1');
        }
        window.location = url;
    }

    function changePerPage(val) {
        var url = new URL(window.location);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location = url;
    }

    // Column visibility
    function toggleColumnMenu() {
        var menu = document.getElementById('columnMenu');
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }

    function toggleColumn(className, visible) {
        document.querySelectorAll('.' + className).forEach(function(el) {
            el.style.display = visible ? '' : 'none';
        });
    }

    // Close column menu on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#columnMenu') && !e.target.closest('[onclick*="toggleColumnMenu"]')) {
            document.getElementById('columnMenu').style.display = 'none';
        }
    });

    // Image modal
    function openImageModal(src) {
        document.getElementById('imageModalImg').src = src;
        openModal('imageModal');
    }

    // Modal helpers
    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) backdrop.classList.remove('active');
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.active').forEach(function(m) { m.classList.remove('active'); });
        }
    });

    // Search on Enter
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
