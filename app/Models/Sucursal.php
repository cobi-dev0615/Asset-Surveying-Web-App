<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = ['empresa_id', 'codigo', 'nombre', 'ciudad', 'direccion', 'eliminado'];

    protected function casts(): array
    {
        return ['eliminado' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
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
