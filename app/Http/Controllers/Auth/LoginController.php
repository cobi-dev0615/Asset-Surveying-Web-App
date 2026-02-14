<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['usuario' => $credentials['usuario'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->tieneAccesoWeb()) {
                Auth::logout();
                return back()->withErrors(['usuario' => 'No tienes acceso a la plataforma web.']);
            }

            if ($user->sesionExpirada()) {
                Auth::logout();
                return back()->withErrors(['usuario' => 'Tu sesión ha expirado. Contacta al administrador.']);
            }

            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['usuario' => 'Usuario o contraseña incorrectos.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
