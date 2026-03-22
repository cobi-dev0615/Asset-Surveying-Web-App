@extends('layouts.app')
@section('title', 'Nueva Empresa')

@section('content')
<div class="page-header">
    <h2>Nueva Empresa</h2>
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
        <form method="POST" action="{{ route('empresas.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="codigo">Código *</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" value="{{ old('codigo') }}" required>
                    @error('codigo') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                    @error('nombre') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="logo">Logo</label>
                <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                @error('logo') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Guardar Empresa</button>
                <a href="{{ route('empresas.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
