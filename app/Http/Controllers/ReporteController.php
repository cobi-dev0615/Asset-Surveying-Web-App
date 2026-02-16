<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoInventario;
use App\Models\ActivoFijoRegistro;
use App\Models\ActivoNoEncontrado;
use App\Models\Empresa;
use App\Models\LogSesionMovil;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteController extends Controller
{
    private function empresaIds()
    {
        $user = Auth::user();
        return $user->esAdmin() ? null : $user->empresas->pluck('id');
    }

    private function scopedEmpresas($empresaIds)
    {
        return Empresa::where('eliminado', false)
            ->when($empresaIds, fn($q) => $q->whereIn('id', $empresaIds))
            ->orderBy('nombre')->get();
    }

    public function conteo(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = ActivoFijoRegistro::where('eliminado', false)
            ->with('inventario.empresa', 'inventario.sucursal', 'usuario');

        if ($empresaIds !== null) {
            $query->whereHas('inventario', fn ($q) => $q->whereIn('empresa_id', $empresaIds));
        }

        if ($request->filled('empresa_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('empresa_id', $request->empresa_id));
        }

        if ($request->filled('sucursal_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('sucursal_id', $request->sucursal_id));
        }

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        $registros = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $empresas = $this->scopedEmpresas($empresaIds);
        $sesiones = ActivoFijoInventario::where('eliminado', false)
            ->when($empresaIds, fn($q) => $q->whereIn('empresa_id', $empresaIds))
            ->orderBy('id', 'desc')->get();

        return view('reportes.conteo', compact('registros', 'empresas', 'sesiones'));
    }

    public function noEncontrados(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = ActivoNoEncontrado::with('inventario.empresa', 'inventario.sucursal', 'usuario');

        if ($empresaIds !== null) {
            $query->whereHas('inventario', fn ($q) => $q->whereIn('empresa_id', $empresaIds));
        }

        if ($request->filled('empresa_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('empresa_id', $request->empresa_id));
        }

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        $registros = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $empresas = $this->scopedEmpresas($empresaIds);
        $sesiones = ActivoFijoInventario::where('eliminado', false)
            ->when($empresaIds, fn($q) => $q->whereIn('empresa_id', $empresaIds))
            ->orderBy('id', 'desc')->get();

        return view('reportes.no-encontrados', compact('registros', 'empresas', 'sesiones'));
    }

    public function global(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = ActivoFijoInventario::where('eliminado', false)
            ->with('empresa', 'sucursal', 'status', 'usuario')
            ->withCount('registros', 'noEncontrados');

        if ($empresaIds !== null) {
            $query->whereIn('empresa_id', $empresaIds);
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        $sesiones = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $empresas = $this->scopedEmpresas($empresaIds);

        // Summary stats (scoped)
        $statsQuery = ActivoFijoInventario::where('eliminado', false);
        if ($empresaIds !== null) {
            $statsQuery->whereIn('empresa_id', $empresaIds);
        }
        $totalSesiones = (clone $statsQuery)->count();
        $finalizadas = (clone $statsQuery)->where('finalizado', true)->count();

        $regQuery = ActivoFijoRegistro::where('eliminado', false);
        if ($empresaIds !== null) {
            $regQuery->whereHas('inventario', fn ($q) => $q->whereIn('empresa_id', $empresaIds));
        }
        $totalRegistros = $regQuery->count();

        $noEncQuery = ActivoNoEncontrado::query();
        if ($empresaIds !== null) {
            $noEncQuery->whereHas('inventario', fn ($q) => $q->whereIn('empresa_id', $empresaIds));
        }
        $totalNoEncontrados = $noEncQuery->count();

        return view('reportes.global', compact(
            'sesiones', 'empresas', 'totalSesiones', 'totalRegistros', 'totalNoEncontrados', 'finalizadas'
        ));
    }

    public function acumulado(Request $request)
    {
        $empresaIds = $this->empresaIds();
        $empresas = $this->scopedEmpresas($empresaIds);

        $query = Empresa::where('eliminado', false)
            ->withCount([
                'activoFijoInventarios as sesiones_count' => fn ($q) => $q->where('eliminado', false),
                'activoFijoInventarios as finalizadas_count' => fn ($q) => $q->where('eliminado', false)->where('finalizado', true),
            ]);

        if ($empresaIds !== null) {
            $query->whereIn('id', $empresaIds);
        }

        if ($request->filled('empresa_id')) {
            $query->where('id', $request->empresa_id);
        }

        $resumen = $query->orderBy('nombre')->get();

        // Per-empresa asset count
        foreach ($resumen as $empresa) {
            $empresa->total_registros = ActivoFijoRegistro::where('eliminado', false)
                ->whereHas('inventario', fn ($q) => $q->where('empresa_id', $empresa->id)->where('eliminado', false))
                ->count();
            $empresa->total_no_encontrados = ActivoNoEncontrado::whereHas('inventario', fn ($q) => $q->where('empresa_id', $empresa->id)->where('eliminado', false))
                ->count();
        }

        return view('reportes.acumulado', compact('resumen', 'empresas'));
    }

    public function sesionesMovil(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = LogSesionMovil::with('inventario.empresa', 'inventario.sucursal', 'usuario');

        if ($empresaIds !== null) {
            $query->whereHas('inventario', fn ($q) => $q->whereIn('empresa_id', $empresaIds));
        }

        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        if ($request->filled('usuario_id')) {
            $query->whereHas('usuario', fn ($q) => $q->where('id', $request->usuario_id));
        }

        $sesiones = $query->orderBy('fecha_hora_entrada', 'desc')->paginate(20)->withQueryString();
        $inventarios = ActivoFijoInventario::where('eliminado', false)
            ->when($empresaIds, fn($q) => $q->whereIn('empresa_id', $empresaIds))
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

    public function exportConteo(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = ActivoFijoRegistro::where('eliminado', false)
            ->with('inventario.empresa', 'inventario.sucursal', 'usuario');

        if ($empresaIds !== null) {
            $query->whereHas('inventario', fn ($q) => $q->whereIn('empresa_id', $empresaIds));
        }
        if ($request->filled('empresa_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('empresa_id', $request->empresa_id));
        }
        if ($request->filled('sucursal_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('sucursal_id', $request->sucursal_id));
        }
        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Conteo');
        $headers = ['ID', 'Sesión', 'Empresa', 'Sucursal', 'Código', 'Descripción', 'Categoría', 'Ubicación', 'Usuario', 'Fecha'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, count($headers));

        $row = 2;
        $query->orderBy('created_at', 'desc')->chunk(1000, function ($registros) use ($sheet, &$row) {
            foreach ($registros as $reg) {
                $sheet->fromArray([
                    $reg->id,
                    $reg->inventario_id,
                    $reg->inventario->empresa->nombre ?? '',
                    $reg->inventario->sucursal->nombre ?? '',
                    $reg->codigo_1,
                    $reg->descripcion,
                    $reg->categoria,
                    $reg->ubicacion_1,
                    $reg->usuario->nombres ?? '',
                    $reg->created_at?->format('d/m/Y H:i'),
                ], null, "A{$row}");
                $row++;
            }
        });

        return $this->downloadSpreadsheet($spreadsheet, 'reporte_conteo');
    }

    public function exportNoEncontrados(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = ActivoNoEncontrado::with('inventario.empresa', 'inventario.sucursal', 'usuario');

        if ($empresaIds !== null) {
            $query->whereHas('inventario', fn ($q) => $q->whereIn('empresa_id', $empresaIds));
        }
        if ($request->filled('empresa_id')) {
            $query->whereHas('inventario', fn ($q) => $q->where('empresa_id', $request->empresa_id));
        }
        if ($request->filled('inventario_id')) {
            $query->where('inventario_id', $request->inventario_id);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('No Encontrados');
        $headers = ['ID', 'Sesión', 'Empresa', 'Sucursal', 'ID Activo', 'Usuario', 'Latitud', 'Longitud', 'Fecha'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, count($headers));

        $row = 2;
        $query->orderBy('created_at', 'desc')->chunk(1000, function ($registros) use ($sheet, &$row) {
            foreach ($registros as $reg) {
                $sheet->fromArray([
                    $reg->id,
                    $reg->inventario_id,
                    $reg->inventario->empresa->nombre ?? '',
                    $reg->inventario->sucursal->nombre ?? '',
                    $reg->activo,
                    $reg->usuario->nombres ?? '',
                    $reg->latitud,
                    $reg->longitud,
                    $reg->created_at?->format('d/m/Y H:i'),
                ], null, "A{$row}");
                $row++;
            }
        });

        return $this->downloadSpreadsheet($spreadsheet, 'reporte_no_encontrados');
    }

    public function exportGlobal(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = ActivoFijoInventario::where('eliminado', false)
            ->with('empresa', 'sucursal', 'status', 'usuario')
            ->withCount('registros', 'noEncontrados');

        if ($empresaIds !== null) {
            $query->whereIn('empresa_id', $empresaIds);
        }
        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        $sesiones = $query->orderBy('created_at', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Global');
        $headers = ['ID', 'Empresa', 'Sucursal', 'Creador', 'Registros', 'No Encontrados', 'Estado', 'Fecha'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, count($headers));

        $row = 2;
        foreach ($sesiones as $sesion) {
            $sheet->fromArray([
                $sesion->id,
                $sesion->empresa->nombre ?? '',
                $sesion->sucursal->nombre ?? '',
                $sesion->usuario->nombres ?? '',
                $sesion->registros_count,
                $sesion->no_encontrados_count,
                $sesion->status->nombre ?? '',
                $sesion->created_at?->format('d/m/Y H:i'),
            ], null, "A{$row}");
            $row++;
        }

        return $this->downloadSpreadsheet($spreadsheet, 'reporte_global');
    }

    public function exportAcumulado(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = Empresa::where('eliminado', false)
            ->withCount([
                'activoFijoInventarios as sesiones_count' => fn ($q) => $q->where('eliminado', false),
                'activoFijoInventarios as finalizadas_count' => fn ($q) => $q->where('eliminado', false)->where('finalizado', true),
            ]);

        if ($empresaIds !== null) {
            $query->whereIn('id', $empresaIds);
        }
        if ($request->filled('empresa_id')) {
            $query->where('id', $request->empresa_id);
        }

        $resumen = $query->orderBy('nombre')->get();

        foreach ($resumen as $empresa) {
            $empresa->total_registros = ActivoFijoRegistro::where('eliminado', false)
                ->whereHas('inventario', fn ($q) => $q->where('empresa_id', $empresa->id)->where('eliminado', false))
                ->count();
            $empresa->total_no_encontrados = ActivoNoEncontrado::whereHas('inventario', fn ($q) => $q->where('empresa_id', $empresa->id)->where('eliminado', false))
                ->count();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Acumulado');
        $headers = ['Empresa', 'Total Sesiones', 'Finalizadas', 'Progreso %', 'Total Registros', 'No Encontrados'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, count($headers));

        $row = 2;
        foreach ($resumen as $empresa) {
            $progreso = $empresa->sesiones_count > 0
                ? round(($empresa->finalizadas_count / $empresa->sesiones_count) * 100, 1)
                : 0;
            $sheet->fromArray([
                $empresa->nombre,
                $empresa->sesiones_count,
                $empresa->finalizadas_count,
                $progreso . '%',
                $empresa->total_registros,
                $empresa->total_no_encontrados,
            ], null, "A{$row}");
            $row++;
        }

        return $this->downloadSpreadsheet($spreadsheet, 'reporte_acumulado');
    }

    public function exportSesionesMovil(Request $request)
    {
        $empresaIds = $this->empresaIds();

        $query = LogSesionMovil::with('inventario.empresa', 'inventario.sucursal', 'usuario');

        if ($empresaIds !== null) {
            $query->whereHas('inventario', fn ($q) => $q->whereIn('empresa_id', $empresaIds));
        }
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
