<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioRegistro extends Model
{
    protected $table = 'inventario_registros';

    protected $fillable = [
        'inventario_id', 'usuario_id', 'producto_id', 'nombre_conteo',
        'cantidad', 'codigo_1', 'codigo_2', 'codigo_3',
        'ubicacion_1', 'ubicacion_2', 'ubicacion_3',
        'precio_compra', 'precio_venta', 'factor', 'unidad_medida',
        'almacen_id', 'nombre_almacen', 'cantidad_teorica',
        'lote', 'fecha_caducidad', 'sincronizado', 'forzado',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'precio_compra' => 'decimal:3',
            'precio_venta' => 'decimal:3',
            'cantidad_teorica' => 'decimal:3',
            'sincronizado' => 'boolean',
            'forzado' => 'boolean',
            'eliminado' => 'boolean',
        ];
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(InventarioDetalle::class, 'registro_id');
    }
}
