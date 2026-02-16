<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoInventario;
use App\Models\ActivoTraspasado;
use App\Models\Empresa;
use App\Models\InventarioStatus;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivoFijoController extends Controller
{
    private function empresaIds()
    {
        $user = Auth::user();
        return $user->esAdmin() ? null : $user->empresas->pluck('id');
    }

    private function scopedEmpresas($empresaIds)
    {
        return Empresa::where('eliminado', false)
            ->when($empresaIds, fn($q) => $q->whereIn('id', $empresaIds))
            ->orderBy('nombre')->get();
    }

    public function index(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = ActivoFijoInventario::where('eliminado', false)->with('empresa', 'sucursal', 'status', 'usuario');

        if ($empresaIds !== null) {
            $query->whereIn('empresa_id', $empresaIds);
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        $sesiones = $query->withCount('registros', 'noEncontrados')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $empresas = $this->scopedEmpresas($empresaIds);
        $statuses = InventarioStatus::all();

        return view('activo-fijo.index', compact('sesiones', 'empresas', 'statuses'));
    }

    public function create()
    {
        $empresas = $this->scopedEmpresas($this->empresaIds());
        $statuses = InventarioStatus::all();
        return view('activo-fijo.create', compact('empresas', 'statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'status_id' => 'required|exists:inventarios_status,id',
        ]);

        ActivoFijoInventario::create([
            'empresa_id' => $request->empresa_id,
            'sucursal_id' => $request->sucursal_id,
            'usuario_id' => Auth::id(),
            'status_id' => $request->status_id,
            'comentarios' => $request->comentarios,
        ]);

        return redirect()->route('activo-fijo.index')->with('success', 'Sesión de activo fijo creada exitosamente.');
    }

    public function show(ActivoFijoInventario $activo_fijo)
    {
        $activo_fijo->load('empresa', 'sucursal', 'status', 'usuario', 'registros.usuario', 'noEncontrados');
        $activo_fijo->loadCount('registros', 'noEncontrados');

        return view('activo-fijo.show', compact('activo_fijo'));
    }

    public function edit(ActivoFijoInventario $activo_fijo)
    {
        $empresas = $this->scopedEmpresas($this->empresaIds());
        $sucursales = Sucursal::where('empresa_id', $activo_fijo->empresa_id)->where('eliminado', false)->get();
        $statuses = InventarioStatus::all();

        return view('activo-fijo.edit', compact('activo_fijo', 'empresas', 'sucursales', 'statuses'));
    }

    public function update(Request $request, ActivoFijoInventario $activo_fijo)
    {
        $request->validate([
            'status_id' => 'required|exists:inventarios_status,id',
        ]);

        $activo_fijo->update($request->only('status_id', 'comentarios'));

        if ($request->status_id == 3) {
            $activo_fijo->update(['finalizado' => true, 'fin_conteo' => now()]);
        }

        return redirect()->route('activo-fijo.index')->with('success', 'Sesión actualizada exitosamente.');
    }

    public function destroy(ActivoFijoInventario $activo_fijo)
    {
        $activo_fijo->update(['eliminado' => true]);
        return redirect()->route('activo-fijo.index')->with('success', 'Sesión eliminada exitosamente.');
    }

    public function traspasos(Request $request)
    {
        $query = ActivoTraspasado::with('sucursalOrigen', 'sucursalDestino', 'usuario');

        if ($request->filled('buscar')) {
            $query->where('activo', $request->buscar);
        }

        $traspasos = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('activo-fijo.traspasos', compact('traspasos'));
    }
}
