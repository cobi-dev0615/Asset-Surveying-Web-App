<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventario extends Model
{
    protected $fillable = [
        'empresa_id', 'sucursal_id', 'nombre', 'usuario_id', 'nombre_usuario',
        'auditor', 'firma_auditor', 'gerente', 'firma_gerente',
        'subgerente', 'firma_subgerente', 'inicio_conteo', 'fin_conteo',
        'status_id', 'finalizado', 'comentarios', 'motivo_cancelacion', 'eliminado',
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
        return $this->hasMany(InventarioRegistro::class, 'inventario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(InventarioDetalle::class, 'inventario_id');
    }

    public function almacenes(): HasMany
    {
        return $this->hasMany(Almacen::class, 'inventario_id');
    }
}
