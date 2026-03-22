@extends('layouts.app')
@section('title', 'Inventarios')

@section('content')
<div class="page-header">
    <h2>Inventarios</h2>
    <div class="page-header-actions">
        <a href="{{ route('inventarios.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Inventario
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span>{{ $inventarios->total() }} inventarios</span>
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
                    <select name="status_id" class="form-control" style="width:auto; min-width:160px;">
                        <option value="">Todos los estados</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->id }}" {{ request('status_id') == $st->id ? 'selected' : '' }}>{{ $st->nombre }}</option>
                        @endforeach
                    </select>
                    <div class="search-box" style="flex:1; max-width:260px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar..." value="{{ request('buscar') }}">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request()->hasAny(['buscar', 'empresa_id', 'status_id']))
                        <a href="{{ route('inventarios.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Sucursal</th>
                        <th>Creador</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th style="width:140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventarios as $inv)
                    <tr>
                        <td>{{ $inv->id }}</td>
                        <td style="font-weight:500;">{{ $inv->nombre }}</td>
                        <td>{{ $inv->empresa->nombre ?? '-' }}</td>
                        <td>{{ $inv->sucursal->nombre ?? '-' }}</td>
                        <td>{{ $inv->usuario->nombres ?? $inv->nombre_usuario ?? '-' }}</td>
                        <td>
                            @php
                                $statusClass = match($inv->status_id) {
                                    1 => 'badge-warning',
                                    2 => 'badge-info',
                                    3 => 'badge-success',
                                    default => 'badge-gray'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $inv->status->nombre ?? 'N/A' }}</span>
                        </td>
                        <td style="font-size:0.8rem; color:var(--text-secondary);">{{ $inv->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div style="display:flex; gap:0.25rem;">
                                <a href="{{ route('inventarios.show', $inv) }}" class="btn btn-sm btn-outline" title="Ver detalles">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('inventarios.edit', $inv) }}" class="btn btn-sm btn-outline" title="Editar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('inventarios.destroy', $inv) }}" onsubmit="return confirm('¿Eliminar este inventario?')">
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
                        <td colspan="8" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                            <div>No se encontraron inventarios</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($inventarios->hasPages())
    <div class="card-footer">
        {{ $inventarios->links() }}
    </div>
    @endif
</div>
@endsection
