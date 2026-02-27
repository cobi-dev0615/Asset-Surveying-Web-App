<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoRegistro;
use App\Models\ActivoTraspasado;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferenciaController extends Controller
{
    public function nueva()
    {
        $empresaId = $this->selectedEmpresaId();

        $empresas = Empresa::where('id', $empresaId)->get();
        $sucursales = Sucursal::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')->get();

        return view('transferencias.nueva', compact('empresas', 'sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'activo' => 'required|integer',
            'sucursal_origen_id' => 'required|exists:sucursales,id',
            'sucursal_destino_id' => 'required|exists:sucursales,id|different:sucursal_origen_id',
        ]);

        ActivoTraspasado::create([
            'activo' => $request->activo,
            'sucursal_origen_id' => $request->sucursal_origen_id,
            'sucursal_destino_id' => $request->sucursal_destino_id,
            'usuario_id' => Auth::id(),
        ]);

        // Mark the asset record as transferred
        ActivoFijoRegistro::where('id', $request->activo)
            ->update(['traspasado' => true, 'solicitado' => true]);

        return redirect()->route('transferencias.solicitadas')->with('success', 'Transferencia solicitada exitosamente.');
    }

    public function solicitadas(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();

        $query = ActivoTraspasado::where('eliminado', false)
            ->with('sucursalOrigen', 'sucursalDestino', 'usuario')
            ->where(function ($q) use ($empresaId) {
                $q->whereHas('sucursalOrigen', fn($sq) => $sq->where('empresa_id', $empresaId))
                  ->orWhereHas('sucursalDestino', fn($sq) => $sq->where('empresa_id', $empresaId));
            });

        if ($request->filled('buscar')) {
            $query->where('activo', $request->buscar);
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_origen_id', $request->sucursal_id);
        }

        $traspasos = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $sucursales = Sucursal::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')->get();

        return view('transferencias.solicitadas', compact('traspasos', 'sucursales'));
    }

    public function recibidas(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();

        $query = ActivoTraspasado::where('eliminado', false)
            ->with('sucursalOrigen', 'sucursalDestino', 'usuario')
            ->where(function ($q) use ($empresaId) {
                $q->whereHas('sucursalOrigen', fn($sq) => $sq->where('empresa_id', $empresaId))
                  ->orWhereHas('sucursalDestino', fn($sq) => $sq->where('empresa_id', $empresaId));
            });

        if ($request->filled('buscar')) {
            $query->where('activo', $request->buscar);
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_destino_id', $request->sucursal_id);
        }

        $traspasos = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $sucursales = Sucursal::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')->get();

        return view('transferencias.recibidas', compact('traspasos', 'sucursales'));
    }
}
