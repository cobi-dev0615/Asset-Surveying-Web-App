<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'Super Administrador', 'slug' => 'super_admin', 'descripcion' => 'Acceso total', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Supervisor', 'slug' => 'supervisor', 'descripcion' => 'Gestiona inventarios', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nombre' => 'Capturista', 'slug' => 'capturista', 'descripcion' => 'Captura datos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nombre' => 'Supervisor Invitado', 'slug' => 'supervisor_invitado', 'descripcion' => 'Traspasos', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Statuses
        DB::table('inventarios_status')->insert([
            ['id' => 1, 'status' => 'PENDIENTE POR INICIAR'],
            ['id' => 2, 'status' => 'INICIADO'],
            ['id' => 3, 'status' => 'FINALIZADO'],
        ]);

        // Admin user (web + app access)
        DB::table('users')->insert([
            'id' => 1,
            'usuario' => 'admin_test',
            'nombres' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'rol_id' => 1,
            'acceso_web' => true,
            'acceso_app' => true,
            'expiracion_sesion' => '2999-12-31',
            'eliminado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Capturista user (app only)
        DB::table('users')->insert([
            'id' => 2,
            'usuario' => 'capturista_test',
            'nombres' => 'Capturista Test',
            'email' => 'cap@test.com',
            'password' => Hash::make('password123'),
            'rol_id' => 3,
            'acceso_web' => false,
            'acceso_app' => true,
            'expiracion_sesion' => '2999-12-31',
            'eliminado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Web-only user (no app access)
        DB::table('users')->insert([
            'id' => 3,
            'usuario' => 'webonly_test',
            'nombres' => 'Web Only',
            'email' => 'web@test.com',
            'password' => Hash::make('password123'),
            'rol_id' => 2,
            'acceso_web' => true,
            'acceso_app' => false,
            'expiracion_sesion' => '2999-12-31',
            'eliminado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Empresa
        DB::table('empresas')->insert([
            'id' => 1,
            'codigo' => 'TEST01',
            'nombre' => 'Empresa Test',
            'usuario_id' => 1,
            'eliminado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign empresa to users
        DB::table('empresa_user')->insert([
            ['empresa_id' => 1, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['empresa_id' => 1, 'user_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Sucursales
        DB::table('sucursales')->insert([
            ['id' => 1, 'empresa_id' => 1, 'codigo' => 'SUC01', 'nombre' => 'Sucursal Centro', 'ciudad' => 'CDMX', 'eliminado' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'empresa_id' => 1, 'codigo' => 'SUC02', 'nombre' => 'Sucursal Norte', 'ciudad' => 'Monterrey', 'eliminado' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Producto
        DB::table('productos')->insert([
            'id' => 1,
            'empresa_id' => 1,
            'codigo_1' => 'PROD001',
            'descripcion' => 'Producto de prueba',
            'marca' => 'TestBrand',
            'precio_compra' => 100,
            'precio_venta' => 150,
            'cantidad_teorica' => 50,
            'eliminado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Inventario session (product counting)
        DB::table('inventarios')->insert([
            'id' => 1,
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'nombre' => 'Inventario Test',
            'usuario_id' => 1,
            'status_id' => 2,
            'eliminado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Activo Fijo session
        DB::table('activo_fijo_inventarios')->insert([
            'id' => 1,
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'usuario_id' => 1,
            'status_id' => 2,
            'eliminado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
