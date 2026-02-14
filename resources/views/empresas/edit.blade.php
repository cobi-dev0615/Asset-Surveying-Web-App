@extends('layouts.app')
@section('title', 'Editar Empresa')

@section('content')
<div class="page-header">
    <h2>Editar Empresa: {{ $empresa->nombre }}</h2>
    <div class="page-header-actions">
        <a href="{{ route('empresas.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">Datos de la Empresa</div>
    <div class="card-body">
        <form method="POST" action="{{ route('empresas.update', $empresa) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="codigo">Código *</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" value="{{ old('codigo', $empresa->codigo) }}" required>
                    @error('codigo') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre', $empresa->nombre) }}" required>
                    @error('nombre') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="logo">Logo</label>
                <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                @if($empresa->logo)
                    <div class="form-hint">Logo actual: {{ $empresa->logo }}</div>
                @endif
                @error('logo') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" style="margin-top:1.5rem;">
                <label class="form-label">Usuarios Asignados</label>
                <div style="max-height:200px; overflow-y:auto; border:1px solid var(--border); border-radius:var(--radius); padding:0.75rem;">
                    @foreach($usuarios as $u)
                    <div class="form-check" style="margin-bottom:0.35rem;">
                        <input type="checkbox" name="usuarios[]" value="{{ $u->id }}" id="usr_{{ $u->id }}" {{ in_array($u->id, $asignados) ? 'checked' : '' }}>
                        <label for="usr_{{ $u->id }}" style="font-size:0.85rem; cursor:pointer;">
                            {{ $u->nombres }} <span style="color:var(--text-secondary);">({{ $u->usuario }})</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Actualizar Empresa</button>
                <a href="{{ route('empresas.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
