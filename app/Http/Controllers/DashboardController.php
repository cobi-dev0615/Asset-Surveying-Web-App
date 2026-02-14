<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoRegistro;
use App\Models\ActivoNoEncontrado;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get all active sessions for the session selector
        $sesiones = ActivoFijoInventario::where('eliminado', false)
            ->with('empresa', 'sucursal')
            ->orderBy('created_at', 'desc')
            ->get();

        // Use selected session or the latest one
        $sesionId = $request->input('sesion_id', $sesiones->first()?->id);
        $sesionActual = $sesiones->firstWhere('id', $sesionId);

        // ── Panel 1: Avance General del Inventario ──
        $totalCatalogo = 0;
        $totalEncontrados = 0;
        $totalNoEncontrados = 0;

        if ($sesionActual) {
            $totalCatalogo = Producto::where('eliminado', false)
                ->when($sesionActual->empresa_id, fn ($q) => $q->where('empresa_id', $sesionActual->empresa_id))
                ->count();

            $totalEncontrados = ActivoFijoRegistro::where('inventario_id', $sesionId)
                ->where('eliminado', false)
                ->count();

            $totalNoEncontrados = ActivoNoEncontrado::where('inventario_id', $sesionId)->count();
        }

        $pendientes = max(0, $totalCatalogo - $totalEncontrados - $totalNoEncontrados);

        $avanceGeneral = [
            'catalogo' => $totalCatalogo,
            'encontrados' => $totalEncontrados,
            'no_encontrados' => $totalNoEncontrados,
            'pendientes' => $pendientes,
        ];

        // Percentages for donut chart
        if ($totalCatalogo > 0) {
            $avanceGeneral['pct_encontrados'] = round(($totalEncontrados / $totalCatalogo) * 100, 1);
            $avanceGeneral['pct_no_encontrados'] = round(($totalNoEncontrados / $totalCatalogo) * 100, 1);
            $avanceGeneral['pct_pendientes'] = round(100 - $avanceGeneral['pct_encontrados'] - $avanceGeneral['pct_no_encontrados'], 1);
        } else {
            $avanceGeneral['pct_encontrados'] = 0;
            $avanceGeneral['pct_no_encontrados'] = 0;
            $avanceGeneral['pct_pendientes'] = 100;
        }

        // ── Panel 2: Avance por Área ──
        $avancePorArea = [];
        if ($sesionActual) {
            $avancePorArea = ActivoFijoRegistro::where('inventario_id', $sesionId)
                ->where('eliminado', false)
                ->select('ubicacion_1', DB::raw('COUNT(*) as cantidad'))
                ->groupBy('ubicacion_1')
                ->orderByDesc('cantidad')
                ->get()
                ->map(fn ($row) => [
                    'area' => $row->ubicacion_1 ?: 'Sin ubicación',
                    'cantidad' => $row->cantidad,
                ])
                ->toArray();
        }

        // ── Panel 3: Activos por Categoría ──
        $avancePorCategoria = [];
        if ($sesionActual) {
            $avancePorCategoria = ActivoFijoRegistro::where('inventario_id', $sesionId)
                ->where('eliminado', false)
                ->select('categoria', DB::raw('COUNT(*) as cantidad'))
                ->groupBy('categoria')
                ->orderByDesc('cantidad')
                ->get()
                ->map(fn ($row) => [
                    'categoria' => $row->categoria ?: 'Sin categoría',
                    'cantidad' => $row->cantidad,
                ])
                ->toArray();
        }

        return view('dashboard', compact(
            'user', 'sesiones', 'sesionId', 'sesionActual',
            'avanceGeneral', 'avancePorArea', 'avancePorCategoria'
        ));
    }
}
