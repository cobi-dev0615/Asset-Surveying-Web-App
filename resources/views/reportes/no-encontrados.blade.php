@extends('layouts.app')
@section('title', 'Activos No Encontrados')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Activos no encontrados
    </h2>
    <div class="page-header-actions">
        <button type="button" class="btn btn-catalog-action" style="background:#607D8B;" onclick="window.location.reload()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Actualizar tabla
        </button>
        <button type="button" class="btn btn-catalog-action" style="background:#9C27B0;" id="btnDesmarcar" onclick="desmarcarSeleccionado()" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Quitar marca de NO ENCONTRADO
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        {{-- Toolbar --}}
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
                    <a href="{{ route('reportes.no-encontrados', ['per_page' => $perPage]) }}" class="btn btn-ghost btn-sm">Limpiar filtros</a>
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
            <table class="tbl-ne" id="tblNE">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => 'codigo_1', 'label' => 'Número de Activo'],
                                ['key' => 'codigo_2', 'label' => 'Número de Tag'],
                                ['key' => 'tag_rfid', 'label' => 'Tag RFID'],
                                ['key' => 'n_serie', 'label' => 'Número de Serie'],
                                ['key' => 'categoria_2', 'label' => 'Categoría'],
                                ['key' => 'descripcion', 'label' => 'Descripción'],
                                ['key' => null, 'label' => 'Usuario'],
                                ['key' => 'created_at', 'label' => 'Fecha Hora'],
                                ['key' => null, 'label' => 'Ubicación'],
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
                    @forelse($productos as $prod)
                    <tr data-id="{{ $prod->id }}" onclick="selectRow(this, event)" style="cursor:pointer;">
                        <td style="font-weight:500;">{{ $prod->codigo_1 ?? '' }}</td>
                        <td>{{ $prod->codigo_2 ?? '' }}</td>
                        <td class="cell-sm">{{ $prod->tag_rfid ?? '' }}</td>
                        <td>{{ $prod->n_serie ?? '' }}</td>
                        <td>{{ $prod->categoria_2 ?? '' }}</td>
                        <td class="cell-truncate" style="max-width:300px;">{{ $prod->descripcion ?? '' }}</td>
                        <td></td>
                        <td class="cell-sm" style="white-space:nowrap;">{{ $prod->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            <div>No se encontraron activos no encontrados</div>
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

{{-- ═══ MODAL: Confirmar Desmarcar ═══ --}}
<div class="modal-backdrop" id="unmarkModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h3>Quitar marca de NO ENCONTRADO</h3>
            <button type="button" onclick="closeModal('unmarkModal')" style="background:none; border:none; font-size:1.3rem; cursor:pointer; color:var(--text-secondary);">&times;</button>
        </div>
        <div class="modal-body" style="text-align:center; padding:1.5rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="#9C27B0" stroke-width="1.5" style="width:48px; height:48px; margin-bottom:0.75rem;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <p style="font-size:0.9rem; color:var(--text); margin-bottom:0.25rem;">¿Quitar la marca de NO ENCONTRADO de este activo?</p>
            <p id="unmarkInfo" style="font-size:0.8rem; color:var(--text-secondary);"></p>
        </div>
        <div class="modal-footer" style="justify-content:center; gap:0.75rem;">
            <button type="button" class="btn btn-outline" onclick="closeModal('unmarkModal')">Cancelar</button>
            <form method="POST" id="unmarkForm" action="{{ route('reportes.no-encontrados.unmark') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="ids[]" id="unmarkId" value="">
                <button type="submit" class="btn" style="background:#9C27B0; color:#fff; border:none; padding:0.5rem 1.25rem; border-radius:var(--radius); font-weight:600; cursor:pointer;">Confirmar</button>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
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

    .tbl-ne { table-layout: auto; width: 100%; }
    .tbl-ne td, .tbl-ne th { font-size: 0.78rem; padding: 0.45rem 0.6rem; }
    .cell-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cell-sm { font-size: 0.74rem; }

    .sortable-th { padding: 0 !important; }
    .sort-link {
        display: flex; align-items: center; gap: 0.2rem;
        padding: 0.5rem 0.5rem; color: var(--text); text-decoration: none;
        line-height: 1.2; font-size: 0.78rem; white-space: nowrap;
    }
    .sort-link:hover { background: #eef1f0; color: var(--text); }
    .sort-arrows { display: inline-flex; flex-direction: column; gap: 1px; flex-shrink: 0; }
    .sort-arrows svg { width: 7px; height: 4px; fill: #ccc; }
    .sort-arrows svg.active { fill: var(--primary); }
    .sort-active { background: #f0f4f2; }

    .tbl-ne tbody tr:hover { background: #e8f0ed !important; }
    .tbl-ne tbody tr.row-selected { background: #1565C0 !important; }
    .tbl-ne tbody tr.row-selected td { color: #fff; }
</style>
@endpush

@push('scripts')
<script>
    var selectedRowId = null;
    var selectedRowCode = '';

    function selectRow(tr, e) {
        if (e && e.target.closest('a')) return;
        var rows = document.querySelectorAll('#tblNE tbody tr');
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
        document.getElementById('btnDesmarcar').disabled = (selectedRowId === null);
        var counter = document.getElementById('selectionCount');
        if (selectedRowId) {
            counter.textContent = '1 row selected';
            counter.style.display = 'inline';
        } else {
            counter.style.display = 'none';
        }
    }

    function desmarcarSeleccionado() {
        if (!selectedRowId) return;
        document.getElementById('unmarkId').value = selectedRowId;
        document.getElementById('unmarkInfo').textContent = 'Activo: ' + selectedRowCode;
        openModal('unmarkModal');
    }

    function changePerPage(val) {
        var url = new URL(window.location);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location = url;
    }

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
