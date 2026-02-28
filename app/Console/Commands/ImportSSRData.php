<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportSSRData extends Command
{
    protected $signature = 'app:import-ssr';
    protected $description = 'Import SSR inventory data from seretail_ssm_ssr database';

    private string $src = 'source_ssr';

    // SSR role IDs are reversed vs Laravel
    // SSR: 1=Capturista, 2=Supervisor, 3=Super Admin
    // Laravel: 1=Super Admin, 2=Supervisor, 3=Capturista
    private array $roleMap = [1 => 3, 2 => 2, 3 => 1];

    public function handle(): int
    {
        $this->info('=== SER Inventarios - SSR Data Import ===');
        $this->newLine();

        // Verify source DB
        try {
            $count = DB::connection($this->src)->table('inventarios')->count();
            $this->info("Source SSR database connected. Found {$count} inventarios.");
        } catch (\Exception $e) {
            $this->error('Cannot connect to source SSR database (seretail_ssm_ssr).');
            $this->error($e->getMessage());
            return 1;
        }

        // Verify activo fijo import was run first
        $empresaCount = DB::table('empresas')->count();
        if ($empresaCount === 0) {
            $this->error('No empresas found. Run app:import-production first.');
            return 1;
        }
        $this->info("Target database has {$empresaCount} empresas from activo fijo import.");

        Schema::disableForeignKeyConstraints();

        $empresaMap = $this->importEmpresas();
        $sucursalMap = $this->importSucursales($empresaMap);
        $userMap = $this->importUsers($empresaMap);
        $inventarioMap = $this->importInventarios($empresaMap, $sucursalMap, $userMap);
        $this->importProductos($inventarioMap, $empresaMap);
        $this->importLotesCaducidades($inventarioMap, $empresaMap);

        Schema::enableForeignKeyConstraints();
        $this->verifyCounts();

        $this->newLine();
        $this->info('SSR import completed successfully!');
        return 0;
    }

    private function importEmpresas(): array
    {
        $this->info('Mapping SSR empresas...');

        $ssrInventarios = DB::connection($this->src)->table('inventarios')->get();
        $empresaMap = []; // SSR empresa text => target empresa_id

        foreach ($ssrInventarios as $inv) {
            $empresaName = trim($inv->empresa);
            if (empty($empresaName) || isset($empresaMap[$empresaName])) {
                continue;
            }

            // Check if empresa already exists by name
            $existing = DB::table('empresas')
                ->whereRaw('LOWER(nombre) = ?', [strtolower($empresaName)])
                ->first();

            if ($existing) {
                $empresaMap[$empresaName] = $existing->id;
                $this->info("  Empresa '{$empresaName}' already exists (ID {$existing->id}).");
            } else {
                // Generate unique codigo from name
                $codigo = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $empresaName), 0, 15));
                // Ensure uniqueness
                $suffix = 0;
                $baseCodigo = $codigo;
                while (DB::table('empresas')->where('codigo', $codigo)->exists()) {
                    $suffix++;
                    $codigo = substr($baseCodigo, 0, 13) . '_' . $suffix;
                }

                // Use the first super admin user as owner
                $adminId = DB::table('users')->where('rol_id', 1)->value('id') ?? 1;

                $id = DB::table('empresas')->insertGetId([
                    'codigo' => $codigo,
                    'nombre' => $empresaName,
                    'usuario_id' => $adminId,
                    'eliminado' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $empresaMap[$empresaName] = $id;
                $this->info("  Created empresa '{$empresaName}' (ID {$id}, codigo: {$codigo}).");
            }
        }

        $this->info("  " . count($empresaMap) . " SSR empresas mapped.");
        return $empresaMap;
    }

    private function importSucursales(array $empresaMap): array
    {
        $this->info('Creating SSR sucursales...');
        $sucursalMap = []; // empresa text => sucursal_id

        foreach ($empresaMap as $empresaName => $empresaId) {
            // Check if empresa already has a sucursal
            $existing = DB::table('sucursales')
                ->where('empresa_id', $empresaId)
                ->first();

            if ($existing) {
                $sucursalMap[$empresaName] = $existing->id;
                $this->info("  Empresa '{$empresaName}' already has sucursal (ID {$existing->id}).");
            } else {
                $id = DB::table('sucursales')->insertGetId([
                    'empresa_id' => $empresaId,
                    'codigo' => 'PRINCIPAL',
                    'nombre' => 'Sucursal Principal',
                    'ciudad' => null,
                    'eliminado' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sucursalMap[$empresaName] = $id;
                $this->info("  Created sucursal 'Sucursal Principal' for '{$empresaName}' (ID {$id}).");
            }
        }

        $this->info("  " . count($sucursalMap) . " sucursales mapped/created.");
        return $sucursalMap;
    }

    private function importUsers(array $empresaMap): array
    {
        $this->info('Importing SSR users (merging with existing)...');
        $ssrUsers = DB::connection($this->src)->table('usuarios')->get();
        $userMap = []; // SSR id_usuario => target user id

        foreach ($ssrUsers as $ssrUser) {
            $usuario = trim($ssrUser->usuario);

            // Check if user already exists by username (case-insensitive)
            $existing = DB::table('users')
                ->whereRaw('LOWER(usuario) = ?', [strtolower($usuario)])
                ->first();

            if ($existing) {
                // Check if it's truly the same person by matching nombres
                $ssrNombres = strtolower(trim($ssrUser->nombres ?? ''));
                $existingNombres = strtolower(trim($existing->nombres ?? ''));

                if ($ssrNombres === $existingNombres || str_contains($ssrNombres, $existingNombres) || str_contains($existingNombres, $ssrNombres)) {
                    // Same person — merge
                    $this->info("  User '{$usuario}' matched existing ID {$existing->id} ({$existing->nombres}). Merging.");
                    $userMap[$ssrUser->id_usuario] = $existing->id;
                } else {
                    // Different person with same username — create with suffix
                    $newUsuario = strtolower($usuario) . '_ssr';
                    $this->warn("  User '{$usuario}' exists as '{$existing->nombres}' but SSR has '{$ssrUser->nombres}'. Creating as '{$newUsuario}'.");

                    $newId = $this->createUser($ssrUser, $newUsuario);
                    $userMap[$ssrUser->id_usuario] = $newId;
                }
            } else {
                // Create new user
                $newId = $this->createUser($ssrUser, $usuario);
                $userMap[$ssrUser->id_usuario] = $newId;
                $this->info("  Created user '{$usuario}' (ID {$newId}).");
            }

            // Grant empresa access for all SSR empresas
            foreach ($empresaMap as $empresaId) {
                DB::table('empresa_user')->insertOrIgnore([
                    'empresa_id' => $empresaId,
                    'user_id' => $userMap[$ssrUser->id_usuario],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->info("  " . count($userMap) . " SSR users mapped.");
        return $userMap;
    }

    private function createUser(object $ssrUser, string $usuario): int
    {
        return DB::table('users')->insertGetId([
            'usuario' => $usuario,
            'nombres' => $ssrUser->nombres ?? '',
            'email' => null,
            'password' => $ssrUser->password ?? '',
            'rol_id' => $this->roleMap[$ssrUser->rol] ?? 3,
            'acceso_web' => (bool) $ssrUser->acceso_pc,
            'acceso_app' => (bool) $ssrUser->acceso_app,
            'expiracion_sesion' => $ssrUser->expiracion_sesion ?? '2999-12-31',
            'activo' => true,
            'eliminado' => (bool) $ssrUser->eliminado,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function importInventarios(array $empresaMap, array $sucursalMap, array $userMap): array
    {
        $this->info('Importing SSR inventarios...');
        $rows = DB::connection($this->src)->table('inventarios')->get();
        $inventarioMap = []; // SSR id_inventario => target inventario id

        foreach ($rows as $row) {
            $empresaName = trim($row->empresa);
            $empresaId = $empresaMap[$empresaName] ?? null;
            $sucursalId = $sucursalMap[$empresaName] ?? null;
            $usuarioId = $userMap[$row->usuario] ?? 1;

            if (!$empresaId || !$sucursalId) {
                $this->warn("  Skipping inventario {$row->id_inventario}: unmapped empresa '{$empresaName}'.");
                continue;
            }

            $newId = DB::table('inventarios')->insertGetId([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'nombre' => $row->nombre_inventario ?? '',
                'usuario_id' => $usuarioId,
                'nombre_usuario' => $row->nombre_usuario,
                'auditor' => $row->auditor,
                'firma_auditor' => $row->firma_auditor,
                'gerente' => $row->gerente,
                'firma_gerente' => $row->firma_gerente,
                'subgerente' => $row->subgerente,
                'firma_subgerente' => $row->firma_subgerente,
                'inicio_conteo' => $row->inicio_conteo ?? 0,
                'fin_conteo' => $row->fin_conteo ?? 0,
                'status_id' => 1,
                'finalizado' => false,
                'comentarios' => null,
                'motivo_cancelacion' => $row->motivo_cancelacion,
                'eliminado' => (bool) $row->eliminado,
                'created_at' => $row->fecha_creacion ?? now(),
                'updated_at' => $row->ultima_actualizacion ?? now(),
            ]);

            $inventarioMap[$row->id_inventario] = $newId;
            $this->info("  Inventario '{$row->nombre_inventario}' ({$empresaName}) => ID {$newId}.");
        }

        $this->info("  " . count($inventarioMap) . " inventarios imported.");
        return $inventarioMap;
    }

    private function importProductos(array $inventarioMap, array $empresaMap): void
    {
        $this->info('Importing SSR productos (chunked)...');

        // Build inventario-to-empresa mapping
        $invToEmpresa = [];
        $ssrInventarios = DB::connection($this->src)->table('inventarios')->get();
        foreach ($ssrInventarios as $inv) {
            $empresaName = trim($inv->empresa);
            $invToEmpresa[$inv->id_inventario] = $empresaMap[$empresaName] ?? null;
        }

        $total = DB::connection($this->src)->table('productos')->count();
        $bar = $this->output->createProgressBar($total);
        $imported = 0;

        DB::connection($this->src)->table('productos')
            ->select([
                'id_producto', 'inventario', 'codigo_1', 'codigo_2', 'codigo_3',
                'codigo_4', 'codigo_5', 'descripcion', 'marca', 'modelo',
                'categoria', 'subcategoria', 'subcategoria_2',
                'precio_compra', 'precio', 'cantidad_teorica', 'factor',
                'unidad_medida', 'seriado', 'observaciones',
                'eliminado', 'forzado', 'fecha_registro',
            ])
            ->orderBy('id_producto')
            ->chunk(500, function ($chunk) use (&$imported, $invToEmpresa, $bar) {
                $batch = [];
                foreach ($chunk as $row) {
                    $empresaId = $invToEmpresa[$row->inventario] ?? null;
                    if (!$empresaId) {
                        continue;
                    }

                    $batch[] = [
                        'empresa_id' => $empresaId,
                        'codigo_1' => $row->codigo_1 ?? '',
                        'codigo_2' => $row->codigo_2,
                        'codigo_3' => $row->codigo_3,
                        'codigo_4' => $row->codigo_4,
                        'codigo_5' => $row->codigo_5,
                        'descripcion' => $row->descripcion ?? '',
                        'marca' => $row->marca,
                        'modelo' => $row->modelo,
                        'categoria' => $row->categoria,
                        'subcategoria' => $row->subcategoria,
                        'subcategoria_2' => $row->subcategoria_2,
                        'precio_compra' => $row->precio_compra ?? 0,
                        'precio_venta' => $row->precio ?? 0,
                        'cantidad_teorica' => $row->cantidad_teorica ?? 0,
                        'factor' => $row->factor ?: 1,
                        'unidad_medida' => $row->unidad_medida,
                        'seriado' => ($row->seriado === 'Si' || $row->seriado === '1'),
                        'observaciones' => $row->observaciones,
                        'forzado' => (bool) $row->forzado,
                        'eliminado' => (bool) $row->eliminado,
                        'created_at' => $row->fecha_registro ?? now(),
                        'updated_at' => $row->fecha_registro ?? now(),
                    ];
                    $imported++;
                }

                if (!empty($batch)) {
                    DB::table('productos')->insert($batch);
                }
                $bar->advance(count($chunk));
            });

        $bar->finish();
        $this->newLine();
        $this->info("  {$imported} productos imported.");
    }

    private function importLotesCaducidades(array $inventarioMap, array $empresaMap): void
    {
        $this->info('Importing SSR lotes_caducidades (chunked)...');

        // Build inventario-to-empresa mapping
        $invToEmpresa = [];
        $ssrInventarios = DB::connection($this->src)->table('inventarios')->get();
        foreach ($ssrInventarios as $inv) {
            $empresaName = trim($inv->empresa);
            $invToEmpresa[$inv->id_inventario] = $empresaMap[$empresaName] ?? null;
        }

        $total = DB::connection($this->src)->table('lotes_caducidades')->count();
        $bar = $this->output->createProgressBar($total);
        $imported = 0;
        $dateParseErrors = 0;

        DB::connection($this->src)->table('lotes_caducidades')
            ->orderBy('id')
            ->chunk(500, function ($chunk) use (&$imported, &$dateParseErrors, $invToEmpresa, $bar) {
                $batch = [];
                foreach ($chunk as $row) {
                    $empresaId = $invToEmpresa[$row->inventario] ?? null;
                    if (!$empresaId) {
                        continue;
                    }

                    $fecha = $this->parseFechaCaducidad($row->fechaCaducidad ?? null);
                    if (!empty($row->fechaCaducidad) && !$fecha) {
                        $dateParseErrors++;
                    }

                    $batch[] = [
                        'empresa_id' => $empresaId,
                        'sku' => $row->codigo2 ?? $row->codigo1 ?? '',
                        'descripcion' => null,
                        'lote' => $row->lote ?? '',
                        'fecha_caducidad' => $fecha,
                        'cantidad' => $row->cantidad ?? 0,
                        'almacen' => null,
                        'eliminado' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $imported++;
                }

                if (!empty($batch)) {
                    DB::table('lotes_caducidades')->insert($batch);
                }
                $bar->advance(count($chunk));
            });

        $bar->finish();
        $this->newLine();
        if ($dateParseErrors > 0) {
            $this->warn("  {$dateParseErrors} date parsing errors (stored as NULL).");
        }
        $this->info("  {$imported} lotes imported.");
    }

    private function parseFechaCaducidad(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Extract date portion: "dd/mm/yyyy" from "dd/mm/yyyy 12:00:00 a. m."
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $value, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $year = $m[3];
            return "{$year}-{$month}-{$day}";
        }

        return null;
    }

    private function verifyCounts(): void
    {
        $this->newLine();
        $this->info('Verifying SSR import counts...');

        $checks = [
            ['empresas (total)', null, DB::table('empresas')->count()],
            ['sucursales (total)', null, DB::table('sucursales')->count()],
            ['users (total)', null, DB::table('users')->count()],
            ['inventarios (SSR)', 2, DB::table('inventarios')->count()],
            ['productos (SSR)', 4919, DB::table('productos')->count()],
            ['lotes_caducidades (SSR)', 2929, DB::table('lotes_caducidades')->count()],
        ];

        $headers = ['Status', 'Table', 'Expected', 'Actual'];
        $rows = [];
        foreach ($checks as [$table, $expected, $actual]) {
            if ($expected === null) {
                $status = '-';
            } elseif ($actual >= $expected) {
                $status = 'OK';
            } else {
                $status = 'MISMATCH';
            }
            $rows[] = [$status, $table, $expected ?? '?', number_format($actual)];
        }

        $this->table($headers, $rows);
    }
}
