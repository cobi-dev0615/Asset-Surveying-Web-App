<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoFijoRegistro extends Model
{
    protected $table = 'activo_fijo_registros';

    protected $fillable = [
        'inventario_id', 'usuario_id', 'id_producto',
        'codigo_1', 'codigo_1_anterior', 'codigo_2', 'codigo_3',
        'tag_rfid', 'n_serie', 'n_serie_anterior', 'n_serie_nuevo',
        'nombre_almacen', 'ubicacion_1', 'categoria', 'descripcion',
        'imagen1', 'imagen2', 'imagen3', 'observaciones',
        'traspasado', 'sucursal_origen', 'forzado', 'solicitado',
        'latitud', 'longitud', 'version_app',
    ];

    protected function casts(): array
    {
        return [
            'traspasado' => 'boolean',
            'forzado' => 'boolean',
            'solicitado' => 'boolean',
            'eliminado' => 'boolean',
        ];
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(ActivoFijoInventario::class, 'inventario_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ActivoFijoProducto::class, 'id_producto');
    }
}
