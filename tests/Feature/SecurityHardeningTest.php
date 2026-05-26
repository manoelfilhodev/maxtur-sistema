<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_checklist_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/checklists')
            ->assertStatus(401);
    }

    public function test_legacy_checklist_show_requires_authentication(): void
    {
        $this->getJson('/api/v1/checklists/1')
            ->assertStatus(401);
    }

    public function test_tenant_context_rejects_user_without_operador(): void
    {
        $user = new User([
            'name' => 'Sem operador',
            'email' => 'sem-operador@example.com',
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        app(TenantContext::class)->operadorId($user);
    }

    public function test_docs_route_is_not_public_in_production_by_default(): void
    {
        $this->app['env'] = 'production';

        $this->get('/docs')
            ->assertStatus(404);
    }
}
