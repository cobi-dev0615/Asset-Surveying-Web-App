<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmpresaSelectionController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        $empresas = Empresa::where('eliminado', false)
            ->when(!$user->esAdmin(), fn($q) => $q->whereIn('id', $user->empresas->pluck('id')))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('seleccionar-empresa', compact('empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $empresa = Empresa::find($request->empresa_id);
        $sucursal = $request->sucursal_id ? Sucursal::find($request->sucursal_id) : null;

        $request->session()->put('selected_empresa_id', $empresa->id);
        $request->session()->put('selected_empresa_nombre', $empresa->nombre);

        if ($sucursal) {
            $request->session()->put('selected_sucursal_id', $sucursal->id);
            $request->session()->put('selected_sucursal_nombre', $sucursal->nombre);
        } else {
            $request->session()->forget(['selected_sucursal_id', 'selected_sucursal_nombre']);
        }

        // Determine which modules have data for this empresa
        $this->setModuleFlags($request, $empresa->id, $sucursal?->id);

        return redirect('/dashboard');
    }

    public function cambiar()
    {
        session()->forget([
            'selected_empresa_id',
            'selected_empresa_nombre',
            'selected_sucursal_id',
            'selected_sucursal_nombre',
            'has_activo_fijo',
            'has_productos_ssr',
            'has_inventarios_ssr',
            'has_transferencias',
        ]);

        return redirect('/seleccionar-empresa');
    }

    private function setModuleFlags(Request $request, int $empresaId, ?int $sucursalId): void
    {
        // Activo Fijo: sessions exist for this empresa
        $afQuery = DB::table('activo_fijo_inventarios')
            ->where('empresa_id', $empresaId)
            ->where('eliminado', false);
        if ($sucursalId) {
            $afQuery->where('sucursal_id', $sucursalId);
        }
        $hasActivoFijo = $afQuery->exists();

        // Productos SSR: products exist for this empresa
        $hasProductosSSR = DB::table('productos')
            ->where('empresa_id', $empresaId)
            ->where('eliminado', false)
            ->exists();

        // Inventarios SSR: sessions exist for this empresa
        $hasInventariosSSR = DB::table('inventarios')
            ->where('empresa_id', $empresaId)
            ->where('eliminado', false)
            ->exists();

        // Transferencias: any transfer orders referencing this empresa's sessions
        $hasTransferencias = DB::table('ordenes_entrada')
            ->where('eliminado', false)
            ->where(function ($q) use ($empresaId) {
                $q->whereIn('inventario_origen_id', function ($sub) use ($empresaId) {
                    $sub->select('id')->from('activo_fijo_inventarios')->where('empresa_id', $empresaId);
                })->orWhereIn('inventario_destino_id', function ($sub) use ($empresaId) {
                    $sub->select('id')->from('activo_fijo_inventarios')->where('empresa_id', $empresaId);
                });
            })
            ->exists();

        $request->session()->put('has_activo_fijo', $hasActivoFijo);
        $request->session()->put('has_productos_ssr', $hasProductosSSR);
        $request->session()->put('has_inventarios_ssr', $hasInventariosSSR);
        $request->session()->put('has_transferencias', $hasTransferencias);
    }
}
