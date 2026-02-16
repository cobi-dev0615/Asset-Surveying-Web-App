<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportProductionData extends Command
{
    protected $signature = 'app:import-production';
    protected $description = 'Import production data from seretail_ser_activo_fijo temp database';

    private string $src = 'source';

    private array $duplicateUsers = [
        'mgarcia' => [1, 26],
        'Paniagua' => [20, 21],
        'Hector' => [10, 40],
        'Kenia' => [28, 31],
        'invitado' => [8, 32],
        'Gio' => [27, 55],
    ];

    public function handle(): int
    {
        $this->info('=== SER Inventarios - Production Data Import ===');
        $this->newLine();

        // Verify source DB is accessible
        try {
            $count = DB::connection($this->src)->table('empresas')->count();
            $this->info("Source database connected. Found {$count} empresas.");
        } catch (\Exception $e) {
            $this->error('Cannot connect to source database (seretail_ser_activo_fijo).');
            $this->error('Run the SQL import first. See plan for commands.');
            return 1;
        }

        // Disable FK checks for entire import (cross-table dependencies)
        Schema::disableForeignKeyConstraints();

        $this->clearData();
        $this->seedRoles();
        $this->importEmpresas();
        $sucursalMap = $this->extractSucursales();
        $this->importUsers();
        $this->importEmpresaUser();
        $this->importInventarios($sucursalMap);
        $this->importProductos();
        $this->importRegistros();
        $this->importLogSesiones();
        $this->importOrdenesEntrada();
        $this->importActivosNoEncontrados();

        Schema::enableForeignKeyConstraints();
        $this->verifyCounts();

        $this->newLine();
        $this->info('Import completed successfully!');
        return 0;
    }

    private function clearData(): void
    {
        $this->warn('Clearing existing data...');

        $tables = [
            'activos_no_encontrados', 'ordenes_entrada_detalle', 'ordenes_entrada',
            'log_sesiones_movil', 'activo_fijo_registros', 'activo_fijo_productos',
            'activo_fijo_inventarios', 'empresa_user', 'users', 'sucursales',
            'empresas', 'roles',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        $this->info('  Data cleared.');
    }

    private function seedRoles(): void
    {
        $this->info('Seeding roles (production ordering)...');
        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'Super Administrador del sistema', 'slug' => 'super_admin', 'descripcion' => 'Acceso total al sistema', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Supervisor de inventarios', 'slug' => 'supervisor', 'descripcion' => 'Gestiona sesiones de inventario', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nombre' => 'Capturista de inventario', 'slug' => 'capturista', 'descripcion' => 'Captura datos en campo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nombre' => 'Supervisor Invitado', 'slug' => 'supervisor_invitado', 'descripcion' => 'Supervisor que puede realizar traspasos', 'created_at' => now(), 'updated_at' => now()],
        ]);
        $this->info('  4 roles seeded.');
    }

    private function importEmpresas(): void
    {
        $this->info('Importing empresas...');
        $rows = DB::connection($this->src)->table('empresas')->get();

        foreach ($rows as $row) {
            DB::table('empresas')->insert([
                'id' => $row->id,
                'codigo' => $row->codigo ?? '',
                'nombre' => $row->nombre ?? '',
                'usuario_id' => $row->usuario,
                'eliminado' => (bool) $row->eliminado,
                'created_at' => $row->fecha_hora ?? now(),
                'updated_at' => $row->fecha_hora ?? now(),
            ]);
        }

        $this->info("  {$rows->count()} empresas imported.");
    }

    private function extractSucursales(): array
    {
        $this->info('Extracting sucursales from inventarios...');

        $branches = DB::connection($this->src)->table('inventarios')
            ->select('empresa', 'sucursal', 'ciudad', 'nombre')
            ->where('sucursal', '!=', '')
            ->groupBy('empresa', 'sucursal', 'ciudad', 'nombre')
            ->get();

        $map = [];
        foreach ($branches as $branch) {
            $key = $branch->empresa . '|' . $branch->sucursal;
            if (isset($map[$key])) {
                continue;
            }

            $id = DB::table('sucursales')->insertGetId([
                'empresa_id' => $branch->empresa,
                'codigo' => $branch->sucursal,
                'nombre' => $branch->nombre ?: $branch->sucursal,
                'ciudad' => $branch->ciudad ?: null,
                'eliminado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $map[$key] = $id;
        }

        $this->info("  " . count($map) . " sucursales extracted.");
        return $map;
    }

    private function importUsers(): void
    {
        $this->info('Importing users...');
        $rows = DB::connection($this->src)->table('usuarios')->orderBy('id')->get();

        $seen = [];
        $imported = 0;

        foreach ($rows as $row) {
            $usuario = $row->usuario;

            // Handle duplicate usernames
            if (in_array($usuario, $seen)) {
                $usuario = $usuario . '_2';
            }
            $seen[] = $row->usuario;

            DB::table('users')->insert([
                'id' => $row->id,
                'usuario' => $usuario,
                'nombres' => $row->nombres ?? '',
                'email' => null,
                'password' => $row->password ?? '',
                'rol_id' => $row->id_rol,
                'acceso_web' => (bool) $row->acceso_cpanel,
                'acceso_app' => (bool) $row->acceso_app,
                'expiracion_sesion' => $row->expiracion_sesion ?? '2999-12-31',
                'archivo_imagen' => $row->archivo_imagen,
                'activo' => (bool) $row->activo,
                'eliminado' => (bool) $row->eliminado,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $imported++;
        }

        $this->info("  {$imported} users imported.");
    }

    private function importEmpresaUser(): void
    {
        $this->info('Importing empresa_user pivot...');
        $rows = DB::connection($this->src)->table('usuarios')
            ->where('empresasAcceso', '!=', '')
            ->get();

        $imported = 0;
        $empresaIds = DB::table('empresas')->pluck('id')->toArray();

        foreach ($rows as $row) {
            $ids = array_filter(array_map('trim', explode(',', $row->empresasAcceso)));
            foreach ($ids as $empresaId) {
                if (!is_numeric($empresaId) || !in_array((int) $empresaId, $empresaIds)) {
                    continue;
                }
                DB::table('empresa_user')->insert([
                    'user_id' => $row->id,
                    'empresa_id' => (int) $empresaId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $imported++;
            }
        }

        $this->info("  {$imported} empresa-user relations imported.");
    }

    private function importInventarios(array $sucursalMap): void
    {
        $this->info('Importing activo_fijo_inventarios...');
        $rows = DB::connection($this->src)->table('inventarios')->get();

        foreach ($rows as $row) {
            $key = $row->empresa . '|' . $row->sucursal;
            $sucursalId = $sucursalMap[$key] ?? 0;

            DB::table('activo_fijo_inventarios')->insert([
                'id' => $row->id,
                'empresa_id' => $row->empresa,
                'sucursal_id' => $sucursalId,
                'sucursal_codigo' => $row->sucursal ?? '',
                'ciudad' => $row->ciudad ?? '',
                'local' => $row->local ?? '',
                'nombre' => $row->nombre ?? '',
                'comentarios' => $row->comentarios ?? '',
                'usuario_id' => (int) $row->usuario,
                'status_id' => $row->status ?: 1,
                'inicio_conteo' => $row->inicio_conteo ?? 0,
                'fin_conteo' => $row->fin_conteo ?? 0,
                'finalizado' => (bool) $row->finalizado,
                'eliminado' => (bool) $row->eliminado,
                'created_at' => $row->fecha_hora ?? now(),
                'updated_at' => $row->ultima_actualizacion ?? $row->fecha_hora ?? now(),
            ]);
        }

        $this->info("  {$rows->count()} inventarios imported.");
    }

    private function importProductos(): void
    {
        $this->info('Importing activo_fijo_productos (chunked)...');

        $total = DB::connection($this->src)->table('productos')->count();
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');

        DB::connection($this->src)->table('productos')
            ->select([
                'id', 'id_inventario', 'id_empresa', 'subsidaria', 'sucursal',
                'codigo_1', 'codigo_2', 'codigo_3', 'tagRFID', 'descripcion',
                'n_serie', 'nSerieAnterior', 'n_serie_nuevo',
                'categoria_1', 'categoria_2', 'marca', 'modelo', 'tipo_activo',
                'fecha_inicio_servicio', 'imagen1', 'imagen2', 'imagen3',
                'cantidad_teorica', 'observaciones',
                'eliminado', 'no_encontrado', 'forzado', 'traspasado', 'solictado',
                'fecha_registro', 'ultima_actualizacion',
            ])
            ->orderBy('id')
            ->chunk(2000, function ($chunk) use ($bar) {
                $batch = [];
                foreach ($chunk as $row) {
                    $batch[] = [
                        'id' => $row->id,
                        'inventario_id' => $row->id_inventario,
                        'empresa_id' => $row->id_empresa,
                        'subsidiaria' => $row->subsidaria ?? '',
                        'sucursal' => $row->sucursal ?? 0,
                        'codigo_1' => $row->codigo_1 ?? '',
                        'codigo_2' => $row->codigo_2 ?? '',
                        'codigo_3' => $row->codigo_3 ?? '',
                        'tag_rfid' => $row->tagRFID ?? '',
                        'descripcion' => $row->descripcion ?? '',
                        'n_serie' => $row->n_serie ?? '',
                        'n_serie_anterior' => $row->nSerieAnterior ?? '',
                        'n_serie_nuevo' => $row->n_serie_nuevo ?? '',
                        'categoria_1' => $row->categoria_1 ?? '',
                        'categoria_2' => $row->categoria_2 ?? '',
                        'marca' => $row->marca ?? '',
                        'modelo' => $row->modelo ?? '',
                        'tipo_activo' => $row->tipo_activo ?? '',
                        'fecha_inicio_servicio' => $row->fecha_inicio_servicio ?? '',
                        'imagen1' => $row->imagen1 ?? '',
                        'imagen2' => $row->imagen2 ?? '',
                        'imagen3' => $row->imagen3 ?? '',
                        'cantidad_teorica' => $row->cantidad_teorica ?? 0,
                        'observaciones' => $row->observaciones ?: null,
                        'eliminado' => (bool) $row->eliminado,
                        'no_encontrado' => (bool) $row->no_encontrado,
                        'forzado' => (bool) $row->forzado,
                        'traspasado' => (bool) $row->traspasado,
                        'solicitado' => (bool) $row->solictado,
                        'fecha_registro' => $row->fecha_registro,
                        'created_at' => $row->fecha_registro ?? now(),
                        'updated_at' => $row->ultima_actualizacion ?? $row->fecha_registro ?? now(),
                    ];
                }
                DB::table('activo_fijo_productos')->insert($batch);
                $bar->advance(count($chunk));
            });

        $bar->finish();
        $this->newLine();
        $this->info("  {$total} productos imported.");
    }

    private function importRegistros(): void
    {
        $this->info('Importing activo_fijo_registros (chunked)...');

        $total = DB::connection($this->src)->table('inventario_registros')->count();
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');

        DB::connection($this->src)->table('inventario_registros')
            ->select([
                'id', 'id_inventario', 'id_usuario', 'id_producto',
                'traspasado', 'cantidad', 'sucursal_origen',
                'codigo_1', 'codigo1Anterior', 'codigo_2', 'codigo_3', 'tagRFID',
                'n_serie', 'nSerieAnterior', 'n_serie_nuevo',
                'nombre_almacen', 'version_app', 'ubicacion_1',
                'imagen1', 'imagen2', 'imagen3', 'observaciones',
                'forzado', 'solicitado', 'latitud', 'longitud',
                'fecha_hora', 'ultima_actualizacion', 'eliminado',
            ])
            ->orderBy('id')
            ->chunk(2000, function ($chunk) use ($bar) {
                $batch = [];
                foreach ($chunk as $row) {
                    $batch[] = [
                        'id' => $row->id,
                        'inventario_id' => $row->id_inventario,
                        'usuario_id' => $row->id_usuario,
                        'id_producto' => $row->id_producto,
                        'codigo_1' => $row->codigo_1 ?? '',
                        'codigo_1_anterior' => $row->codigo1Anterior ?? '',
                        'codigo_2' => $row->codigo_2 ?? '',
                        'codigo_3' => $row->codigo_3 ?? '',
                        'tag_rfid' => $row->tagRFID ?? '',
                        'n_serie' => $row->n_serie ?? '',
                        'n_serie_anterior' => $row->nSerieAnterior ?? '',
                        'n_serie_nuevo' => $row->n_serie_nuevo ?? '',
                        'nombre_almacen' => $row->nombre_almacen ?? '',
                        'ubicacion_1' => $row->ubicacion_1 ?? '',
                        'imagen1' => $row->imagen1 ?? '',
                        'imagen2' => $row->imagen2 ?? '',
                        'imagen3' => $row->imagen3 ?? '',
                        'observaciones' => $row->observaciones ?: null,
                        'traspasado' => $row->traspasado === 'true' || $row->traspasado === '1',
                        'sucursal_origen' => $row->sucursal_origen ?? '',
                        'forzado' => (bool) $row->forzado,
                        'solicitado' => (bool) $row->solicitado,
                        'latitud' => $row->latitud ?? 0,
                        'longitud' => $row->longitud ?? 0,
                        'version_app' => $row->version_app ?? '',
                        'eliminado' => (bool) $row->eliminado,
                        'created_at' => $row->fecha_hora ?? now(),
                        'updated_at' => $row->ultima_actualizacion ?? $row->fecha_hora ?? now(),
                    ];
                }
                DB::table('activo_fijo_registros')->insert($batch);
                $bar->advance(count($chunk));
            });

        $bar->finish();
        $this->newLine();
        $this->info("  {$total} registros imported.");
    }

    private function importLogSesiones(): void
    {
        $this->info('Importing log_sesiones_movil...');
        $rows = DB::connection($this->src)->table('log_sesiones')->get();

        $batch = [];
        foreach ($rows as $row) {
            $batch[] = [
                'id' => $row->id,
                'inventario_id' => $row->id_inventario,
                'usuario_id' => $row->usuario,
                'fecha_hora_entrada' => $row->fecha_hora_entrada,
                'fecha_hora_salida' => $row->fecha_hora_salida,
                'plataforma_dispositivo' => $row->plataforma_dispositivo,
                'serie_dispositivo' => $row->serie_dispositivo,
                'latitud' => $row->latitud ?? 0,
                'longitud' => $row->longitud ?? 0,
                'sesion_activa' => (bool) $row->sesion_activa,
                'created_at' => $row->fecha_hora_entrada ?? now(),
                'updated_at' => $row->fecha_hora_salida ?? $row->fecha_hora_entrada ?? now(),
            ];

            if (count($batch) >= 500) {
                DB::table('log_sesiones_movil')->insert($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('log_sesiones_movil')->insert($batch);
        }

        $this->info("  {$rows->count()} log entries imported.");
    }

    private function importOrdenesEntrada(): void
    {
        $this->info('Importing ordenes_entrada + detalle...');

        $ordenes = DB::connection($this->src)->table('ordenes_entrada')->get();
        foreach ($ordenes as $row) {
            DB::table('ordenes_entrada')->insert([
                'id' => $row->id,
                'usuario_id' => $row->usuario,
                'n_orden' => $row->nOrden,
                'inventario_origen_id' => $row->sucursalOrigen,
                'inventario_destino_id' => $row->sucursalDestino,
                'motivo' => $row->motivo ?? '',
                'comentarios' => $row->comentarios ?: null,
                'estatus_id' => $row->estatus ?: 1,
                'autorizado_por' => $row->autorizadoPor ?: null,
                'surtido_por' => $row->surtidoPor ?: null,
                'cancelado_por' => $row->canceladoPor ?: null,
                'rechazado_por' => $row->rechazadoPor ?: null,
                'fecha_hora_surtido' => $row->fechaHoraSurtido,
                'fecha_hora_cancelacion' => $row->fechaHoraCancelacion,
                'eliminado' => (bool) $row->eliminado,
                'created_at' => $row->fechaHora ?? now(),
                'updated_at' => $row->fechaHora ?? now(),
            ]);
        }

        $detalles = DB::connection($this->src)->table('ordenes_entrada_detalle')->get();
        foreach ($detalles as $row) {
            DB::table('ordenes_entrada_detalle')->insert([
                'id' => $row->id,
                'orden_entrada_id' => $row->ordenEntrada,
                'registro_id' => $row->idRegistro,
                'inventario_id' => $row->idInventario,
                'estatus' => $row->estatus ?? 0,
                'eliminado' => (bool) $row->eliminado,
                'created_at' => $row->ultimaActualizacion ?? now(),
                'updated_at' => $row->ultimaActualizacion ?? now(),
            ]);
        }

        $this->info("  {$ordenes->count()} ordenes + {$detalles->count()} detalles imported.");
    }

    private function importActivosNoEncontrados(): void
    {
        $this->info('Importing activos_no_encontrados...');

        $rows = DB::connection($this->src)->table('activos_no_encontrados')->get();

        foreach ($rows as $row) {
            $inventarioId = DB::table('activo_fijo_productos')
                ->where('id', $row->activo)
                ->value('inventario_id');

            DB::table('activos_no_encontrados')->insert([
                'id' => $row->id,
                'inventario_id' => $inventarioId ?? 0,
                'activo' => $row->activo,
                'usuario_id' => $row->usuario,
                'latitud' => $row->latitud ?? 0,
                'longitud' => $row->longitud ?? 0,
                'created_at' => $row->fechaHora ?? now(),
                'updated_at' => $row->fechaHora ?? now(),
            ]);
        }

        $this->info("  {$rows->count()} activos no encontrados imported.");
    }

    private function verifyCounts(): void
    {
        $this->newLine();
        $this->info('Verifying import counts...');

        $checks = [
            ['empresas', 5],
            ['sucursales', null],
            ['users', 56],
            ['empresa_user', null],
            ['activo_fijo_inventarios', 733],
            ['activo_fijo_productos', null],
            ['activo_fijo_registros', null],
            ['log_sesiones_movil', 4126],
            ['ordenes_entrada', 9],
            ['ordenes_entrada_detalle', 14],
            ['activos_no_encontrados', 2],
        ];

        $tableData = [];
        foreach ($checks as [$table, $expected]) {
            $actual = DB::table($table)->count();
            $status = $expected === null ? '-' : ($actual >= $expected ? 'OK' : 'MISMATCH');
            $tableData[] = [$status, $table, $expected ?? '?', number_format($actual)];
        }

        $this->table(['Status', 'Table', 'Expected', 'Actual'], $tableData);
    }
}
