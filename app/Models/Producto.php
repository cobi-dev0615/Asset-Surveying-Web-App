<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    protected $fillable = [
        'empresa_id', 'codigo_1', 'codigo_2', 'codigo_3', 'codigo_4', 'codigo_5',
        'descripcion', 'marca', 'modelo', 'categoria', 'subcategoria', 'subcategoria_2',
        'precio_compra', 'precio_venta', 'cantidad_teorica', 'factor', 'unidad_medida',
        'n_serie', 'tag_rfid', 'observaciones', 'seriado', 'forzado', 'eliminado',
    ];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:3',
            'precio_venta' => 'decimal:3',
            'cantidad_teorica' => 'decimal:3',
            'factor' => 'decimal:3',
            'seriado' => 'boolean',
            'forzado' => 'boolean',
            'eliminado' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
