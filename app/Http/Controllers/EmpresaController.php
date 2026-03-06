<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoRegistro;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmpresaController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = Empresa::where('eliminado', false)
            ->withCount('sucursales');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $allowedSorts = ['codigo', 'nombre', 'tipo_levantamiento', 'sucursales_count', 'created_at'];
        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int)$request->per_page, [10, 25, 50, 100]) ? (int)$request->per_page : 20;

        $query = $this->buildQuery($request);
        $empresas = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('empresas.index', compact('empresas', 'sort', 'dir', 'perPage'));
    }

    public function create()
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $request->validate([
            'codigo' => 'required|string|max:50|unique:empresas,codigo',
            'nombre' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('codigo', 'nombre');
        $data['usuario_id'] = Auth::id();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Empresa::create($data);

        return redirect()->route('empresas.index')->with('success', 'Empresa creada exitosamente.');
    }

    public function edit(Empresa $empresa)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $usuarios = User::where('eliminado', false)->orderBy('nombres')->get();
        $asignados = $empresa->users->pluck('id')->toArray();

        return view('empresas.edit', compact('empresa', 'usuarios', 'asignados'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $request->validate([
            'codigo' => 'required|string|max:50|unique:empresas,codigo,' . $empresa->id,
            'nombre' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'usuarios' => 'nullable|array',
        ]);

        $empresa->update($request->only('codigo', 'nombre'));

        if ($request->hasFile('logo')) {
            $empresa->update(['logo' => $request->file('logo')->store('logos', 'public')]);
        }

        if ($request->has('usuarios')) {
            $empresa->users()->sync($request->usuarios);
        }

        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada exitosamente.');
    }

    public function destroy(Empresa $empresa)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $empresa->update(['eliminado' => true]);

        return redirect()->route('empresas.index')->with('success', 'Empresa eliminada exitosamente.');
    }

    public function updateTipoLevantamiento(Request $request, Empresa $empresa)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);
        $request->validate([
            'tipo_levantamiento' => 'required|in:activo_fijo,inventario',
        ]);

        $empresa->update(['tipo_levantamiento' => $request->tipo_levantamiento]);

        return response()->json(['success' => true, 'tipo_levantamiento' => $empresa->tipo_levantamiento]);
    }

    public function exportar(Request $request)
    {
        $empresas = $this->buildQuery($request)->orderBy('created_at', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Empresas');

        $headers = ['Código', 'Nombre', 'Tipo Levantamiento', 'Sucursales', 'Fecha Creación'];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = $sheet->getStyle('A1:E1');
        $headerStyle->getFont()->setBold(true)->setSize(10);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('2E7D32');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');

        $row = 2;
        foreach ($empresas as $e) {
            $tipoLev = ($e->tipo_levantamiento ?? 'activo_fijo') === 'inventario' ? 'Inventario' : 'Activo Fijo';
            $sheet->fromArray([
                $e->codigo,
                $e->nombre,
                $tipoLev,
                $e->sucursales_count,
                $e->created_at?->format('Y-m-d H:i:s') ?? '',
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'empresas_' . now()->format('Ymd_His') . '.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'xls');
        (new Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ─── Image management ───

    /**
     * Get image file paths for an empresa's assets.
     */
    private function getEmpresaImagePaths(Empresa $empresa): array
    {
        $registros = ActivoFijoRegistro::where('eliminado', false)
            ->whereHas('inventario', fn($q) => $q->where('empresa_id', $empresa->id)->where('eliminado', false))
            ->select('imagen1', 'imagen2', 'imagen3')
            ->get();

        $paths = [];
        foreach ($registros as $r) {
            foreach (['imagen1', 'imagen2', 'imagen3'] as $col) {
                if (!empty($r->$col)) {
                    $paths[] = $r->$col;
                }
            }
        }

        return $paths;
    }

    /**
     * 1) Image statistics: count and total size.
     */
    public function imageStats(Empresa $empresa)
    {
        $filenames = $this->getEmpresaImagePaths($empresa);
        $basePath = storage_path('app/public/fotos/activos');

        $count = 0;
        $totalSize = 0;

        foreach ($filenames as $fname) {
            $fullPath = $basePath . '/' . $fname;
            if (file_exists($fullPath)) {
                $count++;
                $totalSize += filesize($fullPath);
            }
        }

        return response()->json([
            'empresa' => $empresa->nombre,
            'total_referencias' => count($filenames),
            'archivos_encontrados' => $count,
            'tamano_total' => $totalSize,
            'tamano_legible' => $this->humanFileSize($totalSize),
        ]);
    }

    /**
     * 2) Compress images: reduce JPEG quality to save storage.
     */
    public function reducirImagenes(Request $request, Empresa $empresa)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);

        $quality = (int)($request->input('quality', 60));
        $quality = max(10, min(95, $quality));

        $filenames = $this->getEmpresaImagePaths($empresa);
        $basePath = storage_path('app/public/fotos/activos');

        $processed = 0;
        $savedBytes = 0;

        foreach ($filenames as $fname) {
            $fullPath = $basePath . '/' . $fname;
            if (!file_exists($fullPath)) continue;

            $originalSize = filesize($fullPath);
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            $image = null;
            if (in_array($ext, ['jpg', 'jpeg'])) {
                $image = @imagecreatefromjpeg($fullPath);
            } elseif ($ext === 'png') {
                $image = @imagecreatefrompng($fullPath);
            }

            if (!$image) continue;

            // Save as JPEG with reduced quality
            imagejpeg($image, $fullPath, $quality);
            imagedestroy($image);

            $newSize = filesize($fullPath);
            if ($newSize < $originalSize) {
                $savedBytes += ($originalSize - $newSize);
            }
            $processed++;
        }

        return response()->json([
            'procesadas' => $processed,
            'espacio_liberado' => $savedBytes,
            'espacio_legible' => $this->humanFileSize($savedBytes),
        ]);
    }

    /**
     * 3) Resize images: reduce dimensions to max width/height.
     */
    public function redimensionarImagenes(Request $request, Empresa $empresa)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);

        $maxDim = (int)($request->input('max_dimension', 1024));
        $maxDim = max(200, min(4096, $maxDim));

        $filenames = $this->getEmpresaImagePaths($empresa);
        $basePath = storage_path('app/public/fotos/activos');

        $processed = 0;
        $savedBytes = 0;

        foreach ($filenames as $fname) {
            $fullPath = $basePath . '/' . $fname;
            if (!file_exists($fullPath)) continue;

            $info = @getimagesize($fullPath);
            if (!$info) continue;

            [$width, $height] = $info;

            // Skip if already within limits
            if ($width <= $maxDim && $height <= $maxDim) continue;

            $originalSize = filesize($fullPath);
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            $image = null;
            if (in_array($ext, ['jpg', 'jpeg'])) {
                $image = @imagecreatefromjpeg($fullPath);
            } elseif ($ext === 'png') {
                $image = @imagecreatefrompng($fullPath);
            }

            if (!$image) continue;

            // Calculate new dimensions
            $ratio = min($maxDim / $width, $maxDim / $height);
            $newW = (int)($width * $ratio);
            $newH = (int)($height * $ratio);

            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);

            imagejpeg($resized, $fullPath, 85);
            imagedestroy($image);
            imagedestroy($resized);

            $newSize = filesize($fullPath);
            if ($newSize < $originalSize) {
                $savedBytes += ($originalSize - $newSize);
            }
            $processed++;
        }

        return response()->json([
            'procesadas' => $processed,
            'espacio_liberado' => $savedBytes,
            'espacio_legible' => $this->humanFileSize($savedBytes),
        ]);
    }

    /**
     * 4) Delete all images for an empresa's assets.
     */
    public function eliminarImagenes(Empresa $empresa)
    {
        abort_unless(Auth::user()->esSupervisor(), 403);

        $filenames = $this->getEmpresaImagePaths($empresa);
        $basePath = storage_path('app/public/fotos/activos');

        $deleted = 0;
        $freedBytes = 0;

        foreach ($filenames as $fname) {
            $fullPath = $basePath . '/' . $fname;
            if (file_exists($fullPath)) {
                $freedBytes += filesize($fullPath);
                @unlink($fullPath);
                $deleted++;
            }
        }

        // Clear image references in the database
        ActivoFijoRegistro::where('eliminado', false)
            ->whereHas('inventario', fn($q) => $q->where('empresa_id', $empresa->id)->where('eliminado', false))
            ->update(['imagen1' => null, 'imagen2' => null, 'imagen3' => null]);

        return response()->json([
            'eliminadas' => $deleted,
            'espacio_liberado' => $freedBytes,
            'espacio_legible' => $this->humanFileSize($freedBytes),
        ]);
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int)floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
