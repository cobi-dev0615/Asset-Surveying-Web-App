@extends('layouts.app')
@section('title', 'Sucursales')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        Sucursales
    </h2>
</div>

{{-- Info banner --}}
<div style="background:#fff3cd; border:1px solid #ffc107; border-radius:var(--radius); padding:0.65rem 1rem; margin-bottom:1rem; font-size:0.82rem; color:#856404;">
    Para crear una nueva sucursal se debe importar desde el catálogo de productos, este dato debe colocarse en la columna Subsidiaria.
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        {{-- Action buttons --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1.25rem; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:0.5rem;">
            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <button type="button" class="btn btn-catalog-action" style="background:#607D8B;" onclick="window.location.reload()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Actualizar tabla
                </button>

                @if(Auth::user()->esSupervisor())
                <a href="{{ route('sucursales.create') }}" class="btn btn-catalog-action" style="background:#1976D2;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Agregar
                </a>
                <a href="#" class="btn btn-catalog-action" style="background:#00897B;" id="btnEditar" onclick="editSelected(event)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Editar
                </a>
                <button type="button" class="btn btn-catalog-action" style="background:#7B1FA2;" id="btnEliminar" onclick="deleteSelected()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Eliminar
                </button>
                <button type="button" class="btn btn-catalog-action" style="background:#78909C;" id="btnImagenes" onclick="deleteResidualImages()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    Eliminar imágenes residuales
                </button>
                @endif
            </div>

            <a href="{{ route('sucursales.export', request()->query()) }}" class="btn btn-catalog-action" style="background:#2E7D32;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Exportar a Excel
            </a>
        </div>

        {{-- Search + entries --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1.25rem; flex-wrap:wrap; gap:0.75rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search" value="{{ request('buscar') }}" style="min-width:180px;">
                </div>
                @if(request('buscar'))
                    <a href="{{ route('sucursales.index') }}" class="btn btn-ghost btn-sm">Limpiar filtros</a>
                @endif
            </div>
            <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.82rem; color:var(--text-secondary);">
                Show
                <select class="form-control" style="width:auto; padding:0.3rem 0.5rem; font-size:0.82rem;" onchange="changePerPage(this.value)">
                    @foreach([10,20,25,50,100] as $pp)
                    <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                </select>
                entries
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrapper">
            <table class="tbl-suc">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => 'empresa_id', 'label' => 'Empresa'],
                                ['key' => 'codigo', 'label' => 'Código Sucursal'],
                                ['key' => 'nombre', 'label' => 'Nombre de la Sucursal'],
                                ['key' => null, 'label' => 'Local'],
                                ['key' => 'ciudad', 'label' => 'Ciudad'],
                                ['key' => null, 'label' => 'Status'],
                                ['key' => 'created_at', 'label' => 'Fecha Creación'],
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
                    @forelse($sucursales as $s)
                    @php
                        $latestSession = $s->activoFijoInventarios->first();
                        $statusName = $latestSession?->status?->nombre ?? '';
                        $statusClass = match($statusName) {
                            'PENDIENTE POR INICIAR' => 'badge-status-pendiente',
                            'INICIADO' => 'badge-status-iniciado',
                            'FINALIZADO' => 'badge-status-finalizado',
                            default => '',
                        };
                    @endphp
                    <tr data-id="{{ $s->id }}" class="selectable-row" onclick="selectRow(this)">
                        <td class="cell-truncate" style="max-width:180px;">{{ $s->empresa->nombre ?? '' }}</td>
                        <td>{{ $s->codigo }}</td>
                        <td style="font-weight:500;">{{ $s->nombre }}</td>
                        <td>{{ $latestSession->local ?? '' }}</td>
                        <td>{{ $s->ciudad ?? '' }}</td>
                        <td>
                            @if($statusName)
                                <span class="badge-status {{ $statusClass }}">{{ $statusName }}</span>
                            @endif
                        </td>
                        <td class="cell-date">{{ $s->created_at?->format('Y-m-d H:i:s') ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <div>No se encontraron sucursales</div>
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
            Showing {{ $sucursales->firstItem() ?? 0 }} to {{ $sucursales->lastItem() ?? 0 }} of {{ number_format($sucursales->total()) }} entries
        </span>
        @if($sucursales->hasPages())
            {{ $sucursales->links() }}
        @endif
    </div>
</div>

{{-- Hidden delete form --}}
<form id="deleteForm" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

{{-- Hidden image cleanup form --}}
<form id="imageForm" method="POST" style="display:none;">
    @csrf
</form>

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
    .btn-catalog-action svg { width: 16px; height: 16px; }

    .tbl-suc { table-layout: auto; width: 100%; }
    .tbl-suc td, .tbl-suc th { font-size: 0.76rem; padding: 0.4rem 0.5rem; }
    .cell-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cell-date { white-space: nowrap; font-size: 0.74rem; color: #4CAF50; }

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

    .badge-status {
        display: inline-block; padding: 0.2rem 0.5rem; border-radius: 3px;
        font-size: 0.68rem; font-weight: 700; white-space: nowrap;
    }
    .badge-status-pendiente { background: #4CAF50; color: #fff; }
    .badge-status-iniciado { background: #2196F3; color: #fff; }
    .badge-status-finalizado { background: #9C27B0; color: #fff; }

    .selectable-row { cursor: pointer; transition: background 0.15s; }
    .selectable-row:hover { background: #e8f0ed !important; }
    .selectable-row.selected { background: #c8e6c9 !important; }
</style>
@endpush

@push('scripts')
<script>
    var selectedId = null;

    function selectRow(tr) {
        document.querySelectorAll('.selectable-row').forEach(function(r) { r.classList.remove('selected'); });
        tr.classList.add('selected');
        selectedId = tr.dataset.id;
    }

    function editSelected(e) {
        e.preventDefault();
        if (!selectedId) { alert('Seleccione una sucursal haciendo clic en una fila.'); return; }
        window.location.href = '{{ url("sucursales") }}/' + selectedId + '/edit';
    }

    function deleteSelected() {
        if (!selectedId) { alert('Seleccione una sucursal haciendo clic en una fila.'); return; }
        if (!confirm('¿Está seguro de eliminar esta sucursal?')) return;
        var form = document.getElementById('deleteForm');
        form.action = '{{ url("sucursales") }}/' + selectedId;
        form.submit();
    }

    function deleteResidualImages() {
        if (!selectedId) { alert('Seleccione una sucursal haciendo clic en una fila.'); return; }
        if (!confirm('¿Está seguro de eliminar las imágenes residuales de esta sucursal? Esta acción no se puede deshacer.')) return;
        var form = document.getElementById('imageForm');
        form.action = '{{ url("sucursales") }}/' + selectedId + '/imagenes-residuales';
        form.submit();
    }

    function changePerPage(val) {
        var url = new URL(window.location);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location = url;
    }

    document.getElementById('searchInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var url = new URL(window.location);
            if (this.value) {
                url.searchParams.set('buscar', this.value);
            } else {
                url.searchParams.delete('buscar');
            }
            url.searchParams.set('page', '1');
            window.location = url;
        }
    });
</script>
@endpush
@endsection
