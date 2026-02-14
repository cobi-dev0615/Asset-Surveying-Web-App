<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\InventarioDetalle;
use App\Models\InventarioRegistro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioApiController extends Controller
{
    public function index(Request $request)
    {
        $inventarios = Inventario::where('eliminado', false)
            ->whereIn('empresa_id', $request->user()->empresas->pluck('id'))
            ->with('sucursal', 'status')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($inventarios);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'inventario_id' => 'required|integer|exists:inventarios,id',
            'registros' => 'required|array',
            'registros.*.producto_id' => 'nullable|integer',
            'registros.*.cantidad' => 'required|numeric',
            'registros.*.codigo_1' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $inventario = Inventario::findOrFail($request->inventario_id);
            $count = 0;

            foreach ($request->registros as $data) {
                $registro = InventarioRegistro::create([
                    'inventario_id' => $inventario->id,
                    'usuario_id' => $request->user()->id,
                    'producto_id' => $data['producto_id'] ?? null,
                    'cantidad' => $data['cantidad'],
                    'codigo_1' => $data['codigo_1'],
                    'codigo_2' => $data['codigo_2'] ?? null,
                    'codigo_3' => $data['codigo_3'] ?? null,
                    'ubicacion_1' => $data['ubicacion_1'] ?? null,
                    'nombre_almacen' => $data['nombre_almacen'] ?? null,
                    'almacen_id' => $data['almacen_id'] ?? null,
                    'lote' => $data['lote'] ?? null,
                    'fecha_caducidad' => $data['fecha_caducidad'] ?? null,
                    'precio_compra' => $data['precio_compra'] ?? 0,
                    'precio_venta' => $data['precio_venta'] ?? 0,
                    'unidad_medida' => $data['unidad_medida'] ?? null,
                    'cantidad_teorica' => $data['cantidad_teorica'] ?? 0,
                    'forzado' => $data['forzado'] ?? false,
                    'sincronizado' => true,
                ]);

                if (isset($data['detalles'])) {
                    foreach ($data['detalles'] as $detalle) {
                        InventarioDetalle::create(array_merge($detalle, [
                            'registro_id' => $registro->id,
                            'inventario_id' => $inventario->id,
                            'usuario_id' => $request->user()->id,
                        ]));
                    }
                }

                $count++;
            }

            DB::commit();

            return response()->json([
                'message' => "Se sincronizaron $count registros.",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al sincronizar: ' . $e->getMessage()], 500);
        }
    }
}
