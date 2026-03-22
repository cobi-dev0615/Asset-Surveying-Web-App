<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioDetalle extends Model
{
    protected $table = 'inventario_detalle';

    protected $fillable = [
        'registro_id', 'inventario_id', 'usuario_id', 'producto_id',
        'n_conteo', 'nombre_conteo', 'cantidad', 'factor',
        'codigo_1', 'codigo_2', 'codigo_3', 'unidad_medida',
        'nombre_usuario', 'lote', 'fecha_caducidad', 'fecha_elaboracion',
        'n_serie', 'ubicacion_1', 'ubicacion_2',
        'almacen_id', 'nombre_almacen',
        'latitud', 'longitud', 'id_dispositivo', 'marca_dispositivo',
        'modelo_dispositivo', 'version_app', 'id_app',
        'fecha_captura', 'hora_captura',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'fecha_captura' => 'date',
            'forzado' => 'boolean',
            'editado' => 'boolean',
            'eliminado' => 'boolean',
        ];
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(InventarioRegistro::class, 'registro_id');
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
}
