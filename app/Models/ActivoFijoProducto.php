<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivoFijoProducto extends Model
{
    protected $table = 'activo_fijo_productos';

    protected $fillable = [
        'inventario_id', 'empresa_id', 'subsidiaria', 'sucursal',
        'codigo_1', 'codigo_2', 'codigo_3', 'tag_rfid',
        'descripcion', 'n_serie', 'n_serie_anterior', 'n_serie_nuevo',
        'categoria_1', 'categoria_2', 'marca', 'modelo', 'tipo_activo',
        'fecha_inicio_servicio', 'imagen1', 'imagen2', 'imagen3',
        'cantidad_teorica', 'observaciones',
        'eliminado', 'no_encontrado', 'forzado', 'traspasado', 'solicitado',
        'fecha_registro',
    ];

    protected function casts(): array
    {
        return [
            'eliminado' => 'boolean',
            'no_encontrado' => 'boolean',
            'forzado' => 'boolean',
            'traspasado' => 'boolean',
            'solicitado' => 'boolean',
            'fecha_registro' => 'datetime',
        ];
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(ActivoFijoInventario::class, 'inventario_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function registros(): HasMany
    {
        return $this->hasMany(ActivoFijoRegistro::class, 'id_producto');
    }
}
