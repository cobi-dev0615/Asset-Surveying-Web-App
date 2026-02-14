<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::where('eliminado', false)->with('empresa');

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('descripcion', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo_1', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo_2', 'like', "%{$request->buscar}%")
                  ->orWhere('marca', 'like', "%{$request->buscar}%");
            });
        }

        $productos = $query->orderBy('descripcion')->paginate(20)->withQueryString();
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'empresas'));
    }

    public function create()
    {
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        return view('productos.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'codigo_1' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
        ]);

        Producto::create($request->only([
            'empresa_id', 'codigo_1', 'codigo_2', 'codigo_3', 'codigo_4', 'codigo_5',
            'descripcion', 'marca', 'modelo', 'categoria', 'subcategoria',
            'precio_compra', 'precio_venta', 'cantidad_teorica', 'unidad_medida',
        ]));

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Producto $producto)
    {
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        return view('productos.edit', compact('producto', 'empresas'));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'codigo_1' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
        ]);

        $producto->update($request->only([
            'empresa_id', 'codigo_1', 'codigo_2', 'codigo_3', 'codigo_4', 'codigo_5',
            'descripcion', 'marca', 'modelo', 'categoria', 'subcategoria',
            'precio_compra', 'precio_venta', 'cantidad_teorica', 'unidad_medida',
        ]));

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['eliminado' => true]);
        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
    }

    public function importForm()
    {
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        return view('productos.import', compact('empresas'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
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

                Producto::create([
                    'empresa_id' => $request->empresa_id,
                    'codigo_1' => $mapped['codigo_1'] ?? $mapped['codigo'] ?? $mapped['sku'] ?? '',
                    'codigo_2' => $mapped['codigo_2'] ?? null,
                    'codigo_3' => $mapped['codigo_3'] ?? null,
                    'descripcion' => $mapped['descripcion'] ?? $mapped['nombre'] ?? '',
                    'marca' => $mapped['marca'] ?? null,
                    'modelo' => $mapped['modelo'] ?? null,
                    'categoria' => $mapped['categoria'] ?? null,
                    'subcategoria' => $mapped['subcategoria'] ?? null,
                    'precio_compra' => $mapped['precio_compra'] ?? 0,
                    'precio_venta' => $mapped['precio_venta'] ?? 0,
                    'cantidad_teorica' => $mapped['cantidad_teorica'] ?? $mapped['existencia'] ?? 0,
                    'unidad_medida' => $mapped['unidad_medida'] ?? $mapped['unidad'] ?? null,
                ]);
                $count++;
            }

            return redirect()->route('productos.index')->with('success', "Se importaron $count productos exitosamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }
}
