<div class="card-footer" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
    <span style="font-size:0.8rem; color:var(--text-secondary);">
        Showing {{ $registros->firstItem() ?? 0 }} to {{ $registros->lastItem() ?? 0 }} of {{ number_format($registros->total()) }} entries
    </span>
    @if($registros->hasPages())
        {{ $registros->links() }}
    @endif
</div>

@if($totales)
<div class="inv-totales">
    @if(isset($totales->total_productos))
    <div class="inv-total-item">
        <span class="inv-total-label">Productos:</span>
        <span class="inv-total-value">{{ number_format($totales->total_productos ?? 0) }}</span>
    </div>
    @endif
    @if(isset($totales->conteo_total))
    <div class="inv-total-item">
        <span class="inv-total-label">Conteo total:</span>
        <span class="inv-total-value text-danger">{{ number_format($totales->conteo_total ?? 0, 2) }}</span>
    </div>
    @endif
    @if(isset($totales->valor_inventario))
    <div class="inv-total-item">
        <span class="inv-total-label">Valor inventario:</span>
        <span class="inv-total-value" style="color:var(--primary);">${{ number_format($totales->valor_inventario ?? 0, 2) }}</span>
    </div>
    @endif
    @if(isset($totales->total_registros))
    <div class="inv-total-item">
        <span class="inv-total-label">Registros:</span>
        <span class="inv-total-value">{{ number_format($totales->total_registros ?? 0) }}</span>
    </div>
    @endif
    @if(isset($totales->almacenes))
    <div class="inv-total-item">
        <span class="inv-total-label">Almacenes:</span>
        <span class="inv-total-value">{{ number_format($totales->almacenes ?? 0) }}</span>
    </div>
    @endif
    @if(isset($totales->ubicaciones))
    <div class="inv-total-item">
        <span class="inv-total-label">Ubicaciones:</span>
        <span class="inv-total-value">{{ number_format($totales->ubicaciones ?? 0) }}</span>
    </div>
    @endif
    {{-- Diferencias-specific totals --}}
    @if(isset($totales->inventario_real))
    <div class="inv-total-item">
        <span class="inv-total-label">Inventario real:</span>
        <span class="inv-total-value" style="color:var(--primary);">{{ number_format($totales->inventario_real ?? 0, 2) }}</span>
    </div>
    <div class="inv-total-item">
        <span class="inv-total-label">Inventario teorico:</span>
        <span class="inv-total-value">{{ number_format($totales->inventario_teorico ?? 0, 2) }}</span>
    </div>
    <div class="inv-total-item">
        <span class="inv-total-label">Diferencia:</span>
        <span class="inv-total-value text-danger font-weight-bold">{{ number_format($totales->diferencia ?? 0, 2) }}</span>
    </div>
    <div class="inv-total-item">
        <span class="inv-total-label">Valor real:</span>
        <span class="inv-total-value" style="color:var(--primary);">${{ number_format($totales->valor_real ?? 0, 2) }}</span>
    </div>
    <div class="inv-total-item">
        <span class="inv-total-label">Valor teorico:</span>
        <span class="inv-total-value">${{ number_format($totales->valor_teorico ?? 0, 2) }}</span>
    </div>
    <div class="inv-total-item">
        <span class="inv-total-label">Diferencia valor:</span>
        <span class="inv-total-value text-danger font-weight-bold">${{ number_format($totales->valor_diferencia ?? 0, 2) }}</span>
    </div>
    @endif
</div>
@endif
