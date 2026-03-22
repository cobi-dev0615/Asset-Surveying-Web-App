@extends('layouts.app')
@section('title', 'Órdenes de Transferencia Recibidas')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        Ordenes de transferencia recibidas
    </h2>
</div>

{{-- Info banner --}}
<div style="background:#fff3cd; border:1px solid #ffc107; border-radius:var(--radius); padding:0.65rem 1rem; margin-bottom:1rem; font-size:0.82rem; color:#856404;">
    Aquí puedes ver las órdenes de transferencia recibidas de otras sucursales.
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        {{-- Action buttons --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1.25rem; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:0.5rem;">
            <button type="button" class="btn btn-catalog-action" style="background:#607D8B;" onclick="window.location.reload()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Actualizar tabla
            </button>
        </div>

        {{-- Search + entries --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1.25rem; flex-wrap:wrap; gap:0.75rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search" value="{{ request('buscar') }}" style="min-width:180px;">
                </div>
                @if(request()->hasAny(['buscar', 'sucursal_id']))
                    <a href="{{ route('transferencias.recibidas') }}" class="btn btn-ghost btn-sm">Limpiar filtros</a>
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
            <table class="tbl-sol">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => 'id', 'label' => 'ID'],
                                ['key' => 'n_orden', 'label' => 'Número de Orden'],
                                ['key' => 'estatus', 'label' => 'Estatus'],
                                ['key' => 'motivo', 'label' => 'Motivo'],
                                ['key' => null, 'label' => 'Sucursal de Origen'],
                                ['key' => null, 'label' => 'Sucursal de Destino'],
                                ['key' => null, 'label' => 'Solicitado por'],
                                ['key' => null, 'label' => 'Autorizado por'],
                                ['key' => null, 'label' => 'Surtido por'],
                                ['key' => 'created_at', 'label' => 'Fecha y Hora de Solicitud'],
                                ['key' => 'fecha_hora_surtido', 'label' => 'Fecha y Hora de Surtido'],
                                ['key' => null, 'label' => 'Comentarios'],
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
                    @forelse($traspasos as $t)
                    @php
                        $statusClass = match($t->estatus) {
                            'Pendiente' => 'badge-status-pending',
                            'Autorizada' => 'badge-status-authorized',
                            'Surtida' => 'badge-status-fulfilled',
                            'Cancelada' => 'badge-status-cancelled',
                            default => 'badge-status-pending',
                        };
                        $sucOrigen = $t->sucursalOrigen ? (($t->sucursalOrigen->codigo ? $t->sucursalOrigen->codigo . '-' : '') . $t->sucursalOrigen->nombre) : '';
                        $sucDestino = $t->sucursalDestino ? (($t->sucursalDestino->codigo ? $t->sucursalDestino->codigo . '-' : '') . $t->sucursalDestino->nombre) : '';
                    @endphp
                    <tr>
                        <td>{{ $t->id }}</td>
                        <td>{{ $t->n_orden }}</td>
                        <td><span class="badge-status {{ $statusClass }}">{{ $t->estatus }}</span></td>
                        <td>{{ $t->motivo ?? '' }}</td>
                        <td class="cell-truncate" style="max-width:180px;">{{ $sucOrigen }}</td>
                        <td class="cell-truncate" style="max-width:180px;">{{ $sucDestino }}</td>
                        <td>{{ $t->usuario->nombres ?? '' }}</td>
                        <td>{{ $t->autorizador->nombres ?? '' }}</td>
                        <td>{{ $t->surtidor->nombres ?? '' }}</td>
                        <td class="cell-date">{{ $t->created_at?->format('Y-m-d H:i:s') ?? '' }}</td>
                        <td class="cell-date">{{ $t->fecha_hora_surtido?->format('Y-m-d H:i:s') ?? '' }}</td>
                        <td class="cell-truncate" style="max-width:150px;">{{ $t->comentarios ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            <div>No se encontraron transferencias recibidas</div>
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
            Showing {{ $traspasos->firstItem() ?? 0 }} to {{ $traspasos->lastItem() ?? 0 }} of {{ number_format($traspasos->total()) }} entries
        </span>
        @if($traspasos->hasPages())
            {{ $traspasos->links() }}
        @endif
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
    .btn-catalog-action svg { width: 16px; height: 16px; }

    .tbl-sol { table-layout: auto; width: 100%; }
    .tbl-sol td, .tbl-sol th { font-size: 0.76rem; padding: 0.4rem 0.5rem; }
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
    .badge-status-pending { background: #ffc107; color: #333; }
    .badge-status-authorized { background: #2196F3; color: #fff; }
    .badge-status-fulfilled { background: #4CAF50; color: #fff; }
    .badge-status-cancelled { background: #f44336; color: #fff; }

    .tbl-sol tbody tr:hover { background: #e8f0ed !important; }
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
