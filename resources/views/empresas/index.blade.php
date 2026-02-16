@extends('layouts.app')
@section('title', 'Empresas')

@section('content')
<div class="page-header">
    <h2>Empresas</h2>
    @if(Auth::user()->esAdmin())
    <div class="page-header-actions">
        <a href="{{ route('empresas.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nueva Empresa
        </a>
    </div>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <span>{{ $empresas->total() }} empresas</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="toolbar" style="padding:1rem 1.5rem;">
            <div class="toolbar-left">
                <form method="GET" style="display:flex; gap:0.5rem; flex:1;">
                    <div class="search-box" style="flex:1; max-width:320px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar empresa..." value="{{ request('buscar') }}">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Buscar</button>
                    @if(request('buscar'))
                        <a href="{{ route('empresas.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
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
                        <th>Sucursales</th>
                        <th>Usuarios</th>
                        <th>Productos</th>
                        <th>Creado</th>
                        @if(Auth::user()->esAdmin())<th style="width:120px;">Acciones</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($empresas as $empresa)
                    <tr>
                        <td><span class="badge badge-gray">{{ $empresa->codigo }}</span></td>
                        <td style="font-weight:500;">{{ $empresa->nombre }}</td>
                        <td>{{ $empresa->sucursales_count }}</td>
                        <td>{{ $empresa->users_count }}</td>
                        <td>{{ number_format($empresa->productos_count) }}</td>
                        <td style="color:var(--text-secondary); font-size:0.8rem;">{{ $empresa->created_at->format('d/m/Y') }}</td>
                        @if(Auth::user()->esAdmin())
                        <td>
                            <div style="display:flex; gap:0.25rem;">
                                <a href="{{ route('empresas.edit', $empresa) }}" class="btn btn-sm btn-outline" title="Editar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('empresas.destroy', $empresa) }}" onsubmit="return confirm('¿Eliminar esta empresa?')">
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
                        <td colspan="7" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21h18M3 7v14m6-14v14m6-14v14m6-14v14M6 3h12l3 4H3l3-4z"/></svg>
                            <div>No se encontraron empresas</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($empresas->hasPages())
    <div class="card-footer">
        {{ $empresas->links() }}
    </div>
    @endif
</div>
@endsection
