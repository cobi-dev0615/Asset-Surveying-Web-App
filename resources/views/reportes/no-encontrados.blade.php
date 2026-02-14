@extends('layouts.app')
@section('title', 'Activos No Encontrados')

@section('content')
<div class="page-header">
    <h2>Activos No Encontrados</h2>
</div>

<div class="card">
    <div class="card-header">
        <span>{{ $registros->total() }} activos no encontrados</span>
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
                    <select name="inventario_id" class="form-control" style="width:auto; min-width:180px;">
                        <option value="">Todas las sesiones</option>
                        @foreach($sesiones as $ses)
                            <option value="{{ $ses->id }}" {{ request('inventario_id') == $ses->id ? 'selected' : '' }}>Sesión #{{ $ses->id }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request()->hasAny(['empresa_id', 'inventario_id']))
                        <a href="{{ route('reportes.no-encontrados') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sesión</th>
                        <th>Empresa</th>
                        <th>Sucursal</th>
                        <th>ID Activo</th>
                        <th>Usuario</th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $reg)
                    <tr>
                        <td>{{ $reg->id }}</td>
                        <td><span class="badge badge-primary">#{{ $reg->inventario_id }}</span></td>
                        <td>{{ $reg->inventario->empresa->nombre ?? '-' }}</td>
                        <td>{{ $reg->inventario->sucursal->nombre ?? '-' }}</td>
                        <td style="font-weight:500;">{{ $reg->activo }}</td>
                        <td>{{ $reg->usuario->nombres ?? '-' }}</td>
                        <td style="font-size:0.85rem;">{{ $reg->latitud != 0 ? $reg->latitud : '-' }}</td>
                        <td style="font-size:0.85rem;">{{ $reg->longitud != 0 ? $reg->longitud : '-' }}</td>
                        <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $reg->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            <div>No se encontraron registros</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($registros->hasPages())
    <div class="card-footer">
        {{ $registros->links() }}
    </div>
    @endif
</div>
@endsection
