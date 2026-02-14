@extends('layouts.app')
@section('title', 'Catálogo de Activos Fijos')

@section('content')
<div class="page-header">
    <h2>Catálogo de Activos Fijos</h2>
    <div class="page-header-actions">
        <a href="{{ route('activo-fijo-productos.import.form') }}" class="btn btn-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Importar Excel
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span>{{ number_format($productos->total()) }} activos fijos</span>
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
                    <select name="inventario_id" class="form-control" style="width:auto; min-width:220px;">
                        <option value="">Todas las sesiones</option>
                        @foreach($sesiones as $ses)
                            <option value="{{ $ses->id }}" {{ request('inventario_id') == $ses->id ? 'selected' : '' }}>
                                {{ $ses->empresa->nombre ?? '' }} - {{ $ses->sucursal->nombre ?? $ses->nombre ?? 'Sesión #'.$ses->id }}
                            </option>
                        @endforeach
                    </select>
                    <div class="search-box" style="flex:1; max-width:300px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por código, descripción, marca..." value="{{ request('buscar') }}">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request()->hasAny(['buscar', 'empresa_id', 'inventario_id']))
                        <a href="{{ route('activo-fijo-productos.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Código 1</th>
                        <th>Código 2</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Serie</th>
                        <th>Estado</th>
                        <th style="width:80px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr>
                        <td><span class="badge badge-gray">{{ $producto->codigo_1 }}</span></td>
                        <td>{{ $producto->codigo_2 ?: '-' }}</td>
                        <td style="font-weight:500; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $producto->descripcion }}</td>
                        <td>{{ $producto->categoria_2 ?: $producto->categoria_1 ?: '-' }}</td>
                        <td>{{ $producto->marca ?: '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $producto->n_serie ?: '-' }}</td>
                        <td>
                            @if($producto->no_encontrado)
                                <span class="badge badge-danger">No encontrado</span>
                            @elseif($producto->traspasado)
                                <span class="badge badge-warning">Traspasado</span>
                            @elseif($producto->forzado)
                                <span class="badge badge-info">Forzado</span>
                            @else
                                <span class="badge badge-success">Activo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('activo-fijo-productos.show', $producto) }}" class="btn btn-sm btn-outline" title="Ver detalle">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                            <div>No se encontraron activos fijos</div>
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
