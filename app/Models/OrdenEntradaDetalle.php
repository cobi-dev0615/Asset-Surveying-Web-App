<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenEntradaDetalle extends Model
{
    protected $table = 'ordenes_entrada_detalle';

    protected $fillable = [
        'orden_entrada_id', 'registro_id', 'inventario_id', 'estatus', 'eliminado',
    ];

    protected function casts(): array
    {
        return ['eliminado' => 'boolean'];
    }

    public function ordenEntrada(): BelongsTo
    {
        return $this->belongsTo(OrdenEntrada::class, 'orden_entrada_id');
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(ActivoFijoRegistro::class, 'registro_id');
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(ActivoFijoInventario::class, 'inventario_id');
    }
}
