<div class="card" style="margin-bottom:0; border-bottom:none; border-radius: var(--radius) var(--radius) 0 0;">
    <div class="card-body" style="padding:0;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.85rem 1.25rem; flex-wrap:wrap; gap:0.75rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.5rem; flex:1; flex-wrap:wrap;">
                <form method="GET" action="{{ route($routeName) }}" id="filterForm" style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="dir" value="{{ $dir }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    @if(request('inventario_id'))
                        <input type="hidden" name="inventario_id" value="{{ request('inventario_id') }}">
                    @endif
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar..." value="{{ request('buscar') }}" style="min-width:180px;">
                    </div>
                </form>
                <form method="GET" action="{{ route($routeName) }}" style="display:flex; gap:0.5rem; align-items:center;">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <select name="inventario_id" class="form-control" style="width:auto; min-width:180px; font-size:0.82rem;" onchange="this.form.submit()">
                        <option value="">Todas las sesiones</option>
                        @foreach($sesiones as $ses)
                            <option value="{{ $ses->id }}" {{ request('inventario_id') == $ses->id ? 'selected' : '' }}>{{ $ses->nombre }} (#{{ $ses->id }})</option>
                        @endforeach
                    </select>
                </form>
                @if(request()->hasAny(['buscar', 'inventario_id']))
                    <a href="{{ route($routeName, ['per_page' => $perPage]) }}" class="btn btn-ghost btn-sm">Limpiar filtros</a>
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
    </div>
</div>

@push('scripts')
<script>
function changePerPage(val) {
    var url = new URL(window.location);
    url.searchParams.set('per_page', val);
    url.searchParams.set('page', '1');
    window.location = url;
}
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
