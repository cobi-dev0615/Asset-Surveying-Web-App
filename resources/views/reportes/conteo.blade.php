@extends('layouts.app')
@section('title', 'Reporte de Conteo')

@section('content')
<div class="page-header">
    <h2>Reporte de Conteo</h2>
</div>

<div class="card">
    <div class="card-header">
        <span>{{ $registros->total() }} registros de activos</span>
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
                    @if(request()->hasAny(['empresa_id', 'inventario_id', 'sucursal_id']))
                        <a href="{{ route('reportes.conteo') }}" class="btn btn-ghost btn-sm">Limpiar</a>
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
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Ubicación</th>
                        <th>Usuario</th>
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
                        <td style="font-weight:500;">{{ $reg->codigo_1 ?? '-' }}</td>
                        <td>{{ Str::limit($reg->descripcion, 40) ?? '-' }}</td>
                        <td>{{ $reg->categoria ?? '-' }}</td>
                        <td>{{ $reg->ubicacion_1 ?? '-' }}</td>
                        <td>{{ $reg->usuario->nombres ?? '-' }}</td>
                        <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $reg->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <div>No se encontraron registros de conteo</div>
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
