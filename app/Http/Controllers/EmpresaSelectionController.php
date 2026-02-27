<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaSelectionController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        $empresas = Empresa::where('eliminado', false)
            ->when(!$user->esAdmin(), fn($q) => $q->whereIn('id', $user->empresas->pluck('id')))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('seleccionar-empresa', compact('empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $empresa = Empresa::find($request->empresa_id);
        $sucursal = $request->sucursal_id ? Sucursal::find($request->sucursal_id) : null;

        $request->session()->put('selected_empresa_id', $empresa->id);
        $request->session()->put('selected_empresa_nombre', $empresa->nombre);

        if ($sucursal) {
            $request->session()->put('selected_sucursal_id', $sucursal->id);
            $request->session()->put('selected_sucursal_nombre', $sucursal->nombre);
        } else {
            $request->session()->forget(['selected_sucursal_id', 'selected_sucursal_nombre']);
        }

        return redirect('/dashboard');
    }

    public function cambiar()
    {
        session()->forget([
            'selected_empresa_id',
            'selected_empresa_nombre',
            'selected_sucursal_id',
            'selected_sucursal_nombre',
        ]);

        return redirect('/seleccionar-empresa');
    }
}
