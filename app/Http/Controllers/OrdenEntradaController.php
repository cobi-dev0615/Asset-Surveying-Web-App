<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoRegistro;
use App\Models\OrdenEntrada;
use App\Models\OrdenEntradaDetalle;
use App\Models\OrdenEntradaEstatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdenEntradaController extends Controller
{
    private function empresaIds()
    {
        $user = Auth::user();
        return $user->esAdmin() ? null : $user->empresas->pluck('id');
    }

    public function index(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = OrdenEntrada::where('eliminado', false)
            ->with('usuario', 'inventarioOrigen.empresa', 'inventarioOrigen.sucursal',
                   'inventarioDestino.empresa', 'inventarioDestino.sucursal', 'estatus')
            ->withCount('detalles');

        if ($empresaIds !== null) {
            $query->where(function ($q) use ($empresaIds) {
                $q->whereHas('inventarioOrigen', fn($sq) => $sq->whereIn('empresa_id', $empresaIds))
                  ->orWhereHas('inventarioDestino', fn($sq) => $sq->whereIn('empresa_id', $empresaIds));
            });
        }

        if ($request->filled('estatus_id')) {
            $query->where('estatus_id', $request->estatus_id);
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('n_orden', $request->buscar)
                  ->orWhere('motivo', 'like', "%{$request->buscar}%");
            });
        }

        $ordenes = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $estatuses = OrdenEntradaEstatus::all();

        return view('ordenes-entrada.index', compact('ordenes', 'estatuses'));
    }

    public function create()
    {
        $empresaIds = $this->empresaIds();

        $sesiones = ActivoFijoInventario::where('eliminado', false)
            ->when($empresaIds, fn($q) => $q->whereIn('empresa_id', $empresaIds))
            ->with('empresa', 'sucursal')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ordenes-entrada.create', compact('sesiones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'inventario_origen_id' => 'required|exists:activo_fijo_inventarios,id',
            'inventario_destino_id' => 'required|exists:activo_fijo_inventarios,id|different:inventario_origen_id',
            'motivo' => 'required|string|max:256',
            'comentarios' => 'nullable|string',
            'registros' => 'required|array|min:1',
            'registros.*' => 'exists:activo_fijo_registros,id',
        ]);

        DB::transaction(function () use ($request) {
            $lastOrder = OrdenEntrada::where('inventario_origen_id', $request->inventario_origen_id)
                ->max('n_orden');

            $orden = OrdenEntrada::create([
                'usuario_id' => Auth::id(),
                'n_orden' => ($lastOrder ?? 0) + 1,
                'inventario_origen_id' => $request->inventario_origen_id,
                'inventario_destino_id' => $request->inventario_destino_id,
                'motivo' => $request->motivo,
                'comentarios' => $request->comentarios,
                'estatus_id' => 1,
            ]);

            foreach ($request->registros as $registroId) {
                OrdenEntradaDetalle::create([
                    'orden_entrada_id' => $orden->id,
                    'registro_id' => $registroId,
                    'inventario_id' => $request->inventario_origen_id,
                ]);
            }
        });

        return redirect()->route('ordenes-entrada.index')
            ->with('success', 'Orden de transferencia creada exitosamente.');
    }

    public function show(OrdenEntrada $orden)
    {
        $orden->load(
            'usuario', 'estatus',
            'inventarioOrigen.empresa', 'inventarioOrigen.sucursal',
            'inventarioDestino.empresa', 'inventarioDestino.sucursal',
            'autorizador', 'surtidor', 'cancelador', 'rechazador',
            'detalles.registro'
        );

        return view('ordenes-entrada.show', compact('orden'));
    }

    public function autorizar(OrdenEntrada $orden)
    {
        if ($orden->estatus_id !== 1) {
            return back()->with('error', 'Solo se pueden autorizar órdenes con estatus Pendiente.');
        }

        $orden->update([
            'estatus_id' => 2,
            'autorizado_por' => Auth::id(),
        ]);

        return back()->with('success', 'Orden autorizada exitosamente.');
    }

    public function surtir(OrdenEntrada $orden)
    {
        if ($orden->estatus_id !== 2) {
            return back()->with('error', 'Solo se pueden surtir órdenes con estatus En proceso.');
        }

        DB::transaction(function () use ($orden) {
            $orden->update([
                'estatus_id' => 4,
                'surtido_por' => Auth::id(),
                'fecha_hora_surtido' => now(),
            ]);

            foreach ($orden->detalles as $detalle) {
                ActivoFijoRegistro::where('id', $detalle->registro_id)
                    ->update(['traspasado' => true, 'solicitado' => true]);
            }
        });

        return back()->with('success', 'Orden surtida exitosamente. Los activos han sido marcados como traspasados.');
    }

    public function rechazar(OrdenEntrada $orden)
    {
        if (!in_array($orden->estatus_id, [1, 2])) {
            return back()->with('error', 'Solo se pueden rechazar órdenes Pendientes o En proceso.');
        }

        $orden->update([
            'estatus_id' => 3,
            'rechazado_por' => Auth::id(),
        ]);

        return back()->with('success', 'Orden rechazada.');
    }

    public function cancelar(OrdenEntrada $orden)
    {
        if (in_array($orden->estatus_id, [4, 5])) {
            return back()->with('error', 'No se puede cancelar una orden ya surtida o cancelada.');
        }

        $orden->update([
            'estatus_id' => 5,
            'cancelado_por' => Auth::id(),
            'fecha_hora_cancelacion' => now(),
        ]);

        return back()->with('success', 'Orden cancelada.');
    }
}
