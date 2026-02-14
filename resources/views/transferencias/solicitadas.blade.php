@extends('layouts.app')
@section('title', 'Órdenes Solicitadas')

@section('content')
<div class="page-header">
    <h2>Órdenes de Transferencia Solicitadas</h2>
    <div class="page-header-actions">
        <a href="{{ route('transferencias.nueva') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nueva Solicitud
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span>{{ $traspasos->total() }} transferencias solicitadas</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="toolbar" style="padding:1rem 1.5rem;">
            <div class="toolbar-left">
                <form method="GET" style="display:flex; gap:0.5rem; flex:1; flex-wrap:wrap;">
                    <div class="search-box" style="flex:1; max-width:220px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por ID de activo..." value="{{ request('buscar') }}">
                    </div>
                    <select name="sucursal_id" class="form-control" style="width:auto; min-width:180px;">
                        <option value="">Todas las sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request()->hasAny(['buscar', 'sucursal_id']))
                        <a href="{{ route('transferencias.solicitadas') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ID Activo</th>
                        <th>Sucursal Origen</th>
                        <th>Sucursal Destino</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($traspasos as $traspaso)
                    <tr>
                        <td>{{ $traspaso->id }}</td>
                        <td style="font-weight:500;">{{ $traspaso->activo }}</td>
                        <td>
                            <span class="badge badge-danger">{{ $traspaso->sucursalOrigen->nombre ?? '-' }}</span>
                        </td>
                        <td>
                            <svg viewBox="0 0 24 24" fill="none" stroke="var(--text-light)" stroke-width="2" style="width:16px; height:16px; display:inline; vertical-align:middle;"><polyline points="5 12 19 12"/><polyline points="12 5 19 12 12 19"/></svg>
                            <span class="badge badge-success">{{ $traspaso->sucursalDestino->nombre ?? '-' }}</span>
                        </td>
                        <td>{{ $traspaso->usuario->nombres ?? '-' }}</td>
                        <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $traspaso->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            <div>No se encontraron transferencias solicitadas</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($traspasos->hasPages())
    <div class="card-footer">
        {{ $traspasos->links() }}
    </div>
    @endif
</div>
@endsection
