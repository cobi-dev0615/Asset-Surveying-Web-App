<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        // Try standard bcrypt authentication first
        try {
            if (Auth::attempt(['usuario' => $credentials['usuario'], 'password' => $credentials['password']], $request->boolean('remember'))) {
                return $this->handleSuccessfulLogin($request);
            }
        } catch (\RuntimeException $e) {
            // Laravel throws when stored hash is not bcrypt — fall through to SHA-256 check
        }

        // Fallback: SHA-256 legacy password check (production uses SHA-256)
        $user = User::where('usuario', $credentials['usuario'])->first();
        if ($user && strlen($user->password) === 64 && ctype_xdigit($user->password)) {
            if (hash('sha256', $credentials['password']) === $user->password) {
                // Match — rehash to bcrypt and save (hashed cast auto-bcrypts)
                $user->password = $credentials['password'];
                $user->save();
                Auth::login($user, $request->boolean('remember'));
                return $this->handleSuccessfulLogin($request);
            }
        }

        return back()->withErrors(['usuario' => 'Usuario o contraseña incorrectos.']);
    }

    private function handleSuccessfulLogin(Request $request)
    {
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

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
