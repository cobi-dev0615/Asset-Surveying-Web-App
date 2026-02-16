<?php

namespace Tests\Feature;

use Database\Seeders\TestDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestDataSeeder::class);
    }

    public function test_login_success(): void
    {
        $response = $this->postJson('/api/login', [
            'usuario' => 'admin_test',
            'password' => 'password123',
            'device_name' => 'PHPUnit',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'usuario', 'nombres', 'rol', 'empresas'],
            ]);

        $this->assertEquals('super_admin', $response->json('user.rol'));
    }

    public function test_login_wrong_password(): void
    {
        $response = $this->postJson('/api/login', [
            'usuario' => 'admin_test',
            'password' => 'wrong',
            'device_name' => 'PHPUnit',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Credenciales incorrectas.']);
    }

    public function test_login_denied_no_app_access(): void
    {
        $response = $this->postJson('/api/login', [
            'usuario' => 'webonly_test',
            'password' => 'password123',
            'device_name' => 'PHPUnit',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'No tienes acceso a la aplicación móvil.']);
    }

    public function test_login_validation_error(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['usuario', 'password', 'device_name']);
    }

    public function test_me_returns_user_info(): void
    {
        $token = $this->loginAsAdmin();

        $response = $this->withToken($token)->getJson('/api/me');

        $response->assertOk()
            ->assertJsonStructure(['id', 'usuario', 'nombres', 'rol', 'empresas'])
            ->assertJson([
                'usuario' => 'admin_test',
                'rol' => 'super_admin',
            ]);
    }

    public function test_me_unauthenticated(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_logout(): void
    {
        $token = $this->loginAsAdmin();

        $response = $this->withToken($token)->postJson('/api/logout');
        $response->assertOk()
            ->assertJson(['message' => 'Sesión cerrada.']);

        // Verify token was deleted from DB
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    private function loginAsAdmin(): string
    {
        $response = $this->postJson('/api/login', [
            'usuario' => 'admin_test',
            'password' => 'password123',
            'device_name' => 'PHPUnit',
        ]);

        return $response->json('token');
    }
}
