<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthAndPanelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_redirects_to_dashboard(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
            'ativo' => 1,
            'cargo' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/painel');
        $this->assertAuthenticated();
    }

    public function test_panel_requires_admin_role(): void
    {
        $usuarioComum = User::factory()->create([
            'id' => 2,
            'ativo' => 1,
            'cargo' => 'colaborador',
        ]);

        $this->actingAs($usuarioComum)
            ->get('/painel/usuarios')
            ->assertStatus(403);
    }

    public function test_master_user_can_access_panel_routes(): void
    {
        $master = User::factory()->create([
            'id' => 1,
            'ativo' => 1,
            'cargo' => null,
            'nivel' => null,
        ]);

        $this->actingAs($master)
            ->get('/painel/usuarios')
            ->assertStatus(200);
    }
}
