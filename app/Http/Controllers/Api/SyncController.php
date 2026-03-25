<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoProducto;
use App\Models\ActivoFijoRegistro;
use App\Models\ActivoNoEncontrado;
use App\Models\ActivoTraspasado;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\InventarioStatus;
use App\Models\Producto;
use App\Models\LoteCaducidad;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function dashboardStats(Request $request)
    {
        $user = $request->user();
        $empresaIds = $user->empresas->pluck('id');

        // If specific empresa/sucursal selected, narrow the scope
        if ($request->has('empresa_id')) {
            $empresaIds = $empresaIds->intersect([$request->empresa_id]);
        }
        $sucursalId = $request->get('sucursal_id');

        // Session counts
        $invQuery = Inventario::where('eliminado', false)
            ->whereIn('empresa_id', $empresaIds);
        if ($sucursalId) $invQuery->where('sucursal_id', $sucursalId);
        $inventarioCount = $invQuery->count();

        $afQuery = ActivoFijoInventario::where('eliminado', false)
            ->whereIn('empresa_id', $empresaIds);
        if ($sucursalId) $afQuery->where('sucursal_id', $sucursalId);
        $activoFijoCount = $afQuery->count();

        // AF session IDs for filtered scope
        $afIdQuery = ActivoFijoInventario::where('eliminado', false)
            ->whereIn('empresa_id', $empresaIds);
        if ($sucursalId) $afIdQuery->where('sucursal_id', $sucursalId);
        $afSessionIds = $afIdQuery->pluck('id');

        // Registros = found/scanned assets
        $foundCount = ActivoFijoRegistro::whereIn('inventario_id', $afSessionIds)
            ->where('eliminado', false)
            ->where('traspasado', false)
            ->count();

        // Not found
        $notFoundCount = ActivoFijoProducto::whereIn('inventario_id', $afSessionIds)
            ->where('no_encontrado', true)
            ->where('eliminado', false)
            ->count();

        // Added = scanned but not in catalog (id_producto = 0)
        $addedCount = ActivoFijoRegistro::whereIn('inventario_id', $afSessionIds)
            ->where('eliminado', false)
            ->where('id_producto', 0)
            ->count();

        // Transferred
        $transferredCount = ActivoFijoRegistro::whereIn('inventario_id', $afSessionIds)
            ->where('eliminado', false)
            ->where('traspasado', true)
            ->count();

        // Top categories (join with product catalog for category_2)
        $categories = ActivoFijoRegistro::whereIn('activo_fijo_registros.inventario_id', $afSessionIds)
            ->where('activo_fijo_registros.eliminado', false)
            ->leftJoin('activo_fijo_productos', 'activo_fijo_registros.id_producto', '=', 'activo_fijo_productos.id')
            ->select(
                DB::raw("COALESCE(NULLIF(activo_fijo_productos.categoria_2, ''), NULLIF(activo_fijo_registros.categoria, ''), 'Sin categoría') as cat"),
                DB::raw('COUNT(*) as cnt')
            )
            ->groupBy('cat')
            ->orderByDesc('cnt')
            ->limit(8)
            ->get()
            ->map(fn ($r) => ['name' => $r->cat, 'count' => $r->cnt]);

        return response()->json([
            'inventario_count' => $inventarioCount,
            'activo_fijo_count' => $activoFijoCount,
            'found' => $foundCount,
            'not_found' => $notFoundCount,
            'added' => $addedCount,
            'transferred' => $transferredCount,
            'categories' => $categories,
        ]);
    }
}
