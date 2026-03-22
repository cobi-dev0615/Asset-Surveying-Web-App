<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\TestDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestDataSeeder::class);
    }

    public function test_empresas_returns_user_empresas(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->getJson('/api/empresas');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['codigo' => 'TEST01', 'nombre' => 'Empresa Test']);
    }

    public function test_empresas_includes_sucursales(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->getJson('/api/empresas');

        $response->assertOk();
        $sucursales = $response->json('0.sucursales');
        $this->assertCount(2, $sucursales);
    }

    public function test_sucursales_by_empresa(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->getJson('/api/empresas/1/sucursales');

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['codigo' => 'SUC01']);
    }

    public function test_productos_by_empresa(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->getJson('/api/empresas/1/productos');

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['codigo_1' => 'PROD001']);
    }

    public function test_productos_pagination(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->getJson('/api/empresas/1/productos?per_page=1');

        $response->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 1);
    }

    public function test_statuses_returns_all(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->getJson('/api/statuses');

        $response->assertOk()
            ->assertJsonCount(3)
            ->assertJsonFragment(['status' => 'PENDIENTE POR INICIAR'])
            ->assertJsonFragment(['status' => 'INICIADO'])
            ->assertJsonFragment(['status' => 'FINALIZADO']);
    }

    public function test_activo_fijo_sessions(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->getJson('/api/activo-fijo');

        $response->assertOk()
            ->assertJsonCount(1);
    }

    public function test_inventarios_sessions(): void
    {
        Sanctum::actingAs(User::find(1));

        $response = $this->getJson('/api/inventarios');

        $response->assertOk()
            ->assertJsonCount(1);
    }

    public function test_unauthenticated_access_denied(): void
    {
        $this->getJson('/api/empresas')->assertStatus(401);
        $this->getJson('/api/statuses')->assertStatus(401);
        $this->getJson('/api/activo-fijo')->assertStatus(401);
    }
}
