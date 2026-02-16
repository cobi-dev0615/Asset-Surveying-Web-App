<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'Super Administrador del sistema', 'slug' => 'super_admin', 'descripcion' => 'Acceso total al sistema', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Supervisor de inventarios', 'slug' => 'supervisor', 'descripcion' => 'Gestiona sesiones de inventario', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nombre' => 'Capturista de inventario', 'slug' => 'capturista', 'descripcion' => 'Captura datos en campo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nombre' => 'Supervisor Invitado', 'slug' => 'supervisor_invitado', 'descripcion' => 'Supervisor que puede realizar traspasos', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('inventarios_status')->insert([
            ['id' => 1, 'status' => 'PENDIENTE POR INICIAR'],
            ['id' => 2, 'status' => 'INICIADO'],
            ['id' => 3, 'status' => 'FINALIZADO'],
        ]);
    }
}
