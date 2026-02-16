@extends('layouts.app')
@section('title', 'Sucursales')

@section('content')
<div class="page-header">
    <h2>Sucursales</h2>
    @if(Auth::user()->esAdmin())
    <div class="page-header-actions">
        <a href="{{ route('sucursales.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nueva Sucursal
        </a>
    </div>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <span>{{ $sucursales->total() }} sucursales</span>
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
                    <div class="search-box" style="flex:1; max-width:280px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar sucursal..." value="{{ request('buscar') }}">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request()->hasAny(['buscar', 'empresa_id']))
                        <a href="{{ route('sucursales.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Ciudad</th>
                        <th>Dirección</th>
                        @if(Auth::user()->esAdmin())<th style="width:120px;">Acciones</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($sucursales as $sucursal)
                    <tr>
                        <td><span class="badge badge-gray">{{ $sucursal->codigo }}</span></td>
                        <td style="font-weight:500;">{{ $sucursal->nombre }}</td>
                        <td>{{ $sucursal->empresa->nombre ?? '-' }}</td>
                        <td>{{ $sucursal->ciudad ?? '-' }}</td>
                        <td style="max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $sucursal->direccion ?? '-' }}</td>
                        @if(Auth::user()->esAdmin())
                        <td>
                            <div style="display:flex; gap:0.25rem;">
                                <a href="{{ route('sucursales.edit', $sucursal) }}" class="btn btn-sm btn-outline" title="Editar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('sucursales.destroy', $sucursal) }}" onsubmit="return confirm('¿Eliminar esta sucursal?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="color:var(--danger);" title="Eliminar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <div>No se encontraron sucursales</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sucursales->hasPages())
    <div class="card-footer">
        {{ $sucursales->links() }}
    </div>
    @endif
</div>
@endsection
