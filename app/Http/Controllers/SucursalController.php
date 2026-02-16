<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SucursalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $empresaIds = $user->esAdmin() ? null : $user->empresas->pluck('id');

        $query = Sucursal::where('eliminado', false)->with('empresa');

        if ($empresaIds !== null) {
            $query->whereIn('empresa_id', $empresaIds);
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo', 'like', "%{$request->buscar}%")
                  ->orWhere('ciudad', 'like', "%{$request->buscar}%");
            });
        }

        $sucursales = $query->orderBy('nombre')->paginate(15)->withQueryString();
        $empresas = Empresa::where('eliminado', false)
            ->when($empresaIds, fn($q) => $q->whereIn('id', $empresaIds))
            ->orderBy('nombre')->get();

        return view('sucursales.index', compact('sucursales', 'empresas'));
    }

    public function create()
    {
        abort_unless(Auth::user()->esAdmin(), 403);
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        return view('sucursales.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->esAdmin(), 403);
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
        ]);

        Sucursal::create($request->only('empresa_id', 'codigo', 'nombre', 'ciudad', 'direccion'));

        return redirect()->route('sucursales.index')->with('success', 'Sucursal creada exitosamente.');
    }

    public function edit(Sucursal $sucursal)
    {
        abort_unless(Auth::user()->esAdmin(), 403);
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        return view('sucursales.edit', compact('sucursal', 'empresas'));
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        abort_unless(Auth::user()->esAdmin(), 403);
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
        ]);

        $sucursal->update($request->only('empresa_id', 'codigo', 'nombre', 'ciudad', 'direccion'));

        return redirect()->route('sucursales.index')->with('success', 'Sucursal actualizada exitosamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        abort_unless(Auth::user()->esAdmin(), 403);
        $sucursal->update(['eliminado' => true]);

        return redirect()->route('sucursales.index')->with('success', 'Sucursal eliminada exitosamente.');
    }
}
