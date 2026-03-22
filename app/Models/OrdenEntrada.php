<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenEntrada extends Model
{
    protected $table = 'ordenes_entrada';

    protected $fillable = [
        'usuario_id', 'n_orden', 'inventario_origen_id', 'inventario_destino_id',
        'motivo', 'comentarios', 'estatus_id',
        'autorizado_por', 'surtido_por', 'cancelado_por', 'rechazado_por',
        'fecha_hora_surtido', 'fecha_hora_cancelacion', 'eliminado',
    ];

    protected function casts(): array
    {
        return [
            'eliminado' => 'boolean',
            'fecha_hora_surtido' => 'datetime',
            'fecha_hora_cancelacion' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function inventarioOrigen(): BelongsTo
    {
        return $this->belongsTo(ActivoFijoInventario::class, 'inventario_origen_id');
    }

    public function inventarioDestino(): BelongsTo
    {
        return $this->belongsTo(ActivoFijoInventario::class, 'inventario_destino_id');
    }

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(OrdenEntradaEstatus::class, 'estatus_id');
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

    public function rechazador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rechazado_por');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenEntradaDetalle::class, 'orden_entrada_id');
    }
}
