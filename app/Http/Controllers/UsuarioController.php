<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('eliminado', false)->with('rol', 'empresas');

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombres', 'like', "%{$request->buscar}%")
                  ->orWhere('usuario', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        $usuarios = $query->orderBy('nombres')->paginate(15)->withQueryString();
        $roles = Rol::all();

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function create()
    {
        $roles = Rol::all();
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        return view('usuarios.create', compact('roles', 'empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string|max:100|unique:users,usuario',
            'nombres' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:4',
            'rol_id' => 'required|exists:roles,id',
            'acceso_web' => 'boolean',
            'acceso_app' => 'boolean',
            'empresas' => 'nullable|array',
            'expiracion_sesion' => 'nullable|date',
        ]);

        $user = User::create([
            'usuario' => $request->usuario,
            'nombres' => $request->nombres,
            'email' => $request->email,
            'password' => $request->password,
            'rol_id' => $request->rol_id,
            'acceso_web' => $request->boolean('acceso_web'),
            'acceso_app' => $request->boolean('acceso_app'),
            'expiracion_sesion' => $request->expiracion_sesion,
        ]);

        if ($request->has('empresas')) {
            $user->empresas()->sync($request->empresas);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $usuario)
    {
        $roles = Rol::all();
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        $asignadas = $usuario->empresas->pluck('id')->toArray();

        return view('usuarios.edit', compact('usuario', 'roles', 'empresas', 'asignadas'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'usuario' => 'required|string|max:100|unique:users,usuario,' . $usuario->id,
            'nombres' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:4',
            'rol_id' => 'required|exists:roles,id',
            'acceso_web' => 'boolean',
            'acceso_app' => 'boolean',
            'empresas' => 'nullable|array',
            'expiracion_sesion' => 'nullable|date',
        ]);

        $data = [
            'usuario' => $request->usuario,
            'nombres' => $request->nombres,
            'email' => $request->email,
            'rol_id' => $request->rol_id,
            'acceso_web' => $request->boolean('acceso_web'),
            'acceso_app' => $request->boolean('acceso_app'),
            'expiracion_sesion' => $request->expiracion_sesion,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $usuario->update($data);

        if ($request->has('empresas')) {
            $usuario->empresas()->sync($request->empresas);
        } else {
            $usuario->empresas()->detach();
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario)
    {
        $usuario->update(['eliminado' => true]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
