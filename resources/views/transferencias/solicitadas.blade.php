@extends('layouts.app')
@section('title', 'Órdenes de Transferencia Solicitadas')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        Ordenes de transferencia solicitadas
    </h2>
    <div class="page-header-actions">
        <a href="{{ route('transferencias.nueva') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nueva Solicitud
        </a>
    </div>
</div>

{{-- Info banner --}}
<div style="background:#fff3cd; border:1px solid #ffc107; border-radius:var(--radius); padding:0.65rem 1rem; margin-bottom:1rem; font-size:0.82rem; color:#856404;">
    Aquí puedes gestionar las órdenes de transferencia solicitadas de esta sucursal a otras.
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        {{-- Action buttons + export --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1.25rem; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:0.5rem;">
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <button type="button" class="btn btn-catalog-action" style="background:#607D8B;" onclick="window.location.reload()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Actualizar tabla
                </button>
                <button type="button" class="btn btn-catalog-action" style="background:#2196F3;" onclick="verDetalles()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Ver detalles
                </button>
            </div>
            <a href="{{ route('transferencias.solicitadas.export', request()->query()) }}" class="btn btn-catalog-action" style="background:#4CAF50;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
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
                @if(request()->hasAny(['buscar', 'estatus', 'sucursal_id']))
                    <a href="{{ route('transferencias.solicitadas') }}" class="btn btn-ghost btn-sm">Limpiar filtros</a>
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
            <table class="tbl-sol" id="tblSolicitadas">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => 'id', 'label' => 'ID'],
                                ['key' => 'n_orden', 'label' => 'Número de Orden'],
                                ['key' => 'estatus', 'label' => 'Estatus'],
                                ['key' => 'motivo', 'label' => 'Motivo'],
                                ['key' => null, 'label' => 'Sucursal a la que se Solicita'],
                                ['key' => null, 'label' => 'Solicitado por'],
                                ['key' => null, 'label' => 'Autorizado por'],
                                ['key' => null, 'label' => 'Surtido por'],
                                ['key' => null, 'label' => 'Cancelado por'],
                                ['key' => 'created_at', 'label' => 'Fecha y Hora de Solicitud'],
                                ['key' => 'fecha_hora_surtido', 'label' => 'Fecha y Hora de Surtido'],
                                ['key' => 'fecha_hora_cancelacion', 'label' => 'Fecha y Hora de Cancelación'],
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
                        $sucNombre = '';
                        if ($t->sucursalOrigen) {
                            $sucNombre = ($t->sucursalOrigen->codigo ? $t->sucursalOrigen->codigo . '-' : '') . $t->sucursalOrigen->nombre;
                        }
                    @endphp
                    <tr data-id="{{ $t->id }}" onclick="selectRow(this)" class="clickable-row">
                        <td>{{ $t->id }}</td>
                        <td>{{ $t->n_orden }}</td>
                        <td><span class="badge-status {{ $statusClass }}">{{ $t->estatus }}</span></td>
                        <td>{{ $t->motivo ?? '' }}</td>
                        <td class="cell-truncate" style="max-width:200px;">{{ $sucNombre }}</td>
                        <td>{{ $t->usuario->nombres ?? '' }}</td>
                        <td>{{ $t->autorizador->nombres ?? '' }}</td>
                        <td>{{ $t->surtidor->nombres ?? '' }}</td>
                        <td>{{ $t->cancelador->nombres ?? '' }}</td>
                        <td class="cell-date">{{ $t->created_at?->format('Y-m-d H:i:s') ?? '' }}</td>
                        <td class="cell-date">{{ $t->fecha_hora_surtido?->format('Y-m-d H:i:s') ?? '' }}</td>
                        <td class="cell-date">{{ $t->fecha_hora_cancelacion?->format('Y-m-d H:i:s') ?? '' }}</td>
                        <td class="cell-truncate" style="max-width:150px;">{{ $t->comentarios ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            <div>No se encontraron órdenes de transferencia solicitadas</div>
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

{{-- Detail modal --}}
<div id="detailModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeDetailModal()">
    <div class="modal-content" style="max-width:650px;">
        <div class="modal-header">
            <h3 style="margin:0; font-size:1rem;">Detalle de Transferencia</h3>
            <button onclick="closeDetailModal()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--text-secondary);">&times;</button>
        </div>
        <div class="modal-body" id="detailBody" style="padding:1rem;">
            <!-- Filled by JS -->
        </div>
        <div class="modal-footer" id="detailFooter" style="padding:0.75rem 1rem; border-top:1px solid var(--border); display:flex; gap:0.5rem; justify-content:flex-end;">
            <!-- Action buttons filled by JS -->
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

    .clickable-row { cursor: pointer; }
    .clickable-row:hover { background: #e8f0ed !important; }
    .clickable-row.selected-row { background: #c8e6c9 !important; }

    .modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); z-index: 1000;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-content {
        background: #fff; border-radius: var(--radius); width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        max-height: 80vh; overflow-y: auto;
    }
    .modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 0.75rem 1rem; border-bottom: 1px solid var(--border);
    }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .detail-item label { font-size: 0.72rem; color: var(--text-secondary); display: block; margin-bottom: 0.15rem; }
    .detail-item span { font-size: 0.82rem; font-weight: 500; }

    .btn-action-sm {
        padding: 0.35rem 0.7rem; border: none; border-radius: var(--radius);
        font-size: 0.76rem; font-weight: 600; color: #fff; cursor: pointer;
    }
    .btn-action-sm:hover { opacity: 0.88; }
</style>
@endpush

@push('scripts')
<script>
    var selectedId = null;
    var traspasoData = @json($traspasos->items());

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

    function selectRow(tr) {
        document.querySelectorAll('.clickable-row').forEach(function(r) { r.classList.remove('selected-row'); });
        tr.classList.add('selected-row');
        selectedId = tr.getAttribute('data-id');
    }

    function verDetalles() {
        if (!selectedId) {
            alert('Selecciona una fila de la tabla primero.');
            return;
        }

        var t = null;
        for (var i = 0; i < traspasoData.length; i++) {
            if (String(traspasoData[i].id) === String(selectedId)) {
                t = traspasoData[i];
                break;
            }
        }
        if (!t) return;

        var sucNombre = '';
        if (t.sucursal_origen) {
            sucNombre = (t.sucursal_origen.codigo ? t.sucursal_origen.codigo + '-' : '') + t.sucursal_origen.nombre;
        }
        var sucDestino = '';
        if (t.sucursal_destino) {
            sucDestino = (t.sucursal_destino.codigo ? t.sucursal_destino.codigo + '-' : '') + t.sucursal_destino.nombre;
        }

        var html = '<div class="detail-grid">' +
            detailItem('ID', t.id) +
            detailItem('N\u00famero de Orden', t.n_orden || '-') +
            detailItem('Estatus', '<span class="badge-status badge-status-' + statusCls(t.estatus) + '">' + esc(t.estatus) + '</span>') +
            detailItem('Motivo', t.motivo || '-') +
            detailItem('Sucursal Origen', sucNombre || '-') +
            detailItem('Sucursal Destino', sucDestino || '-') +
            detailItem('Solicitado por', t.usuario ? t.usuario.nombres : '-') +
            detailItem('Autorizado por', t.autorizador ? t.autorizador.nombres : '-') +
            detailItem('Surtido por', t.surtidor ? t.surtidor.nombres : '-') +
            detailItem('Cancelado por', t.cancelador ? t.cancelador.nombres : '-') +
            detailItem('Fecha Solicitud', formatDate(t.created_at)) +
            detailItem('Fecha Surtido', formatDate(t.fecha_hora_surtido)) +
            detailItem('Fecha Cancelaci\u00f3n', formatDate(t.fecha_hora_cancelacion)) +
            detailItem('Comentarios', t.comentarios || '-') +
            '</div>';

        document.getElementById('detailBody').innerHTML = html;

        // Action buttons
        var footer = '';
        if (t.estatus === 'Pendiente') {
            footer += '<form method="POST" action="/transferencias/' + t.id + '/autorizar" style="display:inline;">@csrf<button type="submit" class="btn-action-sm" style="background:#2196F3;">Autorizar</button></form>';
            footer += '<form method="POST" action="/transferencias/' + t.id + '/surtir" style="display:inline;">@csrf<button type="submit" class="btn-action-sm" style="background:#4CAF50;">Surtir</button></form>';
            footer += '<form method="POST" action="/transferencias/' + t.id + '/cancelar" style="display:inline;" onsubmit="return confirm(\'¿Cancelar esta transferencia?\')">@csrf<button type="submit" class="btn-action-sm" style="background:#f44336;">Cancelar</button></form>';
        } else if (t.estatus === 'Autorizada') {
            footer += '<form method="POST" action="/transferencias/' + t.id + '/surtir" style="display:inline;">@csrf<button type="submit" class="btn-action-sm" style="background:#4CAF50;">Surtir</button></form>';
            footer += '<form method="POST" action="/transferencias/' + t.id + '/cancelar" style="display:inline;" onsubmit="return confirm(\'¿Cancelar esta transferencia?\')">@csrf<button type="submit" class="btn-action-sm" style="background:#f44336;">Cancelar</button></form>';
        }
        footer += '<button onclick="closeDetailModal()" class="btn-action-sm" style="background:#78909C;">Cerrar</button>';
        document.getElementById('detailFooter').innerHTML = footer;

        document.getElementById('detailModal').style.display = 'flex';
    }

    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    function detailItem(label, value) {
        return '<div class="detail-item"><label>' + esc(label) + '</label><span>' + value + '</span></div>';
    }

    function statusCls(s) {
        if (s === 'Pendiente') return 'pending';
        if (s === 'Autorizada') return 'authorized';
        if (s === 'Surtida') return 'fulfilled';
        if (s === 'Cancelada') return 'cancelled';
        return 'pending';
    }

    function formatDate(d) {
        if (!d) return '-';
        return d.replace('T', ' ').substring(0, 19);
    }

    function esc(str) {
        if (!str && str !== 0) return '';
        var div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    // Keyboard shortcut: Escape closes modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDetailModal();
    });
</script>
@endpush
@endsection
