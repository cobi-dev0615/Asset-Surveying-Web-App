@extends('layouts.app')
@section('title', 'Sesiones Móvil')

@section('content')
<div class="page-header">
    <h2>Sesiones Móvil</h2>
</div>

<div class="card">
    <div class="card-header">
        <span>{{ $sesiones->total() }} sesiones registradas</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="toolbar" style="padding:1rem 1.5rem;">
            <div class="toolbar-left">
                <form method="GET" style="display:flex; gap:0.5rem; flex:1; flex-wrap:wrap;">
                    <select name="inventario_id" class="form-control" style="width:auto; min-width:220px;">
                        <option value="">Todas las sesiones</option>
                        @foreach($inventarios as $inv)
                            <option value="{{ $inv->id }}" {{ request('inventario_id') == $inv->id ? 'selected' : '' }}>
                                {{ $inv->empresa->nombre ?? '' }} - {{ $inv->sucursal->nombre ?? $inv->nombre ?? 'Sesión #'.$inv->id }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request()->hasAny(['inventario_id', 'usuario_id']))
                        <a href="{{ route('reportes.sesiones-movil') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
            <div class="toolbar-right">
                <a href="{{ route('reportes.sesiones-movil.export', request()->query()) }}" class="btn btn-success btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Exportar Excel
                </a>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sesión</th>
                        <th>Usuario</th>
                        <th>Dispositivo</th>
                        <th>Serie</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sesiones as $ses)
                    <tr>
                        <td>{{ $ses->id }}</td>
                        <td><span class="badge badge-primary">#{{ $ses->inventario_id }}</span></td>
                        <td>{{ $ses->usuario->nombres ?? '-' }}</td>
                        <td>{{ $ses->plataforma_dispositivo ?: '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $ses->serie_dispositivo ?: '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $ses->fecha_hora_entrada ? \Carbon\Carbon::parse($ses->fecha_hora_entrada)->format('d/m/Y H:i') : '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $ses->fecha_hora_salida ? \Carbon\Carbon::parse($ses->fecha_hora_salida)->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            @if($ses->sesion_activa)
                                <span class="badge badge-success">Activa</span>
                            @else
                                <span class="badge badge-gray">Cerrada</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18.01"/></svg>
                            <div>No se encontraron sesiones móvil</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sesiones->hasPages())
    <div class="card-footer">
        {{ $sesiones->links() }}
    </div>
    @endif
</div>
@endsection
