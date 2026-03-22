<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolesAndStatusSeeder::class);

        DB::table('users')->insert([
            'usuario' => 'alan',
            'nombres' => 'Alan Villegas',
            'email' => 'avillegas@seretail.com.mx',
            'password' => Hash::make('admin123'),
            'rol_id' => 3,
            'acceso_web' => true,
            'acceso_app' => true,
            'expiracion_sesion' => '2999-12-31',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
