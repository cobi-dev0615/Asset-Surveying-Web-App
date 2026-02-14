<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenEntradaEstatus extends Model
{
    protected $table = 'ordenes_entrada_estatus';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = ['id', 'nombre_estatus'];
}
