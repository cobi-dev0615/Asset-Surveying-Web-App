<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\InventarioStatus;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();

        $query = Inventario::where('eliminado', false)->with('empresa', 'sucursal', 'status', 'usuario')
            ->where('empresa_id', $empresaId);

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%");
            });
        }

        $inventarios = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $empresas = Empresa::where('id', $empresaId)->get();
        $statuses = InventarioStatus::all();

        return view('inventarios.index', compact('inventarios', 'empresas', 'statuses'));
    }

    public function create()
    {
        $empresas = Empresa::where('id', $this->selectedEmpresaId())->get();
        $statuses = InventarioStatus::all();
        return view('inventarios.create', compact('empresas', 'statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'nombre' => 'required|string|max:255',
            'status_id' => 'required|exists:inventarios_status,id',
        ]);

        Inventario::create([
            'empresa_id' => $request->empresa_id,
            'sucursal_id' => $request->sucursal_id,
            'nombre' => $request->nombre,
            'usuario_id' => Auth::id(),
            'nombre_usuario' => Auth::user()->nombres,
            'status_id' => $request->status_id,
            'auditor' => $request->auditor,
            'gerente' => $request->gerente,
            'subgerente' => $request->subgerente,
            'comentarios' => $request->comentarios,
        ]);

        return redirect()->route('inventarios.index')->with('success', 'Inventario creado exitosamente.');
    }

    public function show(Inventario $inventario)
    {
        $inventario->load('empresa', 'sucursal', 'status', 'usuario');
        $inventario->loadCount('registros', 'detalles');

        // Progress stats from inventario_detalle
        $stats = \App\Models\InventarioDetalle::where('inventario_id', $inventario->id)
            ->where('eliminado', false)
            ->selectRaw('
                COUNT(*) as total_capturas,
                COUNT(DISTINCT codigo_1) as productos_unicos,
                SUM(cantidad) as conteo_total,
                COUNT(DISTINCT nombre_almacen) as almacenes,
                COUNT(DISTINCT ubicacion_1) as ubicaciones,
                COUNT(DISTINCT nombre_usuario) as usuarios_activos,
                SUM(forzado) as forzados,
                MIN(fecha_captura) as primera_captura,
                MAX(fecha_captura) as ultima_captura
            ')->first();

        // Per-user breakdown
        $porUsuario = \App\Models\InventarioDetalle::where('inventario_id', $inventario->id)
            ->where('eliminado', false)
            ->selectRaw('nombre_usuario, COUNT(*) as capturas, SUM(cantidad) as cantidad_total, MIN(fecha_captura) as primera, MAX(fecha_captura) as ultima')
            ->groupBy('nombre_usuario')
            ->orderByDesc('capturas')
            ->get();

        // Per-warehouse breakdown
        $porAlmacen = \App\Models\InventarioDetalle::where('inventario_id', $inventario->id)
            ->where('eliminado', false)
            ->selectRaw('nombre_almacen, COUNT(*) as capturas, SUM(cantidad) as cantidad_total, COUNT(DISTINCT codigo_1) as productos')
            ->groupBy('nombre_almacen')
            ->orderByDesc('capturas')
            ->get();

        // Recent activity (last 20 captures)
        $actividad = \App\Models\InventarioDetalle::where('inventario_id', $inventario->id)
            ->where('eliminado', false)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('inventarios.show', compact('inventario', 'stats', 'porUsuario', 'porAlmacen', 'actividad'));
    }

    public function edit(Inventario $inventario)
    {
        $empresas = Empresa::where('id', $this->selectedEmpresaId())->get();
        $sucursales = Sucursal::where('empresa_id', $inventario->empresa_id)->where('eliminado', false)->get();
        $statuses = InventarioStatus::all();

        return view('inventarios.edit', compact('inventario', 'empresas', 'sucursales', 'statuses'));
    }

    public function update(Request $request, Inventario $inventario)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'status_id' => 'required|exists:inventarios_status,id',
        ]);

        $inventario->update($request->only('nombre', 'status_id', 'auditor', 'gerente', 'subgerente', 'comentarios'));

        if ($request->status_id == 3) {
            $inventario->update(['finalizado' => true, 'fin_conteo' => now()]);
        }

        return redirect()->route('inventarios.index')->with('success', 'Inventario actualizado exitosamente.');
    }

    public function destroy(Inventario $inventario)
    {
        $inventario->update(['eliminado' => true]);
        return redirect()->route('inventarios.index')->with('success', 'Inventario eliminado exitosamente.');
    }

    public function sucursalesPorEmpresa(Empresa $empresa)
    {
        return response()->json(
            $empresa->sucursales()->where('eliminado', false)->orderBy('nombre')->get(['id', 'nombre', 'codigo'])
        );
    }
}
