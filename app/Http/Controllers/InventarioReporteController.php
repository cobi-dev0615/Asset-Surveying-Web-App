<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\InventarioDetalle;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventarioReporteController extends Controller
{
    /**
     * Helper: base query filtered by empresa/sucursal/inventario
     */
    private function baseQuery(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        $query = InventarioDetalle::where('inventario_detalle.eliminado', false)
            ->whereHas('inventario', function ($q) use ($empresaId, $sucursalId) {
                $q->where('empresa_id', $empresaId)->where('eliminado', false);
                if ($sucursalId) $q->where('sucursal_id', $sucursalId);
            });

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo_1', 'like', "%{$buscar}%")
                  ->orWhere('codigo_2', 'like', "%{$buscar}%")
                  ->orWhere('codigo_3', 'like', "%{$buscar}%")
                  ->orWhere('inventario_detalle.nombre_almacen', 'like', "%{$buscar}%")
                  ->orWhere('ubicacion_1', 'like', "%{$buscar}%")
                  ->orWhere('nombre_usuario', 'like', "%{$buscar}%");
            });
        }

        return $query;
    }

    private function getSesiones()
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        return Inventario::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
            ->orderBy('id', 'desc')->get();
    }

    private function paginationParams(Request $request): array
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 50;
        $sort = $request->sort ?? 'created_at';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';
        return [$perPage, $sort, $dir];
    }

    // ─── 1. Agrupado por producto ─────────────────────────────────
    public function agrupadoProducto(Request $request)
    {
        [$perPage, $sort, $dir] = $this->paginationParams($request);
        $allowedSorts = ['codigo_1', 'codigo_2', 'descripcion', 'cantidad_total', 'precio_venta', 'importe'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'cantidad_total';

        $query = $this->baseQuery($request)
            ->join('productos', 'inventario_detalle.producto_id', '=', 'productos.id', 'left')
            ->select(
                'inventario_detalle.codigo_1',
                'inventario_detalle.codigo_2',
                DB::raw("COALESCE(productos.descripcion, inventario_detalle.codigo_1) as descripcion"),
                DB::raw('SUM(inventario_detalle.cantidad) as cantidad_total'),
                DB::raw('COALESCE(MAX(productos.precio_venta), 0) as precio_venta'),
                DB::raw('SUM(inventario_detalle.cantidad) * COALESCE(MAX(productos.precio_venta), 0) as importe')
            )
            ->groupBy('inventario_detalle.codigo_1', 'inventario_detalle.codigo_2', 'descripcion');

        $registros = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        // Totals
        $totales = $this->baseQuery($request)
            ->join('productos', 'inventario_detalle.producto_id', '=', 'productos.id', 'left')
            ->selectRaw('
                COUNT(*) as total_registros,
                COUNT(DISTINCT inventario_detalle.codigo_1) as total_productos,
                SUM(inventario_detalle.cantidad) as conteo_total,
                SUM(inventario_detalle.cantidad * COALESCE(productos.precio_venta, 0)) as valor_inventario,
                COUNT(DISTINCT inventario_detalle.nombre_almacen) as almacenes,
                COUNT(DISTINCT inventario_detalle.ubicacion_1) as ubicaciones
            ')->first();

        $sesiones = $this->getSesiones();

        return view('inventario-reportes.agrupado-producto', compact('registros', 'sesiones', 'totales', 'sort', 'dir', 'perPage'));
    }

    // ─── 2. Agrupado por producto y ubicación ─────────────────────
    public function agrupadoProductoUbicacion(Request $request)
    {
        [$perPage, $sort, $dir] = $this->paginationParams($request);
        $allowedSorts = ['codigo_1', 'codigo_2', 'descripcion', 'cantidad_total', 'precio_venta', 'importe', 'nombre_almacen', 'ubicacion_1'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'cantidad_total';

        $query = $this->baseQuery($request)
            ->join('productos', 'inventario_detalle.producto_id', '=', 'productos.id', 'left')
            ->select(
                'inventario_detalle.codigo_1',
                'inventario_detalle.codigo_2',
                DB::raw("COALESCE(productos.descripcion, inventario_detalle.codigo_1) as descripcion"),
                DB::raw('SUM(inventario_detalle.cantidad) as cantidad_total'),
                DB::raw('COALESCE(MAX(productos.precio_venta), 0) as precio_venta'),
                DB::raw('SUM(inventario_detalle.cantidad) * COALESCE(MAX(productos.precio_venta), 0) as importe'),
                'inventario_detalle.nombre_almacen',
                'inventario_detalle.ubicacion_1'
            )
            ->groupBy('inventario_detalle.codigo_1', 'inventario_detalle.codigo_2', 'descripcion', 'inventario_detalle.nombre_almacen', 'inventario_detalle.ubicacion_1');

        $registros = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        $totales = $this->baseQuery($request)
            ->join('productos', 'inventario_detalle.producto_id', '=', 'productos.id', 'left')
            ->selectRaw('
                COUNT(*) as total_registros,
                COUNT(DISTINCT inventario_detalle.codigo_1) as total_productos,
                SUM(inventario_detalle.cantidad) as conteo_total,
                SUM(inventario_detalle.cantidad * COALESCE(productos.precio_venta, 0)) as valor_inventario,
                COUNT(DISTINCT inventario_detalle.nombre_almacen) as almacenes,
                COUNT(DISTINCT inventario_detalle.ubicacion_1) as ubicaciones
            ')->first();

        $sesiones = $this->getSesiones();

        return view('inventario-reportes.agrupado-producto-ubicacion', compact('registros', 'sesiones', 'totales', 'sort', 'dir', 'perPage'));
    }

    // ─── 3. Detalle ───────────────────────────────────────────────
    public function detalle(Request $request)
    {
        [$perPage, $sort, $dir] = $this->paginationParams($request);
        $allowedSorts = ['cantidad', 'codigo_1', 'codigo_2', 'codigo_3', 'nombre_almacen', 'ubicacion_1', 'nombre_usuario', 'fecha_captura', 'hora_captura', 'n_conteo', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'created_at';

        $query = $this->baseQuery($request)
            ->join('productos', 'inventario_detalle.producto_id', '=', 'productos.id', 'left')
            ->select(
                'inventario_detalle.*',
                DB::raw("COALESCE(productos.descripcion, inventario_detalle.codigo_1) as producto_descripcion"),
                DB::raw("COALESCE(productos.precio_venta, 0) as producto_precio_venta")
            );

        $registros = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        $totales = $this->baseQuery($request)->selectRaw('
            COUNT(*) as total_registros,
            COUNT(DISTINCT codigo_1) as total_productos,
            SUM(cantidad) as conteo_total,
            COUNT(DISTINCT nombre_almacen) as almacenes,
            COUNT(DISTINCT ubicacion_1) as ubicaciones
        ')->first();

        $sesiones = $this->getSesiones();

        return view('inventario-reportes.detalle', compact('registros', 'sesiones', 'totales', 'sort', 'dir', 'perPage'));
    }

    // ─── 4. Diferencias (teórico vs real) ─────────────────────────
    public function diferencias(Request $request)
    {
        [$perPage, $sort, $dir] = $this->paginationParams($request);
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        $allowedSorts = ['codigo_1', 'codigo_2', 'descripcion', 'precio_venta', 'cantidad_teorica', 'cantidad_real', 'diferencia_cantidad', 'diferencia_importe'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'diferencia_cantidad';

        // Get real counts from inventario_detalle grouped by product
        $subQuery = InventarioDetalle::where('inventario_detalle.eliminado', false)
            ->whereHas('inventario', function ($q) use ($empresaId, $sucursalId, $request) {
                $q->where('empresa_id', $empresaId)->where('eliminado', false);
                if ($sucursalId) $q->where('sucursal_id', $sucursalId);
                if ($request->filled('inventario_id')) $q->where('id', $request->inventario_id);
            })
            ->select('codigo_1', DB::raw('SUM(cantidad) as cantidad_real'))
            ->groupBy('codigo_1');

        // Join with productos catalog to get theoretical quantities
        $query = Producto::where('productos.empresa_id', $empresaId)
            ->where('productos.eliminado', false)
            ->joinSub($subQuery, 'conteo', function ($join) {
                $join->on('productos.codigo_1', '=', 'conteo.codigo_1');
            })
            ->select(
                'productos.codigo_1',
                'productos.codigo_2',
                'productos.descripcion',
                'productos.precio_venta',
                'productos.cantidad_teorica',
                'conteo.cantidad_real',
                DB::raw('(conteo.cantidad_real - productos.cantidad_teorica) as diferencia_cantidad'),
                DB::raw('((conteo.cantidad_real - productos.cantidad_teorica) * productos.precio_venta) as diferencia_importe')
            );

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('productos.codigo_1', 'like', "%{$buscar}%")
                  ->orWhere('productos.codigo_2', 'like', "%{$buscar}%")
                  ->orWhere('productos.descripcion', 'like', "%{$buscar}%");
            });
        }

        $registros = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        // Summary totals
        $totalesQuery = Producto::where('productos.empresa_id', $empresaId)
            ->where('productos.eliminado', false)
            ->joinSub($subQuery, 'conteo', function ($join) {
                $join->on('productos.codigo_1', '=', 'conteo.codigo_1');
            });

        $totales = $totalesQuery->selectRaw('
            SUM(conteo.cantidad_real) as inventario_real,
            SUM(productos.cantidad_teorica) as inventario_teorico,
            SUM(conteo.cantidad_real - productos.cantidad_teorica) as diferencia,
            SUM(conteo.cantidad_real * productos.precio_venta) as valor_real,
            SUM(productos.cantidad_teorica * productos.precio_venta) as valor_teorico,
            SUM((conteo.cantidad_real - productos.cantidad_teorica) * productos.precio_venta) as valor_diferencia
        ')->first();

        $sesiones = $this->getSesiones();

        return view('inventario-reportes.diferencias', compact('registros', 'sesiones', 'totales', 'sort', 'dir', 'perPage'));
    }

    // ─── 5. Reporte GNC (tienda + UPC + cantidad) ─────────────────
    public function gnc(Request $request)
    {
        [$perPage, $sort, $dir] = $this->paginationParams($request);
        $allowedSorts = ['nombre_almacen', 'codigo_1', 'cantidad_total'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'nombre_almacen';

        $query = $this->baseQuery($request)
            ->select(
                'nombre_almacen',
                'codigo_1',
                DB::raw('SUM(cantidad) as cantidad_total')
            )
            ->groupBy('nombre_almacen', 'codigo_1');

        $registros = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        $totales = $this->baseQuery($request)->selectRaw('
            COUNT(*) as total_registros,
            COUNT(DISTINCT codigo_1) as total_productos,
            SUM(cantidad) as conteo_total,
            COUNT(DISTINCT nombre_almacen) as almacenes
        ')->first();

        $sesiones = $this->getSesiones();

        return view('inventario-reportes.gnc', compact('registros', 'sesiones', 'totales', 'sort', 'dir', 'perPage'));
    }

    // ─── Excel Exports ────────────────────────────────────────────

    public function exportAgrupadoProducto(Request $request)
    {
        $query = $this->baseQuery($request)
            ->join('productos', 'inventario_detalle.producto_id', '=', 'productos.id', 'left')
            ->select(
                'inventario_detalle.codigo_1',
                'inventario_detalle.codigo_2',
                DB::raw("COALESCE(productos.descripcion, inventario_detalle.codigo_1) as descripcion"),
                DB::raw('SUM(inventario_detalle.cantidad) as cantidad_total'),
                DB::raw('COALESCE(MAX(productos.precio_venta), 0) as precio_venta'),
                DB::raw('SUM(inventario_detalle.cantidad) * COALESCE(MAX(productos.precio_venta), 0) as importe')
            )
            ->groupBy('inventario_detalle.codigo_1', 'inventario_detalle.codigo_2', 'descripcion')
            ->orderBy('cantidad_total', 'desc')
            ->get();

        return $this->generateExcel('Agrupado_Producto', ['Cantidad', 'Código 1', 'SKU', 'Descripción', 'Precio Venta', 'Importe'], $query->map(fn ($r) => [
            $r->cantidad_total, $r->codigo_1, $r->codigo_2, $r->descripcion, $r->precio_venta, $r->importe,
        ])->toArray());
    }

    public function exportAgrupadoProductoUbicacion(Request $request)
    {
        $query = $this->baseQuery($request)
            ->join('productos', 'inventario_detalle.producto_id', '=', 'productos.id', 'left')
            ->select(
                'inventario_detalle.codigo_1',
                'inventario_detalle.codigo_2',
                DB::raw("COALESCE(productos.descripcion, inventario_detalle.codigo_1) as descripcion"),
                DB::raw('SUM(inventario_detalle.cantidad) as cantidad_total'),
                DB::raw('COALESCE(MAX(productos.precio_venta), 0) as precio_venta'),
                DB::raw('SUM(inventario_detalle.cantidad) * COALESCE(MAX(productos.precio_venta), 0) as importe'),
                'inventario_detalle.nombre_almacen',
                'inventario_detalle.ubicacion_1'
            )
            ->groupBy('inventario_detalle.codigo_1', 'inventario_detalle.codigo_2', 'descripcion', 'inventario_detalle.nombre_almacen', 'inventario_detalle.ubicacion_1')
            ->orderBy('cantidad_total', 'desc')
            ->get();

        return $this->generateExcel('Agrupado_Producto_Ubicacion', ['Cantidad', 'Código 1', 'SKU', 'Descripción', 'Precio Venta', 'Importe', 'Almacén', 'Ubicación'], $query->map(fn ($r) => [
            $r->cantidad_total, $r->codigo_1, $r->codigo_2, $r->descripcion, $r->precio_venta, $r->importe, $r->nombre_almacen, $r->ubicacion_1,
        ])->toArray());
    }

    public function exportDetalle(Request $request)
    {
        $query = $this->baseQuery($request)
            ->join('productos', 'inventario_detalle.producto_id', '=', 'productos.id', 'left')
            ->select(
                'inventario_detalle.*',
                DB::raw("COALESCE(productos.descripcion, inventario_detalle.codigo_1) as producto_descripcion"),
                DB::raw("COALESCE(productos.precio_venta, 0) as producto_precio_venta")
            )
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->generateExcel('Detalle_Conteo', [
            'Cantidad', 'Código 1', 'SKU', 'Código 3', 'Descripción', 'Conteo', 'Precio Venta', 'Importe',
            'Unidad Medida', 'Lote', 'Fecha Caducidad', 'Núm. Serie', 'Almacén', 'Ubicación',
            'Usuario', 'Latitud', 'Longitud', 'Fecha Captura', 'Hora Captura', 'Factor', 'Forzado'
        ], $query->map(fn ($r) => [
            $r->cantidad, $r->codigo_1, $r->codigo_2, $r->codigo_3,
            $r->producto_descripcion, $r->n_conteo, $r->producto_precio_venta,
            $r->cantidad * $r->producto_precio_venta,
            $r->unidad_medida, $r->lote, $r->fecha_caducidad, $r->n_serie,
            $r->nombre_almacen, $r->ubicacion_1, $r->nombre_usuario,
            $r->latitud, $r->longitud, $r->fecha_captura, $r->hora_captura,
            $r->factor, $r->forzado ? 'Sí' : 'No',
        ])->toArray());
    }

    public function exportDiferencias(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        $subQuery = InventarioDetalle::where('inventario_detalle.eliminado', false)
            ->whereHas('inventario', function ($q) use ($empresaId, $sucursalId, $request) {
                $q->where('empresa_id', $empresaId)->where('eliminado', false);
                if ($sucursalId) $q->where('sucursal_id', $sucursalId);
                if ($request->filled('inventario_id')) $q->where('id', $request->inventario_id);
            })
            ->select('codigo_1', DB::raw('SUM(cantidad) as cantidad_real'))
            ->groupBy('codigo_1');

        $data = Producto::where('productos.empresa_id', $empresaId)
            ->where('productos.eliminado', false)
            ->joinSub($subQuery, 'conteo', fn ($j) => $j->on('productos.codigo_1', '=', 'conteo.codigo_1'))
            ->select('productos.codigo_1', 'productos.codigo_2', 'productos.descripcion', 'productos.precio_venta', 'productos.cantidad_teorica', 'conteo.cantidad_real')
            ->orderBy('productos.codigo_1')
            ->get();

        return $this->generateExcel('Diferencias', [
            'Código 1', 'SKU', 'Descripción', 'Precio Venta', 'Cantidad Teórica', 'Cantidad Real',
            'Diferencia Cantidad', 'Importe Teórico', 'Importe Real', 'Diferencia Importe'
        ], $data->map(fn ($r) => [
            $r->codigo_1, $r->codigo_2, $r->descripcion, $r->precio_venta,
            $r->cantidad_teorica, $r->cantidad_real,
            $r->cantidad_real - $r->cantidad_teorica,
            $r->cantidad_teorica * $r->precio_venta,
            $r->cantidad_real * $r->precio_venta,
            ($r->cantidad_real - $r->cantidad_teorica) * $r->precio_venta,
        ])->toArray());
    }

    public function exportGnc(Request $request)
    {
        $query = $this->baseQuery($request)
            ->select('nombre_almacen', 'codigo_1', DB::raw('SUM(cantidad) as cantidad_total'))
            ->groupBy('nombre_almacen', 'codigo_1')
            ->orderBy('nombre_almacen')
            ->get();

        return $this->generateExcel('Reporte_GNC', ['Tienda', 'UPC', 'Cantidad'], $query->map(fn ($r) => [
            $r->nombre_almacen, $r->codigo_1, $r->cantidad_total,
        ])->toArray());
    }

    // ─── Excel Generator ──────────────────────────────────────────
    private function generateExcel(string $title, array $headers, array $rows)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title, 0, 31));

        $lastCol = chr(64 + count($headers));

        // Headers
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
        }
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Data
        $row = 2;
        foreach ($rows as $data) {
            $col = 'A';
            foreach ($data as $val) {
                $sheet->setCellValue("{$col}{$row}", $val);
                $col++;
            }
            $row++;
        }

        // Auto-size
        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $fileName = $title . '_' . date('Y-m-d_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'inv_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
