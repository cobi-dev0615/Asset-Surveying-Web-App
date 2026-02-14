@extends('layouts.app')
@section('title', 'Nuevo Producto')

@section('content')
<div class="page-header">
    <h2>Nuevo Producto</h2>
    <div class="page-header-actions">
        <a href="{{ route('productos.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">Datos del Producto</div>
    <div class="card-body">
        <form method="POST" action="{{ route('productos.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="empresa_id">Empresa *</label>
                <select id="empresa_id" name="empresa_id" class="form-control" required>
                    <option value="">Seleccionar empresa...</option>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" {{ old('empresa_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nombre }}</option>
                    @endforeach
                </select>
                @error('empresa_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row" style="grid-template-columns: repeat(3, 1fr);">
                <div class="form-group">
                    <label class="form-label" for="codigo_1">Código 1 *</label>
                    <input type="text" id="codigo_1" name="codigo_1" class="form-control" value="{{ old('codigo_1') }}" required>
                    @error('codigo_1') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="codigo_2">Código 2</label>
                    <input type="text" id="codigo_2" name="codigo_2" class="form-control" value="{{ old('codigo_2') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="codigo_3">Código 3</label>
                    <input type="text" id="codigo_3" name="codigo_3" class="form-control" value="{{ old('codigo_3') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción *</label>
                <input type="text" id="descripcion" name="descripcion" class="form-control" value="{{ old('descripcion') }}" required>
                @error('descripcion') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="marca">Marca</label>
                    <input type="text" id="marca" name="marca" class="form-control" value="{{ old('marca') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="modelo">Modelo</label>
                    <input type="text" id="modelo" name="modelo" class="form-control" value="{{ old('modelo') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="categoria">Categoría</label>
                    <input type="text" id="categoria" name="categoria" class="form-control" value="{{ old('categoria') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="subcategoria">Subcategoría</label>
                    <input type="text" id="subcategoria" name="subcategoria" class="form-control" value="{{ old('subcategoria') }}">
                </div>
            </div>

            <div class="form-row" style="grid-template-columns: repeat(4, 1fr);">
                <div class="form-group">
                    <label class="form-label" for="precio_compra">Precio Compra</label>
                    <input type="number" step="0.01" id="precio_compra" name="precio_compra" class="form-control" value="{{ old('precio_compra', '0') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="precio_venta">Precio Venta</label>
                    <input type="number" step="0.01" id="precio_venta" name="precio_venta" class="form-control" value="{{ old('precio_venta', '0') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cantidad_teorica">Existencia Teórica</label>
                    <input type="number" step="0.001" id="cantidad_teorica" name="cantidad_teorica" class="form-control" value="{{ old('cantidad_teorica', '0') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="unidad_medida">Unidad Medida</label>
                    <input type="text" id="unidad_medida" name="unidad_medida" class="form-control" value="{{ old('unidad_medida') }}" placeholder="PZA, KG, LT...">
                </div>
            </div>

            <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Guardar Producto</button>
                <a href="{{ route('productos.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
