@push('styles')
<style>
    .btn-catalog-action {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.4rem 0.7rem; border: none; border-radius: var(--radius);
        font-size: 0.78rem; font-weight: 600; color: #fff; white-space: nowrap;
        cursor: pointer; transition: var(--transition); text-decoration: none;
    }
    .btn-catalog-action:hover { opacity: 0.88; color: #fff; box-shadow: var(--shadow); }
    .btn-catalog-action svg { width: 16px; height: 16px; }

    .table-scroll-inv { max-height: 520px; overflow-y: auto; }
    .tbl-inv { table-layout: auto; width: 100%; }
    .tbl-inv td, .tbl-inv th { font-size: 0.78rem; padding: 0.45rem 0.6rem; }
    .tbl-inv thead th { position: sticky; top: 0; z-index: 10; background: var(--surface, #fff); box-shadow: 0 1px 0 var(--border, #dee2e6); }
    .cell-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px; }
    .text-right { text-align: right; }
    .text-danger { color: #e53935; }
    .font-weight-bold { font-weight: 700; }

    .sortable-th { padding: 0 !important; }
    .sort-link {
        display: flex; align-items: center; gap: 0.2rem;
        padding: 0.45rem 0.5rem; color: var(--text); text-decoration: none;
        font-size: 0.78rem; white-space: nowrap;
    }
    .sort-link:hover { background: #eef1f0; color: var(--text); }
    .sort-arrows { display: inline-flex; flex-direction: column; gap: 1px; flex-shrink: 0; }
    .sort-arrows svg { width: 7px; height: 4px; fill: #ccc; }
    .sort-arrows svg.active { fill: var(--primary); }
    .sort-active { background: #f0f4f2; }

    .inv-totales {
        display: flex; flex-wrap: wrap; gap: 0.75rem 1.5rem;
        padding: 0.75rem 1.25rem; background: #f8f9fa; border-top: 1px solid var(--border);
        font-size: 0.8rem;
    }
    .inv-total-item { display: flex; gap: 0.3rem; align-items: center; }
    .inv-total-label { color: var(--text-secondary); }
    .inv-total-value { font-weight: 600; }

    /* Diferencias row coloring */
    .row-negative { background: #ffebee !important; }
    .row-zero { background: #e8f5e9 !important; }
    .row-positive { background: #fff8e1 !important; }
</style>
@endpush
