<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijoRegistro;
use App\Models\ActivoTraspasado;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TransferenciaController extends Controller
{
    public function nueva()
    {
        $empresaId = $this->selectedEmpresaId();

        $sucursales = Sucursal::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')->get(['id', 'nombre', 'codigo']);

        return view('transferencias.nueva', compact('sucursales'));
    }

    /**
     * AJAX: Return audited assets for a given source sucursal.
     */
    public function activosPorSucursal(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();
        $sucursalId = $request->input('sucursal_id');

        if (!$sucursalId) {
            return response()->json([]);
        }

        $registros = ActivoFijoRegistro::where('eliminado', false)
            ->where('traspasado', false)
            ->with('producto')
            ->whereHas('inventario', function ($q) use ($empresaId, $sucursalId) {
                $q->where('empresa_id', $empresaId)
                  ->where('sucursal_id', $sucursalId)
                  ->where('eliminado', false);
            });

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $registros->where(function ($q) use ($buscar) {
                $q->where('codigo_1', 'like', "%{$buscar}%")
                  ->orWhere('activo_fijo_registros.descripcion', 'like', "%{$buscar}%")
                  ->orWhere('n_serie', 'like', "%{$buscar}%")
                  ->orWhere('codigo_2', 'like', "%{$buscar}%");
            });
        }

        $results = $registros->orderBy('codigo_1')->limit(50)->get();

        return response()->json($results->map(fn ($r) => [
            'id' => $r->id,
            'codigo_1' => $r->codigo_1 ?? '',
            'descripcion' => $r->descripcion ?: ($r->producto->descripcion ?? ''),
            'codigo_2' => $r->codigo_2 ?? '',
            'codigo_3' => $r->codigo_3 ?? '',
            'n_serie' => $r->n_serie ?? '',
            'n_serie_nuevo' => $r->n_serie_nuevo ?? '',
            'tag_rfid' => $r->tag_rfid ?? '',
            'categoria' => $r->categoria ?: ($r->producto->categoria_2 ?? ''),
            'marca' => $r->producto->marca ?? '',
            'imagen1' => $r->imagen1,
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sucursal_origen_id' => 'required|exists:sucursales,id',
            'activos' => 'required|array|min:1',
            'activos.*' => 'integer',
        ]);

        $empresaId = $this->selectedEmpresaId();
        $sucursalDestinoId = $this->selectedSucursalId();

        if (!$sucursalDestinoId) {
            $sucursalDestinoId = Sucursal::where('empresa_id', $empresaId)
                ->where('eliminado', false)
                ->value('id');
        }

        $motivo = $request->input('motivo', '');
        $comentarios = $request->input('comentarios', '');

        // Get next n_orden for this empresa's transfers
        $lastOrden = ActivoTraspasado::whereHas('sucursalOrigen', fn($q) => $q->where('empresa_id', $empresaId))
            ->max('n_orden');
        $nextOrden = ($lastOrden ?? 0) + 1;

        foreach ($request->activos as $index => $activoId) {
            ActivoTraspasado::create([
                'activo' => $activoId,
                'n_orden' => $nextOrden + $index,
                'sucursal_origen_id' => $request->sucursal_origen_id,
                'sucursal_destino_id' => $sucursalDestinoId,
                'usuario_id' => Auth::id(),
                'motivo' => $motivo,
                'comentarios' => $comentarios,
                'estatus' => 'Pendiente',
            ]);

            ActivoFijoRegistro::where('id', $activoId)
                ->update(['traspasado' => true, 'solicitado' => true]);
        }

        $count = count($request->activos);
        return redirect()->route('transferencias.solicitadas')
            ->with('success', "{$count} activo(s) solicitado(s) para transferencia exitosamente.");
    }

    // ─── Workflow actions ───

    public function autorizar(ActivoTraspasado $traspaso)
    {
        if ($traspaso->estatus !== 'Pendiente') {
            return back()->with('error', 'Solo se pueden autorizar transferencias con estatus Pendiente.');
        }

        $traspaso->update([
            'estatus' => 'Autorizada',
            'autorizado_por' => Auth::id(),
        ]);

        return back()->with('success', 'Transferencia autorizada exitosamente.');
    }

    public function surtir(ActivoTraspasado $traspaso)
    {
        if (!in_array($traspaso->estatus, ['Pendiente', 'Autorizada'])) {
            return back()->with('error', 'Solo se pueden surtir transferencias Pendientes o Autorizadas.');
        }

        $traspaso->update([
            'estatus' => 'Surtida',
            'surtido_por' => Auth::id(),
            'fecha_hora_surtido' => now(),
        ]);

        return back()->with('success', 'Transferencia surtida exitosamente.');
    }

    public function cancelar(ActivoTraspasado $traspaso)
    {
        if (in_array($traspaso->estatus, ['Surtida', 'Cancelada'])) {
            return back()->with('error', 'No se puede cancelar una transferencia ya surtida o cancelada.');
        }

        $traspaso->update([
            'estatus' => 'Cancelada',
            'cancelado_por' => Auth::id(),
            'fecha_hora_cancelacion' => now(),
        ]);

        // Unmark the asset record
        ActivoFijoRegistro::where('id', $traspaso->activo)
            ->update(['traspasado' => false, 'solicitado' => false]);

        return back()->with('success', 'Transferencia cancelada.');
    }

    // ─── Solicitadas list ───

    private function buildSolicitadasQuery(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();

        $query = ActivoTraspasado::where('eliminado', false)
            ->with('sucursalOrigen', 'sucursalDestino', 'usuario', 'autorizador', 'surtidor', 'cancelador')
            ->where(function ($q) use ($empresaId) {
                $q->whereHas('sucursalOrigen', fn($sq) => $sq->where('empresa_id', $empresaId))
                  ->orWhereHas('sucursalDestino', fn($sq) => $sq->where('empresa_id', $empresaId));
            });

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('id', $buscar)
                  ->orWhere('n_orden', $buscar)
                  ->orWhere('motivo', 'like', "%{$buscar}%")
                  ->orWhere('comentarios', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_origen_id', $request->sucursal_id);
        }

        return $query;
    }

    public function solicitadas(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();

        $allowedSorts = ['id', 'n_orden', 'estatus', 'motivo', 'created_at', 'fecha_hora_surtido', 'fecha_hora_cancelacion'];
        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int)$request->per_page, [10, 25, 50, 100]) ? (int)$request->per_page : 20;

        $query = $this->buildSolicitadasQuery($request);
        $traspasos = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        $sucursales = Sucursal::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')->get();

        return view('transferencias.solicitadas', compact('traspasos', 'sucursales', 'sort', 'dir', 'perPage'));
    }

    public function exportSolicitadas(Request $request)
    {
        $query = $this->buildSolicitadasQuery($request);
        $registros = $query->orderBy('created_at', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transferencias Solicitadas');

        $headers = ['ID', 'N° Orden', 'Estatus', 'Motivo', 'Sucursal Solicitada', 'Solicitado por',
                     'Autorizado por', 'Surtido por', 'Cancelado por', 'Fecha Solicitud',
                     'Fecha Surtido', 'Fecha Cancelación', 'Comentarios'];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = $sheet->getStyle('A1:M1');
        $headerStyle->getFont()->setBold(true)->setSize(10);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('2E7D32');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');

        $row = 2;
        foreach ($registros as $r) {
            $sucNombre = '';
            if ($r->sucursalOrigen) {
                $sucNombre = ($r->sucursalOrigen->codigo ? $r->sucursalOrigen->codigo . '-' : '') . $r->sucursalOrigen->nombre;
            }

            $sheet->fromArray([
                $r->id,
                $r->n_orden,
                $r->estatus,
                $r->motivo ?? '',
                $sucNombre,
                $r->usuario->nombres ?? '',
                $r->autorizador->nombres ?? '',
                $r->surtidor->nombres ?? '',
                $r->cancelador->nombres ?? '',
                $r->created_at?->format('Y-m-d H:i:s') ?? '',
                $r->fecha_hora_surtido?->format('Y-m-d H:i:s') ?? '',
                $r->fecha_hora_cancelacion?->format('Y-m-d H:i:s') ?? '',
                $r->comentarios ?? '',
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'transferencias_solicitadas_' . now()->format('Ymd_His') . '.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'xls');
        (new Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ─── Recibidas list ───

    public function recibidas(Request $request)
    {
        $empresaId = $this->selectedEmpresaId();

        $allowedSorts = ['id', 'n_orden', 'estatus', 'motivo', 'created_at', 'fecha_hora_surtido', 'fecha_hora_cancelacion'];
        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int)$request->per_page, [10, 25, 50, 100]) ? (int)$request->per_page : 20;

        $query = ActivoTraspasado::where('eliminado', false)
            ->with('sucursalOrigen', 'sucursalDestino', 'usuario', 'autorizador', 'surtidor', 'cancelador')
            ->where(function ($q) use ($empresaId) {
                $q->whereHas('sucursalOrigen', fn($sq) => $sq->where('empresa_id', $empresaId))
                  ->orWhereHas('sucursalDestino', fn($sq) => $sq->where('empresa_id', $empresaId));
            });

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('id', $buscar)
                  ->orWhere('n_orden', $buscar)
                  ->orWhere('motivo', 'like', "%{$buscar}%")
                  ->orWhere('comentarios', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_destino_id', $request->sucursal_id);
        }

        $traspasos = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();
        $sucursales = Sucursal::where('eliminado', false)
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')->get();

        return view('transferencias.recibidas', compact('traspasos', 'sucursales', 'sort', 'dir', 'perPage'));
    }
}
