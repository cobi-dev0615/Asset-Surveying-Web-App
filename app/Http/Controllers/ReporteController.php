<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoRegistro;
use App\Models\ActivoNoEncontrado;
use App\Models\Empresa;
use App\Models\LogSesionMovil;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function conteo(Request $request)
    {
        $query = ActivoFijoRegistro::where('eliminado', false)
            ->with('inventario.empresa', 'inventario.sucursal', 'usuario');

        if ($request->filled('empresa_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('empresa_id', $request->empresa_id));
        }

        if ($request->filled('sucursal_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('sucursal_id', $request->sucursal_id));
        }

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        $registros = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        $sesiones = ActivoFijoInventario::where('eliminado', false)->orderBy('id', 'desc')->get();

        return view('reportes.conteo', compact('registros', 'empresas', 'sesiones'));
    }

    public function noEncontrados(Request $request)
    {
        $query = ActivoNoEncontrado::with('inventario.empresa', 'inventario.sucursal', 'usuario');

        if ($request->filled('empresa_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('empresa_id', $request->empresa_id));
        }

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        $registros = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        $sesiones = ActivoFijoInventario::where('eliminado', false)->orderBy('id', 'desc')->get();

        return view('reportes.no-encontrados', compact('registros', 'empresas', 'sesiones'));
    }

    public function global(Request $request)
    {
        $query = ActivoFijoInventario::where('eliminado', false)
            ->with('empresa', 'sucursal', 'status', 'usuario')
            ->withCount('registros', 'noEncontrados');

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        $sesiones = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();

        // Summary stats
        $totalSesiones = ActivoFijoInventario::where('eliminado', false)->count();
        $totalRegistros = ActivoFijoRegistro::where('eliminado', false)->count();
        $totalNoEncontrados = ActivoNoEncontrado::count();
        $finalizadas = ActivoFijoInventario::where('eliminado', false)->where('finalizado', true)->count();

        return view('reportes.global', compact(
            'sesiones', 'empresas', 'totalSesiones', 'totalRegistros', 'totalNoEncontrados', 'finalizadas'
        ));
    }

    public function acumulado(Request $request)
    {
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();

        $query = Empresa::where('eliminado', false)
            ->withCount([
                'activoFijoInventarios as sesiones_count' => fn ($q) => $q->where('eliminado', false),
                'activoFijoInventarios as finalizadas_count' => fn ($q) => $q->where('eliminado', false)->where('finalizado', true),
            ]);

        if ($request->filled('empresa_id')) {
            $query->where('id', $request->empresa_id);
        }

        $resumen = $query->orderBy('nombre')->get();

        // Per-empresa asset count
        foreach ($resumen as $empresa) {
            $empresa->total_registros = ActivoFijoRegistro::where('eliminado', false)
                ->whereHas('inventario', fn ($q) => $q->where('empresa_id', $empresa->id)->where('eliminado', false))
                ->count();
            $empresa->total_no_encontrados = ActivoNoEncontrado::whereHas('inventario', fn ($q) => $q->where('empresa_id', $empresa->id)->where('eliminado', false))
                ->count();
        }

        return view('reportes.acumulado', compact('resumen', 'empresas'));
    }

    public function sesionesMovil(Request $request)
    {
        $query = LogSesionMovil::with('inventario.empresa', 'inventario.sucursal', 'usuario');

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        if ($request->filled('usuario_id')) {
            $query->whereHas('usuario', fn ($q) => $q->where('id', $request->usuario_id));
        }

        $sesiones = $query->orderBy('fecha_hora_entrada', 'desc')->paginate(20)->withQueryString();
        $inventarios = ActivoFijoInventario::where('eliminado', false)->with('empresa', 'sucursal')->orderBy('id', 'desc')->get();

        return view('reportes.sesiones-movil', compact('sesiones', 'inventarios'));
    }
}
