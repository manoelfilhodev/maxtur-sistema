<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSanctumChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token_and_user_payload(): void
    {
        User::factory()->create([
            'email' => 'mobile@example.com',
            'password' => bcrypt('secret123'),
            'ativo' => 1,
            'cargo' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'ok',
                'token',
                'user' => ['id', 'name', 'email'],
            ])
            ->assertJson([
                'ok' => true,
                'user' => [
                    'email' => 'mobile@example.com',
                ],
            ]);
    }

    public function test_protected_checklist_post_requires_bearer_token(): void
    {
        $this->postJson('/api/v1/checklists', [])
            ->assertStatus(401);
    }

    public function test_protected_checklist_post_accepts_valid_bearer_token(): void
    {
        User::factory()->create([
            'email' => 'mobile@example.com',
            'password' => bcrypt('secret123'),
            'ativo' => 1,
            'cargo' => 'admin',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile@example.com',
            'password' => 'secret123',
        ])->assertOk();

        $token = $login->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/checklists', [])
            ->assertStatus(201);
    }

    public function test_api_logout_revokes_current_token(): void
    {
        User::factory()->create([
            'email' => 'mobile@example.com',
            'password' => bcrypt('secret123'),
            'ativo' => 1,
            'cargo' => 'admin',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile@example.com',
            'password' => 'secret123',
        ])->assertOk();

        $token = $login->json('token');
        $tokenId = (int) explode('|', $token)[0];

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }
}
