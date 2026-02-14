@extends('layouts.app')
@section('title', 'Reporte Acumulado')

@section('content')
<div class="page-header">
    <h2>Reporte Acumulado por Empresa</h2>
</div>

<div class="card">
    <div class="card-header">
        <span>Resumen acumulado</span>
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
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    @if(request('empresa_id'))
                        <a href="{{ route('reportes.acumulado') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Total Sesiones</th>
                        <th>Finalizadas</th>
                        <th>Progreso</th>
                        <th>Total Registros</th>
                        <th>No Encontrados</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resumen as $empresa)
                    <tr>
                        <td style="font-weight:500;">{{ $empresa->nombre }}</td>
                        <td><span class="badge badge-primary">{{ number_format($empresa->sesiones_count) }}</span></td>
                        <td><span class="badge badge-success">{{ number_format($empresa->finalizadas_count) }}</span></td>
                        <td style="min-width:140px;">
                            @php
                                $pct = $empresa->sesiones_count > 0 ? round(($empresa->finalizadas_count / $empresa->sesiones_count) * 100) : 0;
                            @endphp
                            <div class="progress" style="height:18px;">
                                <div class="progress-bar" style="width:{{ $pct }}%;">{{ $pct }}%</div>
                            </div>
                        </td>
                        <td>{{ number_format($empresa->total_registros) }}</td>
                        <td><span class="badge badge-danger">{{ number_format($empresa->total_no_encontrados) }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                            <div>No se encontraron empresas</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
