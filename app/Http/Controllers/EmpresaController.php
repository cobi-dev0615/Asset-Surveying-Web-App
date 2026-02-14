<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $query = Empresa::where('eliminado', false)->withCount('sucursales', 'users', 'productos');

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo', 'like', "%{$request->buscar}%");
            });
        }

        $empresas = $query->orderBy('nombre')->paginate(15)->withQueryString();

        return view('empresas.index', compact('empresas'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:empresas,codigo',
            'nombre' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('codigo', 'nombre');
        $data['usuario_id'] = Auth::id();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Empresa::create($data);

        return redirect()->route('empresas.index')->with('success', 'Empresa creada exitosamente.');
    }

    public function edit(Empresa $empresa)
    {
        $usuarios = User::where('eliminado', false)->orderBy('nombres')->get();
        $asignados = $empresa->users->pluck('id')->toArray();

        return view('empresas.edit', compact('empresa', 'usuarios', 'asignados'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:empresas,codigo,' . $empresa->id,
            'nombre' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'usuarios' => 'nullable|array',
        ]);

        $empresa->update($request->only('codigo', 'nombre'));

        if ($request->hasFile('logo')) {
            $empresa->update(['logo' => $request->file('logo')->store('logos', 'public')]);
        }

        if ($request->has('usuarios')) {
            $empresa->users()->sync($request->usuarios);
        }

        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada exitosamente.');
    }

    public function destroy(Empresa $empresa)
    {
        $empresa->update(['eliminado' => true]);

        return redirect()->route('empresas.index')->with('success', 'Empresa eliminada exitosamente.');
    }
}
