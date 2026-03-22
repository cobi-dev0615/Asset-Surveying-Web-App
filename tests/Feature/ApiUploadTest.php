<?php

namespace Tests\Feature;

use App\Models\ActivoFijoRegistro;
use App\Models\User;
use Database\Seeders\TestDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestDataSeeder::class);
    }

    // -- Activo Fijo Registros --

    public function test_upload_activo_fijo_registros(): void
    {
        Sanctum::actingAs(User::find(2)); // capturista

        $response = $this->postJson('/api/activo-fijo/upload', [
            'inventario_id' => 1,
            'registros' => [
                [
                    'codigo_1' => 'ACT-001',
                    'descripcion' => 'Laptop Dell',
                    'ubicacion_1' => 'Oficina 1',
                    'categoria' => 'Computo',
                ],
                [
                    'codigo_1' => 'ACT-002',
                    'descripcion' => 'Monitor Samsung',
                    'ubicacion_1' => 'Oficina 2',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['count' => 2]);

        $this->assertDatabaseCount('activo_fijo_registros', 2);
        $this->assertDatabaseHas('activo_fijo_registros', [
            'codigo_1' => 'ACT-001',
            'descripcion' => 'Laptop Dell',
            'usuario_id' => 2,
        ]);
    }

    public function test_upload_activo_fijo_validation(): void
    {
        Sanctum::actingAs(User::find(2));

        $response = $this->postJson('/api/activo-fijo/upload', [
            'inventario_id' => 1,
            'registros' => [
                ['descripcion' => 'Missing codigo_1'],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['registros.0.codigo_1']);
    }

    public function test_upload_activo_fijo_invalid_session(): void
    {
        Sanctum::actingAs(User::find(2));

        $response = $this->postJson('/api/activo-fijo/upload', [
            'inventario_id' => 999,
            'registros' => [
                ['codigo_1' => 'ACT-001'],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['inventario_id']);
    }

    // -- No Encontrados --

    public function test_upload_no_encontrados(): void
    {
        Sanctum::actingAs(User::find(2));

        $response = $this->postJson('/api/activo-fijo/no-encontrados', [
            'inventario_id' => 1,
            'activos' => [
                ['activo' => 100, 'latitud' => 19.432, 'longitud' => -99.133],
                ['activo' => 200],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Activos no encontrados registrados.']);

        $this->assertDatabaseCount('activos_no_encontrados', 2);
        $this->assertDatabaseHas('activos_no_encontrados', [
            'activo' => 100,
            'usuario_id' => 2,
        ]);
    }

    // -- Traspasos --

    public function test_upload_traspasos(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->postJson('/api/activo-fijo/traspasos', [
            'traspasos' => [
                [
                    'activo' => 500,
                    'sucursal_origen_id' => 1,
                    'sucursal_destino_id' => 2,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Traspasos registrados.']);

        $this->assertDatabaseHas('activos_traspasados', [
            'activo' => 500,
            'sucursal_origen_id' => 1,
            'sucursal_destino_id' => 2,
            'usuario_id' => 1,
        ]);
    }

    public function test_upload_traspasos_validation(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->postJson('/api/activo-fijo/traspasos', [
            'traspasos' => [
                ['activo' => 500],
            ],
        ]);

        $response->assertStatus(422);
    }

    // -- Inventario (Product counting) Upload --

    public function test_upload_inventario_registros(): void
    {
        Sanctum::actingAs(User::find(2));

        $response = $this->postJson('/api/inventarios/upload', [
            'inventario_id' => 1,
            'registros' => [
                [
                    'codigo_1' => 'PROD001',
                    'cantidad' => 5,
                    'producto_id' => 1,
                    'ubicacion_1' => 'Pasillo A',
                ],
                [
                    'codigo_1' => 'PROD002',
                    'cantidad' => 10,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['count' => 2]);

        $this->assertDatabaseCount('inventario_registros', 2);
    }

    // -- Image Upload --

    public function test_upload_imagen_multipart(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::find(2));

        $registro = ActivoFijoRegistro::create([
            'inventario_id' => 1,
            'usuario_id' => 2,
            'codigo_1' => 'IMG-TEST',
        ]);

        $file = UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/activo-fijo/upload-imagen', [
            'registro_id' => $registro->id,
            'campo' => 'imagen1',
            'imagen' => $file,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'path']);

        $this->assertNotNull($registro->fresh()->imagen1);
    }

    public function test_upload_imagen_base64(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::find(2));

        $registro = ActivoFijoRegistro::create([
            'inventario_id' => 1,
            'usuario_id' => 2,
            'codigo_1' => 'IMG-TEST-2',
        ]);

        $base64 = base64_encode(str_repeat('x', 300));

        $response = $this->postJson('/api/activo-fijo/upload-imagen', [
            'registro_id' => $registro->id,
            'campo' => 'imagen2',
            'imagen_base64' => $base64,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'path']);

        $this->assertNotNull($registro->fresh()->imagen2);
    }

    public function test_upload_imagen_validation(): void
    {
        Sanctum::actingAs(User::find(2));

        $response = $this->postJson('/api/activo-fijo/upload-imagen', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['registro_id', 'campo']);
    }

    public function test_upload_imagen_invalid_campo(): void
    {
        Sanctum::actingAs(User::find(2));

        $registro = ActivoFijoRegistro::create([
            'inventario_id' => 1,
            'usuario_id' => 2,
            'codigo_1' => 'IMG-TEST-3',
        ]);

        $response = $this->postJson('/api/activo-fijo/upload-imagen', [
            'registro_id' => $registro->id,
            'campo' => 'imagen_hacked',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['campo']);
    }
}
