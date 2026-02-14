<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioStatus extends Model
{
    protected $table = 'inventarios_status';
    public $timestamps = false;

    protected $fillable = ['status'];

    public function getNombreAttribute(): string
    {
        return $this->status;
    }
}
