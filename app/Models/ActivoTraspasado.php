<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoTraspasado extends Model
{
    protected $table = 'activos_traspasados';

    protected $fillable = [
        'activo', 'sucursal_origen_id', 'sucursal_destino_id', 'usuario_id',
    ];

    protected function casts(): array
    {
        return ['eliminado' => 'boolean'];
    }

    public function sucursalOrigen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }

    public function sucursalDestino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
