<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Almacen extends Model
{
    protected $table = 'almacenes';

    protected $fillable = ['inventario_id', 'nombre', 'n_conteo'];

    protected function casts(): array
    {
        return ['eliminado' => 'boolean'];
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }
}
