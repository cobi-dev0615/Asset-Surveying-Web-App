<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivoFijoInventario extends Model
{
    protected $table = 'activo_fijo_inventarios';

    protected $fillable = [
        'empresa_id', 'sucursal_id', 'usuario_id', 'status_id',
        'comentarios', 'inicio_conteo', 'fin_conteo', 'finalizado', 'eliminado',
    ];

    protected function casts(): array
    {
        return [
            'finalizado' => 'boolean',
            'eliminado' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(InventarioStatus::class, 'status_id');
    }

    public function registros(): HasMany
    {
        return $this->hasMany(ActivoFijoRegistro::class, 'inventario_id');
    }

    public function noEncontrados(): HasMany
    {
        return $this->hasMany(ActivoNoEncontrado::class, 'inventario_id');
    }
}
