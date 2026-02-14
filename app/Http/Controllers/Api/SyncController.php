<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\InventarioStatus;
use App\Models\Producto;
use App\Models\LoteCaducidad;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function empresas(Request $request)
    {
        $empresas = $request->user()->empresas()
            ->where('eliminado', false)
            ->with(['sucursales' => fn ($q) => $q->where('eliminado', false)])
            ->get();

        return response()->json($empresas);
    }

    public function sucursales(Request $request, int $empresaId)
    {
        $sucursales = Sucursal::where('empresa_id', $empresaId)
            ->where('eliminado', false)
            ->get();

        return response()->json($sucursales);
    }

    public function productos(Request $request, int $empresaId)
    {
        $query = Producto::where('empresa_id', $empresaId)
            ->where('eliminado', false);

        if ($request->has('desde')) {
            $query->where('updated_at', '>=', $request->desde);
        }

        $productos = $query->paginate($request->get('per_page', 500));

        return response()->json($productos);
    }

    public function lotesCaducidades(Request $request, int $empresaId)
    {
        $query = LoteCaducidad::where('empresa_id', $empresaId)
            ->where('eliminado', false);

        if ($request->has('sku')) {
            $query->where('sku', $request->sku);
        }

        $lotes = $query->paginate($request->get('per_page', 500));

        return response()->json($lotes);
    }

    public function statuses()
    {
        return response()->json(InventarioStatus::all());
    }
}
