<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoRegistro;
use App\Models\ActivoNoEncontrado;
use App\Models\ActivoTraspasado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivoFijoApiController extends Controller
{
    public function index(Request $request)
    {
        $inventarios = ActivoFijoInventario::where('eliminado', false)
            ->whereIn('empresa_id', $request->user()->empresas->pluck('id'))
            ->with('sucursal', 'status')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($inventarios);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'inventario_id' => 'required|integer|exists:activo_fijo_inventarios,id',
            'registros' => 'required|array',
            'registros.*.codigo_1' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $count = 0;

            foreach ($request->registros as $data) {
                ActivoFijoRegistro::create([
                    'inventario_id' => $request->inventario_id,
                    'usuario_id' => $request->user()->id,
                    'id_producto' => $data['id_producto'] ?? 0,
                    'codigo_1' => $data['codigo_1'],
                    'codigo_1_anterior' => $data['codigo_1_anterior'] ?? null,
                    'codigo_2' => $data['codigo_2'] ?? null,
                    'codigo_3' => $data['codigo_3'] ?? null,
                    'tag_rfid' => $data['tag_rfid'] ?? null,
                    'n_serie' => $data['n_serie'] ?? null,
                    'n_serie_anterior' => $data['n_serie_anterior'] ?? null,
                    'n_serie_nuevo' => $data['n_serie_nuevo'] ?? null,
                    'nombre_almacen' => $data['nombre_almacen'] ?? null,
                    'ubicacion_1' => $data['ubicacion_1'] ?? null,
                    'categoria' => $data['categoria'] ?? null,
                    'descripcion' => $data['descripcion'] ?? null,
                    'imagen1' => $data['imagen1'] ?? null,
                    'imagen2' => $data['imagen2'] ?? null,
                    'imagen3' => $data['imagen3'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                    'traspasado' => $data['traspasado'] ?? false,
                    'sucursal_origen' => $data['sucursal_origen'] ?? null,
                    'forzado' => $data['forzado'] ?? false,
                    'latitud' => $data['latitud'] ?? 0,
                    'longitud' => $data['longitud'] ?? 0,
                    'version_app' => $data['version_app'] ?? null,
                ]);
                $count++;
            }

            DB::commit();

            return response()->json([
                'message' => "Se sincronizaron $count registros de activo fijo.",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function uploadNoEncontrados(Request $request)
    {
        $request->validate([
            'inventario_id' => 'required|integer|exists:activo_fijo_inventarios,id',
            'activos' => 'required|array',
            'activos.*.activo' => 'required|integer',
        ]);

        foreach ($request->activos as $data) {
            ActivoNoEncontrado::create([
                'inventario_id' => $request->inventario_id,
                'activo' => $data['activo'],
                'usuario_id' => $request->user()->id,
                'latitud' => $data['latitud'] ?? 0,
                'longitud' => $data['longitud'] ?? 0,
            ]);
        }

        return response()->json(['message' => 'Activos no encontrados registrados.']);
    }

    public function uploadTraspasos(Request $request)
    {
        $request->validate([
            'traspasos' => 'required|array',
            'traspasos.*.activo' => 'required|integer',
            'traspasos.*.sucursal_origen_id' => 'required|integer|exists:sucursales,id',
            'traspasos.*.sucursal_destino_id' => 'required|integer|exists:sucursales,id',
        ]);

        foreach ($request->traspasos as $data) {
            ActivoTraspasado::create([
                'activo' => $data['activo'],
                'sucursal_origen_id' => $data['sucursal_origen_id'],
                'sucursal_destino_id' => $data['sucursal_destino_id'],
                'usuario_id' => $request->user()->id,
            ]);
        }

        return response()->json(['message' => 'Traspasos registrados.']);
    }
}
