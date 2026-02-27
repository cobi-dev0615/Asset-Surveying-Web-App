<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function selectedEmpresaId()
    {
        return session('selected_empresa_id');
    }

    protected function selectedSucursalId()
    {
        return session('selected_sucursal_id');
    }

    protected function scopeBySelection($query, $empresaCol = 'empresa_id', $sucursalCol = null)
    {
        $query->where($empresaCol, $this->selectedEmpresaId());

        if ($this->selectedSucursalId() && $sucursalCol) {
            $query->where($sucursalCol, $this->selectedSucursalId());
        }

        return $query;
    }
}
