@extends('layouts.app')
@section('title', 'Productos')

@section('content')
<div class="page-header">
    <h2>Productos</h2>
    <div class="page-header-actions">
        <a href="{{ route('productos.import.form') }}" class="btn btn-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Importar Excel
        </a>
        <a href="{{ route('productos.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Producto
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span>{{ number_format($productos->total()) }} productos</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="toolbar" style="padding:1rem 1.5rem;">
            <div class="toolbar-left">
                <form method="GET" style="display:flex; gap:0.5rem; flex:1; flex-wrap:wrap;">
                    <select name="empresa_id" class="form-control" style="width:auto; min-width:180px;">
                        <option value="">Todas las empresas</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->id }}" {{ request('empresa_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nombre }}</option>
                        @endforeach
                    </select>
                    <div class="search-box" style="flex:1; max-width:300px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por código, descripción, marca..." value="{{ request('buscar') }}">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request()->hasAny(['buscar', 'empresa_id']))
                        <a href="{{ route('productos.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Empresa</th>
                        <th>Marca</th>
                        <th>Categoría</th>
                        <th style="text-align:right;">P. Compra</th>
                        <th style="text-align:right;">P. Venta</th>
                        <th style="text-align:right;">Existencia</th>
                        <th style="width:100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr>
                        <td><span class="badge badge-gray">{{ $producto->codigo_1 }}</span></td>
                        <td style="font-weight:500; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $producto->descripcion }}</td>
                        <td>{{ $producto->empresa->nombre ?? '-' }}</td>
                        <td>{{ $producto->marca ?? '-' }}</td>
                        <td>{{ $producto->categoria ?? '-' }}</td>
                        <td style="text-align:right;">${{ number_format($producto->precio_compra, 2) }}</td>
                        <td style="text-align:right;">${{ number_format($producto->precio_venta, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($producto->cantidad_teorica, 0) }}</td>
                        <td>
                            <div style="display:flex; gap:0.25rem;">
                                <a href="{{ route('productos.edit', $producto) }}" class="btn btn-sm btn-outline" title="Editar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('productos.destroy', $producto) }}" onsubmit="return confirm('¿Eliminar este producto?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="color:var(--danger);">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            <div>No se encontraron productos</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($productos->hasPages())
    <div class="card-footer">
        {{ $productos->links() }}
    </div>
    @endif
</div>
@endsection
