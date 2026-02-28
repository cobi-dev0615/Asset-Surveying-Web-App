<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoTraspasado extends Model
{
    protected $table = 'activos_traspasados';

    protected $fillable = [
        'activo', 'n_orden', 'sucursal_origen_id', 'sucursal_destino_id', 'usuario_id',
        'motivo', 'comentarios', 'estatus',
        'autorizado_por', 'surtido_por', 'cancelado_por',
        'fecha_hora_surtido', 'fecha_hora_cancelacion',
    ];

    protected function casts(): array
    {
        return [
            'eliminado' => 'boolean',
            'fecha_hora_surtido' => 'datetime',
            'fecha_hora_cancelacion' => 'datetime',
        ];
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

    public function autorizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    public function surtidor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surtido_por');
    }

    public function cancelador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelado_por');
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(ActivoFijoRegistro::class, 'activo');
    }
}
