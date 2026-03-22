<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoProducto;
use App\Models\ActivoFijoRegistro;
use App\Models\Empresa;
use App\Models\LogSesionMovil;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteController extends Controller
{
    public function conteo(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        $query = ActivoFijoRegistro::where('activo_fijo_registros.eliminado', false)
            ->with('inventario.empresa', 'inventario.sucursal', 'usuario', 'producto')
            ->whereHas('inventario', function ($q) use ($empresaId, $sucursalId) {
                $q->where('empresa_id', $empresaId);
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
                  ->orWhere('tag_rfid', 'like', "%{$buscar}%")
                  ->orWhere('n_serie', 'like', "%{$buscar}%")
                  ->orWhere('n_serie_nuevo', 'like', "%{$buscar}%")
                  ->orWhere('activo_fijo_registros.descripcion', 'like', "%{$buscar}%")
                  ->orWhere('activo_fijo_registros.categoria', 'like', "%{$buscar}%")
                  ->orWhere('nombre_almacen', 'like', "%{$buscar}%")
                  ->orWhere('observaciones', 'like', "%{$buscar}%");
            });
        }

        // Duplicate filter: show only assets scanned more than once
        if ($request->filled('duplicados') && $request->duplicados === '1') {
            $query->whereIn('codigo_1', function ($sub) use ($empresaId, $sucursalId) {
                $sub->select('codigo_1')
                    ->from('activo_fijo_registros')
                    ->where('eliminado', false)
                    ->whereIn('inventario_id', function ($inv) use ($empresaId, $sucursalId) {
                        $inv->select('id')->from('activo_fijo_inventarios')
                            ->where('empresa_id', $empresaId);
                        if ($sucursalId) $inv->where('sucursal_id', $sucursalId);
                    })
                    ->groupBy('codigo_1')
                    ->havingRaw('COUNT(*) > 1');
            });
        }

        // Sorting
        $sortable = ['codigo_1', 'n_serie', 'n_serie_nuevo', 'codigo_2', 'codigo_3', 'tag_rfid', 'categoria', 'descripcion', 'nombre_almacen', 'ubicacion_1', 'created_at'];
        $sort = in_array($request->sort, $sortable) ? $request->sort : 'created_at';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 50;

        $registros = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        $sesiones = ActivoFijoInventario::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
            ->orderBy('id', 'desc')->get();

        return view('reportes.conteo', compact('registros', 'sesiones', 'sort', 'dir', 'perPage'));
    }

    private function buildNoEncontradosQuery(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        $query = ActivoFijoProducto::where('no_encontrado', true)
            ->where('eliminado', false)
            ->where('empresa_id', $empresaId);

        if ($sucursalId) {
            $query->whereHas('inventario', fn ($q) => $q->where('sucursal_id', $sucursalId));
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo_1', 'like', "%{$buscar}%")
                  ->orWhere('codigo_2', 'like', "%{$buscar}%")
                  ->orWhere('tag_rfid', 'like', "%{$buscar}%")
                  ->orWhere('n_serie', 'like', "%{$buscar}%")
                  ->orWhere('categoria_2', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        return $query;
    }

    public function noEncontrados(Request $request)
    {
        $query = $this->buildNoEncontradosQuery($request);

        $sortable = ['codigo_1', 'codigo_2', 'tag_rfid', 'n_serie', 'categoria_2', 'descripcion', 'created_at'];
        $sort = in_array($request->sort, $sortable) ? $request->sort : 'codigo_1';
        $dir = $request->dir === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 50;

        $productos = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('reportes.no-encontrados', compact('productos', 'sort', 'dir', 'perPage'));
    }

    public function noEncontradosDesmarcar(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No se seleccionaron activos.');
        }

        ActivoFijoProducto::whereIn('id', $ids)->update(['no_encontrado' => false]);

        return back()->with('success', count($ids) . ' activo(s) desmarcado(s) exitosamente.');
    }

    private function buildGlobalQuery(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        $query = ActivoFijoRegistro::where('activo_fijo_registros.eliminado', false)
            ->with('inventario.empresa', 'inventario.sucursal', 'usuario', 'producto')
            ->whereHas('inventario', function ($q) use ($empresaId, $sucursalId) {
                $q->where('empresa_id', $empresaId);
                if ($sucursalId) $q->where('sucursal_id', $sucursalId);
            });

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo_1', 'like', "%{$buscar}%")
                  ->orWhere('codigo_2', 'like', "%{$buscar}%")
                  ->orWhere('codigo_3', 'like', "%{$buscar}%")
                  ->orWhere('tag_rfid', 'like', "%{$buscar}%")
                  ->orWhere('n_serie', 'like', "%{$buscar}%")
                  ->orWhere('n_serie_nuevo', 'like', "%{$buscar}%")
                  ->orWhere('activo_fijo_registros.descripcion', 'like', "%{$buscar}%")
                  ->orWhere('activo_fijo_registros.categoria', 'like', "%{$buscar}%")
                  ->orWhere('nombre_almacen', 'like', "%{$buscar}%")
                  ->orWhere('observaciones', 'like', "%{$buscar}%");
            });
        }

        return $query;
    }

    public function global(Request $request)
    {
        $query = $this->buildGlobalQuery($request);

        $sortable = ['codigo_1', 'n_serie', 'n_serie_nuevo', 'codigo_2', 'codigo_3', 'tag_rfid', 'categoria', 'descripcion', 'nombre_almacen', 'ubicacion_1', 'created_at'];
        $sort = in_array($request->sort, $sortable) ? $request->sort : 'created_at';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 50;

        $registros = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('reportes.global', compact('registros', 'sort', 'dir', 'perPage'));
    }

    public function globalImprimir(Request $request)
    {
        $query = $this->buildGlobalQuery($request);
        $conImagenes = $request->boolean('con_imagenes');
        $registros = $query->orderBy('created_at', 'desc')->get();

        return view('reportes.global-print', compact('registros', 'conImagenes'));
    }

    private function buildAcumuladoQuery(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();

        $query = ActivoFijoRegistro::where('activo_fijo_registros.eliminado', false)
            ->with('inventario.sucursal', 'usuario', 'producto')
            ->whereHas('inventario', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)
                  ->where('eliminado', false)
                  ->whereIn('status_id', [2, 3]); // Only started or finalized
            });

        if ($request->filled('sucursal_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('sucursal_id', $request->sucursal_id));
        }

        if ($request->filled('categoria')) {
            $cat = $request->categoria;
            $query->where(function ($q) use ($cat) {
                $q->where('activo_fijo_registros.categoria', $cat)
                  ->orWhereHas('producto', fn ($p) => $p->where('categoria_2', $cat));
            });
        }

        if ($request->filled('marca')) {
            $query->whereHas('producto', fn ($q) => $q->where('marca', $request->marca));
        }

        if ($request->filled('estatus')) {
            $estatus = $request->estatus;
            $query->where(function ($q) use ($estatus) {
                if ($estatus === 'ENCONTRADO') {
                    $q->where('forzado', false)->where('traspasado', false)->where('solicitado', false);
                } elseif ($estatus === 'AGREGADO') {
                    $q->where('forzado', true);
                } elseif ($estatus === 'TRASPASO') {
                    $q->where('traspasado', true);
                } elseif ($estatus === 'SOLICITADO') {
                    $q->where('solicitado', true);
                }
            });
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo_1', 'like', "%{$buscar}%")
                  ->orWhere('codigo_2', 'like', "%{$buscar}%")
                  ->orWhere('codigo_3', 'like', "%{$buscar}%")
                  ->orWhere('tag_rfid', 'like', "%{$buscar}%")
                  ->orWhere('n_serie', 'like', "%{$buscar}%")
                  ->orWhere('n_serie_nuevo', 'like', "%{$buscar}%")
                  ->orWhere('activo_fijo_registros.descripcion', 'like', "%{$buscar}%")
                  ->orWhere('nombre_almacen', 'like', "%{$buscar}%")
                  ->orWhere('observaciones', 'like', "%{$buscar}%");
            });
        }

        return $query;
    }

    public function acumulado(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();

        $query = $this->buildAcumuladoQuery($request);

        $sortable = ['codigo_1', 'n_serie', 'n_serie_nuevo', 'codigo_2', 'codigo_3', 'tag_rfid', 'categoria', 'descripcion', 'nombre_almacen', 'created_at'];
        $sort = in_array($request->sort, $sortable) ? $request->sort : 'created_at';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 50;

        $registros = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        // Filter dropdown data
        $sucursales = Sucursal::where('empresa_id', $empresaId)
            ->where('eliminado', false)
            ->whereHas('activoFijoInventarios', fn ($q) => $q->where('eliminado', false)->whereIn('status_id', [2, 3]))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        $categorias = ActivoFijoProducto::where('empresa_id', $empresaId)
            ->where('eliminado', false)
            ->where('categoria_2', '!=', '')
            ->distinct()
            ->orderBy('categoria_2')
            ->pluck('categoria_2');

        $marcas = ActivoFijoProducto::where('empresa_id', $empresaId)
            ->where('eliminado', false)
            ->where('marca', '!=', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');

        return view('reportes.acumulado', compact('registros', 'sucursales', 'categorias', 'marcas', 'sort', 'dir', 'perPage'));
    }

    public function acumuladoImprimir(Request $request)
    {
        $query = $this->buildAcumuladoQuery($request);
        $registros = $query->orderBy('created_at', 'desc')->get();

        return view('reportes.acumulado-print', compact('registros'));
    }

    public function sesionesMovil(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        $query = LogSesionMovil::with('inventario.empresa', 'inventario.sucursal', 'usuario')
            ->whereHas('inventario', function ($q) use ($empresaId, $sucursalId) {
                $q->where('empresa_id', $empresaId);
                if ($sucursalId) $q->where('sucursal_id', $sucursalId);
            });

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        if ($request->filled('usuario_id')) {
            $query->whereHas('usuario', fn ($q) => $q->where('id', $request->usuario_id));
        }

        $sesiones = $query->orderBy('fecha_hora_entrada', 'desc')->paginate(20)->withQueryString();
        $inventarios = ActivoFijoInventario::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
            ->with('empresa', 'sucursal')->orderBy('id', 'desc')->get();

        return view('reportes.sesiones-movil', compact('sesiones', 'inventarios'));
    }

    // ── Excel Export Methods ──

    private function styleHeaders($sheet, int $colCount): void
    {
        $lastCol = chr(64 + $colCount);
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '778C85']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function buildConteoQuery(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        $query = ActivoFijoRegistro::where('activo_fijo_registros.eliminado', false)
            ->with('inventario.empresa', 'inventario.sucursal', 'usuario', 'producto')
            ->whereHas('inventario', function ($q) use ($empresaId, $sucursalId) {
                $q->where('empresa_id', $empresaId);
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
                  ->orWhere('activo_fijo_registros.descripcion', 'like', "%{$buscar}%")
                  ->orWhere('n_serie', 'like', "%{$buscar}%")
                  ->orWhere('activo_fijo_registros.categoria', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('duplicados') && $request->duplicados === '1') {
            $query->whereIn('codigo_1', function ($sub) use ($empresaId, $sucursalId) {
                $sub->select('codigo_1')
                    ->from('activo_fijo_registros')
                    ->where('eliminado', false)
                    ->whereIn('inventario_id', function ($inv) use ($empresaId, $sucursalId) {
                        $inv->select('id')->from('activo_fijo_inventarios')
                            ->where('empresa_id', $empresaId);
                        if ($sucursalId) $inv->where('sucursal_id', $sucursalId);
                    })
                    ->groupBy('codigo_1')
                    ->havingRaw('COUNT(*) > 1');
            });
        }

        return $query;
    }

    private function getRegStatus($reg): string
    {
        if ($reg->forzado) return 'AGREGADO';
        if ($reg->traspasado) return 'TRASPASADO';
        if ($reg->solicitado) return 'SOLICITADO';
        return 'ENCONTRADO';
    }

    public function exportConteo(Request $request)
    {
        $conImagenes = $request->boolean('con_imagenes');
        $query = $this->buildConteoQuery($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Conteo');

        $headers = [
            'Número de Activo', 'Número de Serie', 'Serie Revisado',
            'Número de Tag', 'Tag Nuevo', 'Tag RFID',
            'Categoría', 'Descripción de Activo', 'Marca', 'Unidades',
            'Departamento/Área', 'Estatus', 'Comentarios',
            'Usuario', 'Fecha Hora', 'Ubicación',
        ];
        if ($conImagenes) $headers[] = 'Imágenes';

        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, count($headers));

        $row = 2;
        $query->orderBy('created_at', 'desc')->chunk(500, function ($registros) use ($sheet, &$row, $conImagenes) {
            foreach ($registros as $reg) {
                $data = [
                    $reg->codigo_1 ?? '',
                    $reg->n_serie ?? '',
                    $reg->n_serie_nuevo ?? '',
                    $reg->codigo_2 ?? '',
                    $reg->codigo_3 ?? '',
                    $reg->tag_rfid ?? '',
                    $reg->categoria ?? ($reg->producto->categoria_2 ?? ''),
                    $reg->descripcion ?? ($reg->producto->descripcion ?? ''),
                    $reg->producto->marca ?? '',
                    $reg->producto->cantidad_teorica ?? 1,
                    $reg->nombre_almacen ?? '',
                    $this->getRegStatus($reg),
                    $reg->observaciones ?? '',
                    $reg->usuario->nombres ?? '',
                    $reg->created_at?->format('d/m/Y H:i:s'),
                    $reg->ubicacion_1 ?? '',
                ];
                $sheet->fromArray($data, null, "A{$row}");

                if ($conImagenes && $reg->imagen1) {
                    $imgPath = storage_path("app/public/fotos/activos/{$reg->imagen1}");
                    if (file_exists($imgPath)) {
                        try {
                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                            $drawing->setPath($imgPath);
                            $drawing->setHeight(60);
                            $drawing->setCoordinates(chr(64 + count($data) + 1) . $row);
                            $drawing->setWorksheet($sheet);
                            $sheet->getRowDimension($row)->setRowHeight(50);
                        } catch (\Exception $e) {
                            // Skip images that fail to load
                        }
                    }
                }

                $row++;
            }
        });

        $suffix = $conImagenes ? '_con_imagenes' : '_sin_imagenes';
        return $this->downloadSpreadsheet($spreadsheet, 'reporte_conteo' . $suffix);
    }

    public function conteoImprimir(Request $request)
    {
        $query = $this->buildConteoQuery($request);
        $conImagenes = $request->boolean('con_imagenes');
        $registros = $query->orderBy('created_at', 'desc')->get();

        return view('reportes.conteo-print', compact('registros', 'conImagenes'));
    }

    public function conteoEliminar(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No se seleccionaron registros.');
        }

        ActivoFijoRegistro::whereIn('id', $ids)->update(['eliminado' => true]);

        return back()->with('success', count($ids) . ' registro(s) eliminado(s) exitosamente.');
    }

    public function conteoEditar(Request $request, ActivoFijoRegistro $registro)
    {
        if ($request->isMethod('get')) {
            return response()->json($registro);
        }

        $registro->update($request->only([
            'codigo_1', 'codigo_2', 'codigo_3', 'tag_rfid',
            'n_serie', 'n_serie_nuevo', 'categoria', 'descripcion',
            'nombre_almacen', 'ubicacion_1', 'observaciones',
        ]));

        return back()->with('success', 'Registro actualizado exitosamente.');
    }

    public function exportNoEncontrados(Request $request)
    {
        $query = $this->buildNoEncontradosQuery($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('No Encontrados');
        $headers = ['Número de Activo', 'Número de Tag', 'Tag RFID', 'Número de Serie', 'Categoría', 'Descripción', 'Usuario', 'Fecha Hora', 'Ubicación'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, count($headers));

        $row = 2;
        $query->orderBy('codigo_1')->chunk(1000, function ($productos) use ($sheet, &$row) {
            foreach ($productos as $prod) {
                $sheet->fromArray([
                    $prod->codigo_1 ?? '',
                    $prod->codigo_2 ?? '',
                    $prod->tag_rfid ?? '',
                    $prod->n_serie ?? '',
                    $prod->categoria_2 ?? '',
                    $prod->descripcion ?? '',
                    '',
                    $prod->created_at?->format('d/m/Y H:i'),
                    '',
                ], null, "A{$row}");
                $row++;
            }
        });

        return $this->downloadSpreadsheet($spreadsheet, 'reporte_no_encontrados');
    }

    public function exportGlobal(Request $request)
    {
        $conImagenes = $request->boolean('con_imagenes');
        $query = $this->buildGlobalQuery($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Global');

        $headers = [
            'Número de Activo', 'Número de Serie', 'Serie Revisado',
            'Número de Tag', 'Tag Nuevo', 'Tag RFID',
            'Categoría', 'Descripción de Activo', 'Marca', 'Unidades',
            'Departamento/Área', 'Estatus', 'Comentarios',
            'Usuario', 'Fecha Hora', 'Ubicación',
        ];
        if ($conImagenes) $headers[] = 'Imágenes';

        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, count($headers));

        $row = 2;
        $query->orderBy('created_at', 'desc')->chunk(500, function ($registros) use ($sheet, &$row, $conImagenes) {
            foreach ($registros as $reg) {
                $data = [
                    $reg->codigo_1 ?? '',
                    $reg->n_serie ?? '',
                    $reg->n_serie_nuevo ?? '',
                    $reg->codigo_2 ?? '',
                    $reg->codigo_3 ?? '',
                    $reg->tag_rfid ?? '',
                    $reg->categoria ?? ($reg->producto->categoria_2 ?? ''),
                    $reg->descripcion ?? ($reg->producto->descripcion ?? ''),
                    $reg->producto->marca ?? '',
                    $reg->producto->cantidad_teorica ?? 1,
                    $reg->nombre_almacen ?? '',
                    $this->getRegStatus($reg),
                    $reg->observaciones ?? '',
                    $reg->usuario->nombres ?? '',
                    $reg->created_at?->format('d/m/Y H:i:s'),
                    $reg->ubicacion_1 ?? '',
                ];
                $sheet->fromArray($data, null, "A{$row}");

                if ($conImagenes && $reg->imagen1) {
                    $imgPath = storage_path("app/public/fotos/activos/{$reg->imagen1}");
                    if (file_exists($imgPath)) {
                        try {
                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                            $drawing->setPath($imgPath);
                            $drawing->setHeight(60);
                            $drawing->setCoordinates(chr(64 + count($data) + 1) . $row);
                            $drawing->setWorksheet($sheet);
                            $sheet->getRowDimension($row)->setRowHeight(50);
                        } catch (\Exception $e) {
                            // Skip images that fail to load
                        }
                    }
                }

                $row++;
            }
        });

        $suffix = $conImagenes ? '_con_imagenes' : '_sin_imagenes';
        return $this->downloadSpreadsheet($spreadsheet, 'reporte_global' . $suffix);
    }

    public function exportAcumulado(Request $request)
    {
        $query = $this->buildAcumuladoQuery($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Acumulado');
        $headers = [
            'Sucursal', 'Área/Departamento', 'Número de Activo', 'Descripción',
            'Número de Tag', 'Número de Tag Nuevo', 'Tag RFID',
            'Número de Serie', 'Número de Serie Revisado',
            'Marca', 'Categoría', 'Estatus', 'Observaciones',
        ];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, count($headers));

        $row = 2;
        $query->orderBy('created_at', 'desc')->chunk(500, function ($registros) use ($sheet, &$row) {
            foreach ($registros as $reg) {
                $sheet->fromArray([
                    $reg->inventario->sucursal->nombre ?? '',
                    $reg->nombre_almacen ?? '',
                    $reg->codigo_1 ?? '',
                    $reg->descripcion ?? ($reg->producto->descripcion ?? ''),
                    $reg->codigo_2 ?? '',
                    $reg->codigo_3 ?? '',
                    $reg->tag_rfid ?? '',
                    $reg->n_serie ?? '',
                    $reg->n_serie_nuevo ?? '',
                    $reg->producto->marca ?? '',
                    $reg->categoria ?: ($reg->producto->categoria_2 ?? ''),
                    $this->getRegStatus($reg),
                    $reg->observaciones ?? '',
                ], null, "A{$row}");
                $row++;
            }
        });

        return $this->downloadSpreadsheet($spreadsheet, 'reporte_acumulado');
    }

    public function exportSesionesMovil(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $this->selectedSucursalId();

        $query = LogSesionMovil::with('inventario.empresa', 'inventario.sucursal', 'usuario')
            ->whereHas('inventario', function ($q) use ($empresaId, $sucursalId) {
                $q->where('empresa_id', $empresaId);
                if ($sucursalId) $q->where('sucursal_id', $sucursalId);
            });

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }
        if ($request->filled('usuario_id')) {
            $query->whereHas('usuario', fn ($q) => $q->where('id', $request->usuario_id));
        }

        $sesiones = $query->orderBy('fecha_hora_entrada', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Sesiones Móvil');
        $headers = ['ID', 'Sesión', 'Usuario', 'Dispositivo', 'Serie', 'Entrada', 'Salida', 'Estado'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, count($headers));

        $row = 2;
        foreach ($sesiones as $ses) {
            $sheet->fromArray([
                $ses->id,
                $ses->inventario_id,
                $ses->usuario->nombres ?? '',
                $ses->plataforma_dispositivo,
                $ses->serie_dispositivo,
                $ses->fecha_hora_entrada ? date('d/m/Y H:i', strtotime($ses->fecha_hora_entrada)) : '',
                $ses->fecha_hora_salida ? date('d/m/Y H:i', strtotime($ses->fecha_hora_salida)) : '',
                $ses->sesion_activa ? 'Activa' : 'Cerrada',
            ], null, "A{$row}");
            $row++;
        }

        return $this->downloadSpreadsheet($spreadsheet, 'reporte_sesiones_movil');
    }

    private function downloadSpreadsheet(Spreadsheet $spreadsheet, string $prefix)
    {
        $filename = $prefix . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
