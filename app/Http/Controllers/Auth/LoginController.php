<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
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
            'rol_tipo' => 'nullable|string',
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

        return back()->withErrors(['usuario' => 'Usuario o contraseña incorrectos.'])->withInput($request->only('usuario', 'rol_tipo'));
    }

    private function handleSuccessfulLogin(Request $request)
    {
        $user = Auth::user();

        if (!$user->tieneAccesoWeb()) {
            Auth::logout();
            return back()->withErrors(['usuario' => 'No tienes acceso a la plataforma web. Contacta al administrador.'])->withInput($request->only('usuario', 'rol_tipo'));
        }

        if ($user->sesionExpirada()) {
            Auth::logout();
            return back()->withErrors(['usuario' => 'Tu sesión ha expirado. Contacta al administrador.'])->withInput($request->only('usuario', 'rol_tipo'));
        }

        // Validate role type matches if provided
        $rolTipo = $request->input('rol_tipo');
        if ($rolTipo && $user->rol->slug !== $rolTipo) {
            Auth::logout();
            return back()->withErrors(['usuario' => 'Tu cuenta no tiene el rol seleccionado. Contacta al administrador.'])->withInput($request->only('usuario', 'rol_tipo'));
        }

        $request->session()->regenerate();

        // Role-based redirect
        $redirectMap = [
            'super_admin' => '/dashboard',
            'supervisor' => '/dashboard',
            'capturista' => '/dashboard',
            'supervisor_invitado' => '/transferencias/solicitadas',
        ];

        $route = $redirectMap[$user->rol->slug] ?? '/dashboard';
        return redirect()->intended($route);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showRegister()
    {
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        return view('auth.register', compact('empresas'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string|max:100|unique:users,usuario',
            'nombres' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:4|confirmed',
            'empresas' => 'required|array|min:1',
            'empresas.*' => 'exists:empresas,id',
        ], [
            'usuario.unique' => 'Este usuario ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 4 caracteres.',
            'empresas.required' => 'Debes seleccionar al menos una empresa.',
            'empresas.min' => 'Debes seleccionar al menos una empresa.',
        ]);

        $user = User::create([
            'usuario' => $request->usuario,
            'nombres' => $request->nombres,
            'email' => $request->email,
            'password' => $request->password,
            'rol_id' => 3, // Capturista by default
            'acceso_web' => false,
            'acceso_app' => false,
            'expiracion_sesion' => '2999-12-31',
        ]);

        $user->empresas()->sync($request->empresas);

        return redirect()->route('login')->with('success', 'Cuenta creada exitosamente. Un administrador debe activar tu acceso antes de que puedas iniciar sesión.');
    }
}
