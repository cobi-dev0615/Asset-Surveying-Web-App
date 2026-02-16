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

        $query = Inventario::where('eliminado', false)->with('empresa', 'sucursal', 'status', 'usuario');

        if ($empresaIds !== null) {
            $query->whereIn('empresa_id', $empresaIds);
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%");
            });
        }

        $inventarios = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $empresas = $this->scopedEmpresas($empresaIds);
        $statuses = InventarioStatus::all();

        return view('inventarios.index', compact('inventarios', 'empresas', 'statuses'));
    }

    public function create()
    {
        $empresas = $this->scopedEmpresas($this->empresaIds());
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
        $inventario->load('empresa', 'sucursal', 'status', 'usuario', 'registros.usuario');
        $inventario->loadCount('registros', 'detalles');

        return view('inventarios.show', compact('inventario'));
    }

    public function edit(Inventario $inventario)
    {
        $empresas = $this->scopedEmpresas($this->empresaIds());
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
