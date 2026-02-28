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
        $empresaId = $this->selectedEmpresaId();

        $sucursalId = $this->selectedSucursalId();

        $query = ActivoFijoProducto::where('eliminado', false)
            ->with('empresa', 'inventario.sucursal')
            ->where('empresa_id', $empresaId);

        // Scope by sucursal through inventory sessions
        if ($sucursalId) {
            $query->whereHas('inventario', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            });
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('descripcion', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo_1', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo_2', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo_3', 'like', "%{$request->buscar}%")
                  ->orWhere('tag_rfid', 'like', "%{$request->buscar}%")
                  ->orWhere('n_serie', 'like', "%{$request->buscar}%")
                  ->orWhere('marca', 'like', "%{$request->buscar}%")
                  ->orWhere('categoria_2', 'like', "%{$request->buscar}%");
            });
        }

        $sortable = ['codigo_1','codigo_2','codigo_3','tag_rfid','n_serie','n_serie_nuevo','categoria_2','descripcion','marca','forzado','traspasado'];
        $sort = in_array($request->sort, $sortable) ? $request->sort : 'codigo_1';
        $dir = $request->dir === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->per_page, [10,25,50,100]) ? (int) $request->per_page : 50;

        $productos = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        // Sessions scoped by empresa + sucursal (if selected)
        $sesionesQuery = ActivoFijoInventario::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->with('empresa', 'sucursal')
            ->orderBy('created_at', 'desc');

        if ($sucursalId) {
            $sesionesQuery->where('sucursal_id', $sucursalId);
        }

        $sesiones = $sesionesQuery->get();

        // Auto-determine inventario_id: most recent session for empresa+sucursal
        $defaultInventarioId = $sesiones->first()->id ?? null;

        // Scope categories & brands by the same sessions (empresa + sucursal)
        $sesionIds = $sesiones->pluck('id');

        $categorias = ActivoFijoProducto::where('empresa_id', $empresaId)
            ->where('eliminado', false)
            ->whereIn('inventario_id', $sesionIds)
            ->whereNotNull('categoria_2')
            ->where('categoria_2', '!=', '')
            ->distinct()
            ->orderBy('categoria_2')
            ->pluck('categoria_2');

        $marcas = ActivoFijoProducto::where('empresa_id', $empresaId)
            ->where('eliminado', false)
            ->whereIn('inventario_id', $sesionIds)
            ->whereNotNull('marca')
            ->where('marca', '!=', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');

        return view('activo-fijo-productos.index', compact('productos', 'sort', 'dir', 'perPage', 'categorias', 'marcas', 'defaultInventarioId'));
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

    public function store(Request $request)
    {
        $request->validate([
            'inventario_id' => 'required|exists:activo_fijo_inventarios,id',
            'codigo_1' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'codigo_2' => 'nullable|string|max:100',
            'codigo_3' => 'nullable|string|max:100',
            'tag_rfid' => 'nullable|string|max:255',
            'n_serie' => 'nullable|string|max:255',
            'n_serie_nuevo' => 'nullable|string|max:255',
            'categoria_1' => 'nullable|string|max:255',
            'categoria_2' => 'nullable|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        ActivoFijoProducto::create([
            'empresa_id' => $this->selectedEmpresaId(),
            'inventario_id' => $request->inventario_id,
            'codigo_1' => $request->codigo_1,
            'codigo_2' => $request->codigo_2,
            'codigo_3' => $request->codigo_3,
            'tag_rfid' => $request->tag_rfid,
            'descripcion' => $request->descripcion,
            'n_serie' => $request->n_serie,
            'n_serie_nuevo' => $request->n_serie_nuevo,
            'categoria_1' => $request->categoria_1,
            'categoria_2' => $request->categoria_2,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('activo-fijo-productos.index')
            ->with('success', 'Activo fijo creado exitosamente.');
    }

    public function edit(ActivoFijoProducto $producto)
    {
        return response()->json($producto);
    }

    public function update(Request $request, ActivoFijoProducto $producto)
    {
        $request->validate([
            'codigo_1' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'codigo_2' => 'nullable|string|max:100',
            'codigo_3' => 'nullable|string|max:100',
            'tag_rfid' => 'nullable|string|max:255',
            'n_serie' => 'nullable|string|max:255',
            'n_serie_nuevo' => 'nullable|string|max:255',
            'categoria_1' => 'nullable|string|max:255',
            'categoria_2' => 'nullable|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $producto->update($request->only([
            'codigo_1', 'codigo_2', 'codigo_3', 'tag_rfid',
            'descripcion', 'n_serie', 'n_serie_nuevo',
            'categoria_1', 'categoria_2', 'marca', 'modelo', 'observaciones',
        ]));

        return redirect()->route('activo-fijo-productos.index')
            ->with('success', 'Activo fijo actualizado exitosamente.');
    }

    public function destroy(ActivoFijoProducto $producto)
    {
        $producto->update(['eliminado' => true]);

        return redirect()->route('activo-fijo-productos.index')
            ->with('success', 'Activo fijo eliminado exitosamente.');
    }

    public function importForm()
    {
        $empresaId = $this->selectedEmpresaId();
        $empresas = Empresa::where('id', $empresaId)->get();
        $sesiones = ActivoFijoInventario::where('eliminado', false)
            ->where('empresa_id', $empresaId)
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
