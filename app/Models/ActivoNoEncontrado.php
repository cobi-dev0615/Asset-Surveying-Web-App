<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoNoEncontrado extends Model
{
    protected $table = 'activos_no_encontrados';

    protected $fillable = ['inventario_id', 'activo', 'usuario_id', 'latitud', 'longitud'];

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(ActivoFijoInventario::class, 'inventario_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
