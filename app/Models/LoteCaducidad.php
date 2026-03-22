<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteCaducidad extends Model
{
    protected $table = 'lotes_caducidades';

    protected $fillable = [
        'empresa_id', 'sku', 'descripcion', 'lote',
        'fecha_caducidad', 'cantidad', 'almacen',
    ];

    protected function casts(): array
    {
        return [
            'fecha_caducidad' => 'date',
            'cantidad' => 'decimal:3',
            'eliminado' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
