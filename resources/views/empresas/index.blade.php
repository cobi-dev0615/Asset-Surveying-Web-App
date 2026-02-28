@extends('layouts.app')
@section('title', 'Catálogo de Empresas')

@section('content')
<div class="page-header">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;vertical-align:middle;margin-right:0.3rem;opacity:0.5;"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        Catálogo de empresas
    </h2>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        {{-- Action buttons --}}
        <div style="display:flex; align-items:center; justify-content:flex-end; padding:0.75rem 1.25rem; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:0.5rem;">
            <button type="button" class="btn btn-catalog-action" style="background:#607D8B;" onclick="window.location.reload()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Actualizar tabla
            </button>
            @if(Auth::user()->esSupervisor())
            <a href="{{ route('empresas.create') }}" class="btn btn-catalog-action" style="background:#2196F3;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Agregar
            </a>
            <button type="button" class="btn btn-catalog-action" style="background:#009688;" onclick="editarSeleccionada()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Editar
            </button>
            <button type="button" class="btn btn-catalog-action" style="background:#9C27B0;" onclick="eliminarSeleccionada()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Eliminar
            </button>
            <button type="button" class="btn btn-catalog-action" style="background:#78909C;" onclick="abrirImagenesModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Reducir Imágenes
            </button>
            @endif
        </div>

        {{-- Search + export + entries --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1.25rem; flex-wrap:wrap; gap:0.75rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search" value="{{ request('buscar') }}" style="min-width:180px;">
                </div>
                @if(request()->hasAny(['buscar']))
                    <a href="{{ route('empresas.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                @endif
            </div>
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <a href="{{ route('empresas.export', request()->query()) }}" class="btn btn-catalog-action" style="background:#4CAF50; font-size:0.76rem; padding:0.4rem 0.7rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                    Exportar a Excel
                </a>
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
        </div>

        {{-- Table --}}
        <div class="table-wrapper">
            <table class="tbl-empresas" id="tblEmpresas">
                <thead>
                    <tr>
                        @php
                            $columns = [
                                ['key' => 'codigo', 'label' => 'Código'],
                                ['key' => 'nombre', 'label' => 'Nombre'],
                                ['key' => 'sucursales_count', 'label' => 'Sucursales'],
                                ['key' => 'created_at', 'label' => 'Fecha Creación'],
                            ];
                        @endphp
                        @foreach($columns as $col)
                        <th class="sortable-th {{ $sort === $col['key'] ? 'sort-active' : '' }}">
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
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($empresas as $empresa)
                    <tr data-id="{{ $empresa->id }}" onclick="selectRow(this)" class="clickable-row">
                        <td style="font-weight:500;">{{ $empresa->codigo }}</td>
                        <td>{{ $empresa->nombre }}</td>
                        <td>{{ $empresa->sucursales_count }}</td>
                        <td class="cell-date">{{ $empresa->created_at?->format('Y-m-d H:i:s') ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            <div>No se encontraron empresas</div>
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
            Showing {{ $empresas->firstItem() ?? 0 }} to {{ $empresas->lastItem() ?? 0 }} of {{ number_format($empresas->total()) }} entries
        </span>
        @if($empresas->hasPages())
            {{ $empresas->links() }}
        @endif
    </div>
</div>

{{-- Hidden delete form --}}
<form id="deleteForm" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

{{-- Image management modal --}}
<div id="imgModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)cerrarImagenesModal()">
    <div class="modal-content" style="max-width:600px;">
        <div class="modal-header">
            <h3 style="margin:0; font-size:1rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;vertical-align:middle;margin-right:0.3rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Gestión de Imágenes — <span id="imgEmpresaNombre"></span>
            </h3>
            <button onclick="cerrarImagenesModal()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--text-secondary);">&times;</button>
        </div>
        <div class="modal-body" style="padding:1rem;">
            {{-- Stats section --}}
            <div id="imgStats" style="background:#f5f7f6; border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1rem;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
                    <div><span style="font-size:0.72rem; color:var(--text-secondary);">Referencias en BD</span><br><strong id="statRefs">—</strong></div>
                    <div><span style="font-size:0.72rem; color:var(--text-secondary);">Archivos en disco</span><br><strong id="statFiles">—</strong></div>
                    <div><span style="font-size:0.72rem; color:var(--text-secondary);">Tamaño total</span><br><strong id="statSize">—</strong></div>
                    <div><span style="font-size:0.72rem; color:var(--text-secondary);">Estado</span><br><strong id="statStatus" style="color:#4CAF50;">Listo</strong></div>
                </div>
            </div>

            {{-- 4 feature cards --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                {{-- 1. Reducir calidad --}}
                <div class="img-feature-card">
                    <div class="img-feature-icon" style="background:#FF9800;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </div>
                    <h4>Reducir Calidad</h4>
                    <p>Comprime las imágenes JPEG para reducir el tamaño en disco.</p>
                    <div style="display:flex; align-items:center; gap:0.4rem; margin-bottom:0.5rem;">
                        <label style="font-size:0.72rem; white-space:nowrap;">Calidad:</label>
                        <select id="compressQuality" class="form-control" style="font-size:0.76rem; padding:0.2rem 0.4rem; width:auto;">
                            <option value="80">80% (Leve)</option>
                            <option value="60" selected>60% (Media)</option>
                            <option value="40">40% (Alta)</option>
                        </select>
                    </div>
                    <button onclick="reducirCalidad()" class="img-feature-btn" style="background:#FF9800;">Comprimir</button>
                </div>

                {{-- 2. Redimensionar --}}
                <div class="img-feature-card">
                    <div class="img-feature-icon" style="background:#2196F3;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                    </div>
                    <h4>Redimensionar</h4>
                    <p>Reduce las dimensiones de imágenes grandes al máximo especificado.</p>
                    <div style="display:flex; align-items:center; gap:0.4rem; margin-bottom:0.5rem;">
                        <label style="font-size:0.72rem; white-space:nowrap;">Máximo:</label>
                        <select id="maxDimension" class="form-control" style="font-size:0.76rem; padding:0.2rem 0.4rem; width:auto;">
                            <option value="2048">2048px</option>
                            <option value="1024" selected>1024px</option>
                            <option value="800">800px</option>
                            <option value="512">512px</option>
                        </select>
                    </div>
                    <button onclick="redimensionar()" class="img-feature-btn" style="background:#2196F3;">Redimensionar</button>
                </div>

                {{-- 3. Estadísticas --}}
                <div class="img-feature-card">
                    <div class="img-feature-icon" style="background:#4CAF50;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <h4>Actualizar Estadísticas</h4>
                    <p>Recalcula el conteo y tamaño de las imágenes de esta empresa.</p>
                    <button onclick="cargarStats()" class="img-feature-btn" style="background:#4CAF50;">Actualizar</button>
                </div>

                {{-- 4. Eliminar imágenes --}}
                <div class="img-feature-card">
                    <div class="img-feature-icon" style="background:#f44336;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </div>
                    <h4>Eliminar Imágenes</h4>
                    <p>Elimina todas las imágenes de activos de esta empresa permanentemente.</p>
                    <button onclick="eliminarImagenes()" class="img-feature-btn" style="background:#f44336;">Eliminar Todas</button>
                </div>
            </div>

            {{-- Result area --}}
            <div id="imgResult" style="display:none; margin-top:0.75rem; padding:0.6rem 0.85rem; border-radius:var(--radius); font-size:0.82rem;"></div>
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

    .tbl-empresas { table-layout: auto; width: 100%; }
    .tbl-empresas td, .tbl-empresas th { font-size: 0.82rem; padding: 0.55rem 0.75rem; }
    .cell-date { white-space: nowrap; font-size: 0.8rem; color: #4CAF50; }

    .sortable-th { padding: 0 !important; }
    .sort-link {
        display: flex; align-items: center; gap: 0.25rem;
        padding: 0.55rem 0.75rem; color: var(--text); text-decoration: none; line-height: 1.2;
        font-size: 0.82rem; white-space: nowrap; font-weight: 600; text-transform: uppercase;
    }
    .sort-link:hover { background: #eef1f0; color: var(--text); }
    .sort-arrows { display: inline-flex; flex-direction: column; gap: 1px; flex-shrink: 0; }
    .sort-arrows svg { width: 7px; height: 4px; fill: #ccc; }
    .sort-arrows svg.active { fill: var(--primary); }
    .sort-active { background: #f0f4f2; }

    .clickable-row { cursor: pointer; }
    .clickable-row:hover { background: #e8f0ed !important; }
    .clickable-row.selected-row { background: #c8e6c9 !important; }

    /* Modal */
    .modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); z-index: 1000;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-content {
        background: #fff; border-radius: var(--radius); width: 90%;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-height: 85vh; overflow-y: auto;
    }
    .modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 0.75rem 1rem; border-bottom: 1px solid var(--border);
    }

    /* Image feature cards */
    .img-feature-card {
        border: 1px solid var(--border); border-radius: var(--radius);
        padding: 0.85rem; text-align: center;
    }
    .img-feature-card h4 { font-size: 0.82rem; margin: 0.4rem 0 0.25rem; }
    .img-feature-card p { font-size: 0.72rem; color: var(--text-secondary); margin: 0 0 0.5rem; line-height: 1.35; }
    .img-feature-icon {
        width: 38px; height: 38px; border-radius: 50%; display: inline-flex;
        align-items: center; justify-content: center; margin: 0 auto;
    }
    .img-feature-icon svg { width: 18px; height: 18px; }
    .img-feature-btn {
        display: inline-block; padding: 0.35rem 0.8rem; border: none;
        border-radius: var(--radius); font-size: 0.76rem; font-weight: 600;
        color: #fff; cursor: pointer; width: 100%;
    }
    .img-feature-btn:hover { opacity: 0.88; }
    .img-feature-btn:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
@endpush

@push('scripts')
<script>
    var selectedId = null;

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

    function editarSeleccionada() {
        if (!selectedId) {
            alert('Selecciona una empresa de la tabla primero.');
            return;
        }
        window.location.href = '/empresas/' + selectedId + '/edit';
    }

    function eliminarSeleccionada() {
        if (!selectedId) {
            alert('Selecciona una empresa de la tabla primero.');
            return;
        }
        if (!confirm('¿Estás seguro de que deseas eliminar esta empresa?')) return;

        var form = document.getElementById('deleteForm');
        form.action = '/empresas/' + selectedId;
        form.submit();
    }

    // ─── Image management ───
    var csrfToken = '{{ csrf_token() }}';

    function abrirImagenesModal() {
        if (!selectedId) {
            alert('Selecciona una empresa de la tabla primero.');
            return;
        }
        var row = document.querySelector('.clickable-row.selected-row');
        var nombre = row ? row.children[1].textContent : '';
        document.getElementById('imgEmpresaNombre').textContent = nombre;
        document.getElementById('imgResult').style.display = 'none';
        document.getElementById('imgModal').style.display = 'flex';
        cargarStats();
    }

    function cerrarImagenesModal() {
        document.getElementById('imgModal').style.display = 'none';
    }

    function cargarStats() {
        setStatus('Cargando...');
        fetch('/empresas/' + selectedId + '/imagenes/stats')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('statRefs').textContent = data.total_referencias;
                document.getElementById('statFiles').textContent = data.archivos_encontrados;
                document.getElementById('statSize').textContent = data.tamano_legible;
                setStatus('Listo');
            })
            .catch(function() { setStatus('Error al cargar'); });
    }

    function reducirCalidad() {
        if (!confirm('¿Comprimir las imágenes de esta empresa? Esta acción no se puede deshacer.')) return;
        var quality = document.getElementById('compressQuality').value;
        setStatus('Comprimiendo...');
        disableButtons(true);

        fetch('/empresas/' + selectedId + '/imagenes/reducir', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ quality: parseInt(quality) })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showResult('success', 'Compresión completada: ' + data.procesadas + ' imágenes procesadas, ' + data.espacio_legible + ' liberados.');
            setStatus('Listo');
            disableButtons(false);
            cargarStats();
        })
        .catch(function() {
            showResult('error', 'Error al comprimir las imágenes.');
            setStatus('Error');
            disableButtons(false);
        });
    }

    function redimensionar() {
        if (!confirm('¿Redimensionar las imágenes de esta empresa? Esta acción no se puede deshacer.')) return;
        var maxDim = document.getElementById('maxDimension').value;
        setStatus('Redimensionando...');
        disableButtons(true);

        fetch('/empresas/' + selectedId + '/imagenes/redimensionar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ max_dimension: parseInt(maxDim) })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showResult('success', 'Redimensionado completado: ' + data.procesadas + ' imágenes procesadas, ' + data.espacio_legible + ' liberados.');
            setStatus('Listo');
            disableButtons(false);
            cargarStats();
        })
        .catch(function() {
            showResult('error', 'Error al redimensionar las imágenes.');
            setStatus('Error');
            disableButtons(false);
        });
    }

    function eliminarImagenes() {
        if (!confirm('¿ELIMINAR TODAS las imágenes de esta empresa? Esta acción es PERMANENTE e IRREVERSIBLE.')) return;
        if (!confirm('¿Estás completamente seguro? Se eliminarán TODOS los archivos de imagen.')) return;
        setStatus('Eliminando...');
        disableButtons(true);

        fetch('/empresas/' + selectedId + '/imagenes/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showResult('success', 'Eliminación completada: ' + data.eliminadas + ' imágenes eliminadas, ' + data.espacio_legible + ' liberados.');
            setStatus('Listo');
            disableButtons(false);
            cargarStats();
        })
        .catch(function() {
            showResult('error', 'Error al eliminar las imágenes.');
            setStatus('Error');
            disableButtons(false);
        });
    }

    function setStatus(text) {
        var el = document.getElementById('statStatus');
        el.textContent = text;
        el.style.color = text === 'Listo' ? '#4CAF50' : (text.includes('Error') ? '#f44336' : '#FF9800');
    }

    function disableButtons(disabled) {
        document.querySelectorAll('.img-feature-btn').forEach(function(btn) { btn.disabled = disabled; });
    }

    function showResult(type, msg) {
        var el = document.getElementById('imgResult');
        el.style.display = 'block';
        el.style.background = type === 'success' ? '#e8f5e9' : '#ffebee';
        el.style.color = type === 'success' ? '#2e7d32' : '#c62828';
        el.style.border = '1px solid ' + (type === 'success' ? '#a5d6a7' : '#ef9a9a');
        el.textContent = msg;
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') cerrarImagenesModal();
    });
</script>
@endpush
@endsection
