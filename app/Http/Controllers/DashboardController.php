<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoRegistro;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\ActivoFijoProducto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $empresaId = $this->selectedEmpresaId();

        $empresas = Empresa::where('eliminado', false)
            ->when(!$user->esAdmin(), fn ($q) => $q->whereIn('id', $user->empresas->pluck('id')))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $sucursalId = $this->selectedSucursalId();

        $sesionesQuery = ActivoFijoInventario::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->with('empresa', 'sucursal')
            ->orderBy('created_at', 'desc');

        if ($sucursalId) {
            $sesionesQuery->where('sucursal_id', $sucursalId);
        }

        $sesiones = $sesionesQuery->get();

        $sesionId = $request->input('sesion_id', $sesiones->first()?->id);
        $sesionActual = $sesiones->firstWhere('id', $sesionId);

        $avanceGeneral = $this->getAvanceGeneral($sesionId, $sesionActual);
        $avancePorArea = $this->getAvancePorArea($sesionId, $sesionActual);
        $avancePorCategoria = $this->getAvancePorCategoria($sesionId, $sesionActual);
        $colores = ['#ff4444','#00C851','#4285F4','#33b5e5','#ffbb33','#aa66cc','#2BBBAD','#2E2E2E','#3F729B','#c51162'];

        return view('dashboard', compact(
            'user', 'empresas', 'sesiones', 'sesionId', 'sesionActual',
            'avanceGeneral', 'avancePorArea', 'avancePorCategoria', 'colores'
        ));
    }

    /**
     * Return sessions filtered by empresa + sucursal (JSON for modal).
     */
    public function sesiones(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $request->sucursal_id ?: $this->selectedSucursalId();

        $query = ActivoFijoInventario::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->when($sucursalId, fn ($q, $v) => $q->where('sucursal_id', $v))
            ->with('empresa:id,nombre', 'sucursal:id,nombre,codigo')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'empresa_id', 'sucursal_id', 'nombre', 'created_at']);

        return response()->json($query->map(fn ($s) => [
            'id' => $s->id,
            'nombre' => $s->nombre,
            'empresa' => $s->empresa->nombre ?? '',
            'sucursal' => ($s->sucursal->codigo ?? '') . ' - ' . ($s->sucursal->nombre ?? ''),
            'fecha' => $s->created_at?->format('d/m/Y'),
        ]));
    }

    public function refreshAvanceGeneral(Request $request)
    {
        $sesionId = $request->input('sesion_id');
        $sesionActual = $sesionId ? ActivoFijoInventario::find($sesionId) : null;
        return response()->json($this->getAvanceGeneral($sesionId, $sesionActual));
    }

    public function refreshAvanceArea(Request $request)
    {
        $sesionId = $request->input('sesion_id');
        $sesionActual = $sesionId ? ActivoFijoInventario::find($sesionId) : null;
        return response()->json($this->getAvancePorArea($sesionId, $sesionActual));
    }

    public function refreshAvanceCategoria(Request $request)
    {
        $sesionId = $request->input('sesion_id');
        $sesionActual = $sesionId ? ActivoFijoInventario::find($sesionId) : null;
        return response()->json($this->getAvancePorCategoria($sesionId, $sesionActual));
    }

    private function getAvanceGeneral($sesionId, $sesionActual): array
    {
        $totalCatalogo = 0;
        $totalEncontrados = 0;
        $totalNoEncontrados = 0;

        if ($sesionActual) {
            $totalCatalogo = ActivoFijoProducto::where('eliminado', false)
                ->where('inventario_id', $sesionId)->count();
            $totalEncontrados = ActivoFijoRegistro::where('inventario_id', $sesionId)
                ->where('eliminado', false)->count();
            $totalNoEncontrados = ActivoFijoProducto::where('inventario_id', $sesionId)
                ->where('no_encontrado', true)->where('eliminado', false)->count();
        }

        $pendientes = max(0, $totalCatalogo - $totalEncontrados - $totalNoEncontrados);

        $data = [
            'catalogo' => $totalCatalogo,
            'encontrados' => $totalEncontrados,
            'no_encontrados' => $totalNoEncontrados,
            'pendientes' => $pendientes,
        ];

        if ($totalCatalogo > 0) {
            $data['pct_encontrados'] = round(($totalEncontrados / $totalCatalogo) * 100, 1);
            $data['pct_no_encontrados'] = round(($totalNoEncontrados / $totalCatalogo) * 100, 1);
            $data['pct_pendientes'] = round(100 - $data['pct_encontrados'] - $data['pct_no_encontrados'], 1);
        } else {
            $data['pct_encontrados'] = 0;
            $data['pct_no_encontrados'] = 0;
            $data['pct_pendientes'] = 100;
        }

        return $data;
    }

    private function getAvancePorArea($sesionId, $sesionActual): array
    {
        if (!$sesionActual) return [];

        return ActivoFijoRegistro::where('inventario_id', $sesionId)
            ->where('eliminado', false)
            ->select('nombre_almacen', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('nombre_almacen')
            ->orderByDesc('cantidad')
            ->get()
            ->map(fn ($row) => [
                'area' => $row->nombre_almacen ?: 'Sin ubicación',
                'cantidad' => $row->cantidad,
            ])
            ->toArray();
    }

    private function getAvancePorCategoria($sesionId, $sesionActual): array
    {
        if (!$sesionActual) return [];

        return ActivoFijoRegistro::where('activo_fijo_registros.inventario_id', $sesionId)
            ->where('activo_fijo_registros.eliminado', false)
            ->leftJoin('activo_fijo_productos', function ($join) use ($sesionId) {
                $join->on('activo_fijo_registros.id_producto', '=', 'activo_fijo_productos.id')
                     ->where('activo_fijo_productos.inventario_id', $sesionId);
            })
            ->select(
                DB::raw('COALESCE(activo_fijo_productos.categoria_2, activo_fijo_registros.categoria) as cat'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->groupBy('cat')
            ->orderByDesc('cantidad')
            ->get()
            ->map(fn ($row) => [
                'categoria' => $row->cat ?: 'Sin categoría',
                'cantidad' => $row->cantidad,
            ])
            ->toArray();
    }
}
