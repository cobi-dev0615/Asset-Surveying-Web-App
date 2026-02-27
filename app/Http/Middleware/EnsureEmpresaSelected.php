<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpresaSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('selected_empresa_id')) {
            return redirect('/seleccionar-empresa');
        }

        return $next($request);
    }
}
