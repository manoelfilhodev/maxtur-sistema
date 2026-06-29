<?php

namespace Tests\Feature;

use App\Models\Checklist;
use App\Models\Cliente;
use App\Models\Operador;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV2IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_inactive_user_and_preserves_tokens_per_device(): void
    {
        $operador = Operador::create(['nome' => 'MaxTur', 'ativo' => true]);
        $admin = User::factory()->create(['operador_id' => $operador->id, 'role' => 'admin', 'ativo' => true]);
        $inactive = User::factory()->create(['operador_id' => $operador->id, 'ativo' => false]);

        $this->postJson('/api/v2/auth/login', ['email' => $inactive->email, 'password' => 'password'])
            ->assertUnauthorized()->assertJson(['ok' => false]);

        foreach (['Android pessoal', 'Tablet veículo'] as $device) {
            $this->postJson('/api/v2/auth/login', [
                'email' => $admin->email, 'password' => 'password', 'device_name' => $device,
            ])->assertOk()->assertJsonStructure(['data' => ['token', 'expires_at', 'user' => ['role']]]);
        }

        $this->assertCount(2, $admin->fresh()->tokens);
    }

    public function test_only_currently_assigned_driver_can_list_and_open_trip(): void
    {
        [$admin, $cliente, $veiculo, $viagem] = $this->baseScenario();
        $antigo = User::factory()->create(['operador_id' => $admin->operador_id, 'role' => 'MOTORISTA', 'cargo' => 'motorista', 'ativo' => true]);
        $atual = User::factory()->create(['operador_id' => $admin->operador_id, 'role' => 'MOTORISTA', 'cargo' => 'motorista', 'ativo' => true]);
        $this->assign($viagem, $veiculo, $antigo, $admin);
        $this->assign($viagem, $veiculo, $atual, $admin);

        Sanctum::actingAs($antigo);
        $this->getJson('/api/v2/motorista/viagens')->assertOk()->assertJsonPath('data.total', 0);
        $this->getJson("/api/v2/motorista/viagens/{$viagem->id}")->assertNotFound();

        Sanctum::actingAs($atual);
        $this->getJson('/api/v2/motorista/viagens')->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson("/api/v2/motorista/viagens/{$viagem->id}")->assertOk()->assertJsonPath('data.id', $viagem->id);
    }

    public function test_v2_writes_require_idempotency_key_and_replay_without_duplicates(): void
    {
        [$admin, $cliente, $veiculo, $viagem] = $this->baseScenario();
        $motorista = User::factory()->create(['operador_id' => $admin->operador_id, 'role' => 'MOTORISTA', 'cargo' => 'motorista', 'ativo' => true]);
        $this->assign($viagem, $veiculo, $motorista, $admin);
        Sanctum::actingAs($motorista);
        $payload = ['minutos_atraso' => 12, 'motivo' => 'Trânsito', 'ocorrido_em' => '2026-06-29 08:10:00'];

        $this->postJson("/api/v2/motorista/viagens/{$viagem->id}/atraso", $payload)
            ->assertUnprocessable()->assertJsonPath('ok', false);

        $first = $this->withHeader('Idempotency-Key', 'delay-mobile-001')
            ->postJson("/api/v2/motorista/viagens/{$viagem->id}/atraso", $payload);
        $first->assertCreated()->assertHeader('Idempotency-Replayed', 'false');

        $this->withHeader('Idempotency-Key', 'delay-mobile-001')
            ->postJson("/api/v2/motorista/viagens/{$viagem->id}/atraso", $payload)
            ->assertCreated()->assertHeader('Idempotency-Replayed', 'true');

        $this->assertDatabaseCount('atrasos_viagem', 1);
    }

    public function test_checklist_is_role_and_tenant_scoped_in_current_and_legacy_api(): void
    {
        [$admin] = $this->baseScenario();
        $clienteUser = User::factory()->create(['operador_id' => $admin->operador_id, 'role' => 'CLIENTE', 'cargo' => 'cliente', 'ativo' => true]);
        Sanctum::actingAs($clienteUser);
        $this->getJson('/api/v2/checklists/itens')->assertForbidden()->assertJsonPath('ok', false);

        $outroOperador = Operador::create(['nome' => 'Outro', 'ativo' => true]);
        $outroAdmin = User::factory()->create(['operador_id' => $outroOperador->id, 'role' => 'admin', 'ativo' => true]);
        $checklist = Checklist::create(['operador_id' => $outroOperador->id, 'status' => 'em_andamento', 'created_by' => $outroAdmin->id]);

        Sanctum::actingAs($admin);
        $this->getJson("/api/v1/checklists/{$checklist->id}")->assertNotFound();
        $this->postJson("/api/v1/checklists/{$checklist->id}/finalizar")->assertNotFound();
    }

    public function test_driver_payment_statement_and_pdf_use_only_finalized_current_assignments(): void
    {
        [$admin, $cliente, $veiculo, $viagem] = $this->baseScenario('finalizada');
        $motorista = User::factory()->create([
            'operador_id' => $admin->operador_id, 'role' => 'MOTORISTA', 'cargo' => 'motorista', 'ativo' => true,
            'tipo_recebimento' => 'por_viagem', 'valor_por_viagem' => 185,
        ]);
        $this->assign($viagem, $veiculo, $motorista, $admin);
        Sanctum::actingAs($motorista);
        $params = ['data_inicio' => '2026-06-01', 'data_fim' => '2026-06-30'];

        $this->getJson('/api/v2/motorista/pagamentos/extrato?'.http_build_query($params))
            ->assertOk()
            ->assertJsonPath('data.resumo.finalizadas', 1)
            ->assertJsonPath('data.resumo.valor_calculado', 185)
            ->assertJsonPath('data.viagens.data.0.elegivel_pagamento', true);

        $pdf = $this->get('/api/v2/motorista/pagamentos/extrato.pdf?'.http_build_query($params), ['Accept' => 'application/pdf']);
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
    }

    private function baseScenario(string $status = 'pronta_para_execucao'): array
    {
        $operador = Operador::create(['nome' => 'MaxTur', 'ativo' => true]);
        $admin = User::factory()->create(['operador_id' => $operador->id, 'role' => 'admin', 'ativo' => true]);
        $cliente = Cliente::create(['operador_id' => $operador->id, 'razao_social' => 'Cliente API', 'ativo' => true]);
        $veiculo = Veiculo::create(['operador_id' => $operador->id, 'placa' => 'API2A22', 'modelo' => 'Van', 'capacidade_passageiros' => 20, 'status_operacional' => 'liberado']);
        $viagem = SolicitacaoViagem::create([
            'operador_id' => $operador->id, 'cliente_id' => $cliente->id, 'origem' => 'Base', 'destino' => 'Cliente',
            'data_hora' => '2026-06-29 08:00:00', 'status' => $status, 'natureza' => 'programada', 'tipo_periodo' => 'diario',
        ]);

        return [$admin, $cliente, $veiculo, $viagem];
    }

    private function assign(SolicitacaoViagem $viagem, Veiculo $veiculo, User $motorista, User $admin): void
    {
        SolicitacaoAtribuicao::create([
            'operador_id' => $viagem->operador_id, 'solicitacao_id' => $viagem->id,
            'veiculo_id' => $veiculo->id, 'motorista_id' => $motorista->id,
            'atribuido_por' => $admin->id, 'atribuido_em' => now(),
        ]);
    }
}
