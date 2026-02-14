<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoProducto;
use App\Models\Empresa;
use Illuminate\Http\Request;

class ActivoFijoProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivoFijoProducto::where('eliminado', false)
            ->with('empresa', 'inventario.sucursal');

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('descripcion', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo_1', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo_2', 'like', "%{$request->buscar}%")
                  ->orWhere('marca', 'like', "%{$request->buscar}%")
                  ->orWhere('categoria_2', 'like', "%{$request->buscar}%");
            });
        }

        $productos = $query->orderBy('codigo_1')->paginate(20)->withQueryString();
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        $sesiones = ActivoFijoInventario::where('eliminado', false)
            ->with('empresa', 'sucursal')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('activo-fijo-productos.index', compact('productos', 'empresas', 'sesiones'));
    }

    public function show(ActivoFijoProducto $producto)
    {
        $producto->load('empresa', 'inventario.sucursal');

        $registros = $producto->registros()
            ->where('eliminado', false)
            ->with('usuario', 'inventario')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('activo-fijo-productos.show', compact('producto', 'registros'));
    }

    public function importForm()
    {
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        $sesiones = ActivoFijoInventario::where('eliminado', false)
            ->with('empresa', 'sucursal')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('activo-fijo-productos.import', compact('empresas', 'sesiones'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'inventario_id' => 'required|exists:activo_fijo_inventarios,id',
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();

            if (in_array($extension, ['xlsx', 'xls'])) {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getPathname());
                $spreadsheet = $reader->load($file->getPathname());
                $rows = $spreadsheet->getActiveSheet()->toArray();
            } else {
                $rows = array_map('str_getcsv', file($file->getPathname()));
            }

            $header = array_shift($rows);
            $header = array_map(fn($h) => strtolower(trim($h ?? '')), $header);

            $count = 0;
            foreach ($rows as $row) {
                if (empty(array_filter($row))) continue;

                $mapped = array_combine($header, array_pad($row, count($header), null));

                ActivoFijoProducto::create([
                    'empresa_id' => $request->empresa_id,
                    'inventario_id' => $request->inventario_id,
                    'codigo_1' => $mapped['codigo_1'] ?? $mapped['codigo'] ?? $mapped['numero_activo'] ?? '',
                    'codigo_2' => $mapped['codigo_2'] ?? $mapped['tag'] ?? '',
                    'codigo_3' => $mapped['codigo_3'] ?? '',
                    'descripcion' => $mapped['descripcion'] ?? $mapped['nombre'] ?? '',
                    'categoria_1' => $mapped['categoria_1'] ?? $mapped['categoria'] ?? '',
                    'categoria_2' => $mapped['categoria_2'] ?? $mapped['subcategoria'] ?? '',
                    'marca' => $mapped['marca'] ?? '',
                    'modelo' => $mapped['modelo'] ?? '',
                    'n_serie' => $mapped['n_serie'] ?? $mapped['serie'] ?? '',
                    'cantidad_teorica' => $mapped['cantidad_teorica'] ?? $mapped['cantidad'] ?? 0,
                ]);
                $count++;
            }

            return redirect()->route('activo-fijo-productos.index')
                ->with('success', "Se importaron $count activos fijos exitosamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }
}
