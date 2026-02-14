<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $fillable = ['codigo', 'nombre', 'logo', 'usuario_id', 'eliminado'];

    protected function casts(): array
    {
        return [
            'eliminado' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'empresa_user');
    }

    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }

    public function activoFijoInventarios(): HasMany
    {
        return $this->hasMany(ActivoFijoInventario::class);
    }
}
