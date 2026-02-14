<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogSesionMovil extends Model
{
    protected $table = 'log_sesiones_movil';

    protected $fillable = [
        'inventario_id', 'usuario_id',
        'fecha_hora_entrada', 'fecha_hora_salida',
        'plataforma_dispositivo', 'serie_dispositivo',
        'latitud', 'longitud', 'sesion_activa',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_entrada' => 'datetime',
            'fecha_hora_salida' => 'datetime',
            'sesion_activa' => 'boolean',
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
}
