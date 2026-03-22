<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'required|string',
        ]);

        $user = User::where('usuario', $request->usuario)->first();

        if (!$user) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        // Try bcrypt first
        $authenticated = false;
        try {
            if (Hash::check($request->password, $user->password)) {
                $authenticated = true;
            }
        } catch (\RuntimeException $e) {
            // Not a valid bcrypt hash — fall through to SHA-256 check
        }

        // Fallback: SHA-256 legacy password check (production uses SHA-256)
        if (!$authenticated && strlen($user->password) === 64 && ctype_xdigit($user->password)) {
            if (hash('sha256', $request->password) === $user->password) {
                // Match — rehash to bcrypt and save (hashed cast auto-bcrypts)
                $user->password = $request->password;
                $user->save();
                $authenticated = true;
            }
        }

        if (!$authenticated) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        if (!$user->tieneAccesoApp()) {
            return response()->json(['message' => 'No tienes acceso a la aplicación móvil.'], 403);
        }

        if ($user->sesionExpirada()) {
            return response()->json(['message' => 'Tu sesión ha expirado.'], 403);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'usuario' => $user->usuario,
                'nombres' => $user->nombres,
                'rol' => $user->rol->slug,
                'rol_nombre' => $user->rol->nombre,
                'empresas' => $user->empresas->map(fn ($e) => [
                    'id' => $e->id,
                    'codigo' => $e->codigo,
                    'nombre' => $e->nombre,
                ]),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('rol', 'empresas');

        return response()->json([
            'id' => $user->id,
            'usuario' => $user->usuario,
            'nombres' => $user->nombres,
            'rol' => $user->rol->slug,
            'rol_nombre' => $user->rol->nombre,
            'acceso_web' => $user->acceso_web,
            'acceso_app' => $user->acceso_app,
            'empresas' => $user->empresas->map(fn ($e) => [
                'id' => $e->id,
                'codigo' => $e->codigo,
                'nombre' => $e->nombre,
            ]),
        ]);
    }
}
