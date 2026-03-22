<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Acumulado de Activos - SER Inventarios</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; padding: 15px; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 2px solid #778c85; padding-bottom: 8px; }
        .header-info { font-size: 10px; color: #666; text-align: right; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        th { background: #778c85; color: #fff; padding: 4px 5px; text-align: left; font-weight: 600; white-space: nowrap; }
        td { padding: 3px 5px; border-bottom: 1px solid #ddd; vertical-align: top; }
        tr:nth-child(even) { background: #f9f9f9; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 2px; font-size: 8px; font-weight: 700; color: #fff; }
        .badge-found { background: #4CAF50; }
        .badge-added { background: #FF9800; }
        .badge-transferred { background: #ffc107; color: #333; }
        .badge-requested { background: #9C27B0; }
        .total { margin-top: 8px; font-size: 10px; color: #666; }
        @media print {
            body { padding: 0; }
            @page { size: landscape; margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Reporte Acumulado de Activos</h1>
            <span style="font-size:10px; color:#666;">SER Inventarios</span>
        </div>
        <div class="header-info">
            <div>Fecha: {{ now()->format('d/m/Y H:i') }}</div>
            <div>Total: {{ number_format($registros->count()) }} registros</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sucursal</th>
                <th>Área/Depto</th>
                <th>N° Activo</th>
                <th>Descripción</th>
                <th>N° Tag</th>
                <th>Tag Nuevo</th>
                <th>Tag RFID</th>
                <th>N° Serie</th>
                <th>Serie Rev.</th>
                <th>Marca</th>
                <th>Categoría</th>
                <th>Estatus</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registros as $reg)
            @php
                $status = 'ENCONTRADO'; $badgeClass = 'badge-found';
                if ($reg->forzado) { $status = 'AGREGADO'; $badgeClass = 'badge-added'; }
                elseif ($reg->traspasado) { $status = 'TRASPASO'; $badgeClass = 'badge-transferred'; }
                elseif ($reg->solicitado) { $status = 'SOLICITADO'; $badgeClass = 'badge-requested'; }
            @endphp
            <tr>
                <td>{{ $reg->inventario->sucursal->nombre ?? '' }}</td>
                <td>{{ $reg->nombre_almacen ?? '' }}</td>
                <td>{{ $reg->codigo_1 ?? '' }}</td>
                <td>{{ Str::limit($reg->descripcion ?: ($reg->producto->descripcion ?? ''), 40) }}</td>
                <td>{{ $reg->codigo_2 ?? '' }}</td>
                <td>{{ $reg->codigo_3 ?? '' }}</td>
                <td>{{ $reg->tag_rfid ?? '' }}</td>
                <td>{{ $reg->n_serie ?? '' }}</td>
                <td>{{ $reg->n_serie_nuevo ?? '' }}</td>
                <td>{{ $reg->producto->marca ?? '' }}</td>
                <td>{{ $reg->categoria ?: ($reg->producto->categoria_2 ?? '') }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $status }}</span></td>
                <td>{{ Str::limit($reg->observaciones ?? '', 30) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">Total de registros: {{ number_format($registros->count()) }}</div>

    <script>window.onload = function() { window.print(); };</script>
</body>
</html>
