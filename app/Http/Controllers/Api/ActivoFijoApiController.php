<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoProducto;
use App\Models\ActivoFijoRegistro;
use App\Models\ActivoNoEncontrado;
use App\Models\ActivoTraspasado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'empresa_id' => 'required|integer|exists:empresas,id',
            'sucursal_id' => 'required|integer|exists:sucursales,id',
        ]);

        $inventario = ActivoFijoInventario::create([
            'nombre' => $request->nombre,
            'empresa_id' => $request->empresa_id,
            'sucursal_id' => $request->sucursal_id,
            'usuario_id' => $request->user()->id,
            'status_id' => 1,
            'eliminado' => false,
            'finalizado' => false,
        ]);

        $inventario->load('sucursal', 'empresa');

        return response()->json($inventario, 201);
    }

    public function productos(Request $request)
    {
        $empresaIds = $request->user()->empresas->pluck('id');

        $query = ActivoFijoProducto::where('eliminado', false)
            ->whereIn('empresa_id', $empresaIds);

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        return response()->json(
            $query->select([
                'id', 'inventario_id', 'empresa_id', 'codigo_1', 'codigo_2', 'codigo_3',
                'tag_rfid', 'descripcion', 'n_serie', 'categoria_1', 'categoria_2',
                'marca', 'modelo', 'tipo_activo',
            ])
            ->orderBy('id')
            ->paginate($request->integer('per_page', 500))
        );
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
                $registro = ActivoFijoRegistro::create([
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

                // Save base64 images to disk
                $this->saveBase64Images($registro, $data, $request->inventario_id);

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

    public function uploadImagen(Request $request)
    {
        $request->validate([
            'registro_id' => 'required|integer|exists:activo_fijo_registros,id',
            'campo' => 'required|in:imagen1,imagen2,imagen3',
        ]);

        $registro = ActivoFijoRegistro::findOrFail($request->registro_id);

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store(
                'fotos/activos/' . $registro->inventario_id,
                'public'
            );
            $registro->update([$request->campo => $path]);

            return response()->json(['message' => 'Imagen subida.', 'path' => $path]);
        }

        if ($request->filled('imagen_base64')) {
            $path = $this->saveBase64($request->imagen_base64, $registro->inventario_id, $request->campo);
            if ($path) {
                $registro->update([$request->campo => $path]);
                return response()->json(['message' => 'Imagen subida.', 'path' => $path]);
            }
        }

        return response()->json(['message' => 'No se proporcionó imagen.'], 422);
    }

    private function saveBase64Images(ActivoFijoRegistro $registro, array $data, int $inventarioId): void
    {
        foreach (['imagen1', 'imagen2', 'imagen3'] as $field) {
            $value = $data[$field] ?? null;
            if (!empty($value) && strlen($value) > 200) {
                $path = $this->saveBase64($value, $inventarioId, $field);
                if ($path) {
                    $registro->update([$field => $path]);
                }
            }
        }
    }

    private function saveBase64(string $base64, int $inventarioId, string $field): ?string
    {
        // Strip data URI prefix if present
        if (str_contains($base64, ',')) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
        }

        $imageData = base64_decode($base64, true);
        if ($imageData === false) {
            return null;
        }

        $filename = 'fotos/activos/' . $inventarioId . '/' . uniqid() . '_' . $field . '.jpg';
        Storage::disk('public')->put($filename, $imageData);

        return $filename;
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

    public function uploadRfidTags(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:activo_fijo_inventarios,id',
            'tags' => 'required|array',
            'tags.*.epc' => 'required|string',
        ]);

        $count = 0;

        foreach ($request->tags as $data) {
            DB::table('activo_fijo_rfid_tags')->insert([
                'session_id' => $request->session_id,
                'epc' => $data['epc'],
                'rssi' => $data['rssi'] ?? 0,
                'read_count' => $data['read_count'] ?? 1,
                'matched' => $data['matched'] ?? false,
                'matched_registro_id' => $data['matched_registro_id'] ?? null,
                'scanned_at' => $data['timestamp'] ?? now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        return response()->json([
            'message' => "Se sincronizaron $count tags RFID.",
            'count' => $count,
        ]);
    }
}
