<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoRegistro;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SucursalController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = Sucursal::where('eliminado', false)
            ->with(['empresa', 'activoFijoInventarios' => function ($q) {
                $q->where('eliminado', false)
                  ->with('status')
                  ->latest()
                  ->limit(1);
            }]);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%")
                  ->orWhere('ciudad', 'like', "%{$buscar}%")
                  ->orWhereHas('empresa', fn($eq) => $eq->where('nombre', 'like', "%{$buscar}%"));
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $allowedSorts = ['empresa_id', 'codigo', 'nombre', 'ciudad', 'created_at'];
        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int)$request->per_page, [10, 25, 50, 100]) ? (int)$request->per_page : 20;

        $query = $this->buildQuery($request);
        $sucursales = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('sucursales.index', compact('sucursales', 'sort', 'dir', 'perPage'));
    }

    public function create()
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        return view('sucursales.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
        ]);

        Sucursal::create($request->only('empresa_id', 'codigo', 'nombre', 'ciudad', 'direccion'));

        return redirect()->route('sucursales.index')->with('success', 'Sucursal creada exitosamente.');
    }

    public function edit(Sucursal $sucursal)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $empresas = Empresa::where('eliminado', false)->orderBy('nombre')->get();
        return view('sucursales.edit', compact('sucursal', 'empresas'));
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
        ]);

        $sucursal->update($request->only('empresa_id', 'codigo', 'nombre', 'ciudad', 'direccion'));

        return redirect()->route('sucursales.index')->with('success', 'Sucursal actualizada exitosamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $sucursal->update(['eliminado' => true]);

        return redirect()->route('sucursales.index')->with('success', 'Sucursal eliminada exitosamente.');
    }

    public function exportar(Request $request)
    {
        $sucursales = $this->buildQuery($request)->orderBy('created_at', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sucursales');

        $headers = ['Empresa', 'Código Sucursal', 'Nombre de la Sucursal', 'Tipo Levantamiento', 'Local', 'Ciudad', 'Status', 'Fecha Creación'];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = $sheet->getStyle('A1:H1');
        $headerStyle->getFont()->setBold(true)->setSize(10);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('2E7D32');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');

        $row = 2;
        foreach ($sucursales as $s) {
            $latestSession = $s->activoFijoInventarios->first();
            $tipoLev = ($s->empresa->tipo_levantamiento ?? 'activo_fijo') === 'inventario' ? 'Inventario' : 'Activo Fijo';
            $sheet->fromArray([
                $s->empresa->nombre ?? '',
                $s->codigo,
                $s->nombre,
                $tipoLev,
                $latestSession->local ?? '',
                $s->ciudad ?? '',
                $latestSession?->status?->nombre ?? '',
                $s->created_at?->format('Y-m-d H:i:s') ?? '',
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'sucursales_' . now()->format('Ymd_His') . '.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'xls');
        (new Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Delete residual/orphaned images for a sucursal's assets.
     */
    public function eliminarImagenesResiduales(Sucursal $sucursal)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);

        $registros = ActivoFijoRegistro::where('eliminado', false)
            ->whereHas('inventario', fn($q) => $q->where('sucursal_id', $sucursal->id)->where('eliminado', false))
            ->select('imagen1', 'imagen2', 'imagen3')
            ->get();

        $basePath = storage_path('app/public/fotos/activos');
        $deleted = 0;
        $freedBytes = 0;

        foreach ($registros as $r) {
            foreach (['imagen1', 'imagen2', 'imagen3'] as $col) {
                if (!empty($r->$col)) {
                    $fullPath = $basePath . '/' . $r->$col;
                    if (file_exists($fullPath)) {
                        $freedBytes += filesize($fullPath);
                        @unlink($fullPath);
                        $deleted++;
                    }
                }
            }
        }

        // Clear references
        ActivoFijoRegistro::where('eliminado', false)
            ->whereHas('inventario', fn($q) => $q->where('sucursal_id', $sucursal->id)->where('eliminado', false))
            ->update(['imagen1' => null, 'imagen2' => null, 'imagen3' => null]);

        $size = $this->humanFileSize($freedBytes);

        return back()->with('success', "{$deleted} imagen(es) residual(es) eliminada(s). Espacio liberado: {$size}.");
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int)floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
