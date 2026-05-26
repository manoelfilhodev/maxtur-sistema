<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Operador;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminSolicitacaoAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_vehicle_cannot_be_assigned_to_solicitacao(): void
    {
        [$admin, $solicitacao, $veiculo, $motorista] = $this->makeAssignmentScenario('bloqueado');

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/solicitacoes/{$solicitacao->id}/atribuir", [
            'veiculo_id' => $veiculo->id,
            'motorista_id' => $motorista->id,
        ])
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'message' => 'Veículo bloqueado por checklist não pode ser atribuído.',
            ]);

        $this->assertDatabaseCount('solicitacao_atribuicoes', 0);
    }

    public function test_released_vehicle_can_be_assigned_to_solicitacao(): void
    {
        [$admin, $solicitacao, $veiculo, $motorista] = $this->makeAssignmentScenario('liberado');

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/solicitacoes/{$solicitacao->id}/atribuir", [
            'veiculo_id' => $veiculo->id,
            'motorista_id' => $motorista->id,
        ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'message' => 'Atribuicao registrada. A viagem agora aguarda checklist do motorista.',
            ]);

        $this->assertDatabaseHas('solicitacao_atribuicoes', [
            'solicitacao_id' => $solicitacao->id,
            'veiculo_id' => $veiculo->id,
            'motorista_id' => $motorista->id,
        ]);
        $this->assertDatabaseHas('solicitacoes_viagem', [
            'id' => $solicitacao->id,
            'status' => 'checklist_pendente',
        ]);
    }

    private function makeAssignmentScenario(string $vehicleStatus): array
    {
        $operador = Operador::query()->create([
            'nome' => 'Maxtur',
            'ativo' => true,
        ]);

        $cliente = Cliente::query()->create([
            'operador_id' => $operador->id,
            'razao_social' => 'Cliente Teste',
            'nome_fantasia' => 'Cliente Teste',
            'ativo' => true,
        ]);

        $admin = User::factory()->create([
            'operador_id' => $operador->id,
            'role' => 'admin',
            'ativo' => true,
        ]);

        $motorista = User::factory()->create([
            'operador_id' => $operador->id,
            'role' => 'motorista',
            'ativo' => true,
        ]);

        $veiculo = Veiculo::query()->create([
            'operador_id' => $operador->id,
            'placa' => 'ABC1D23',
            'modelo' => 'Van',
            'capacidade_passageiros' => 15,
            'status_operacional' => $vehicleStatus,
        ]);

        $solicitacao = SolicitacaoViagem::query()->create([
            'operador_id' => $operador->id,
            'cliente_id' => $cliente->id,
            'origem' => 'Base',
            'destino' => 'Cliente',
            'data_hora' => now()->addDay(),
            'passageiros_previstos' => 10,
            'status' => 'aprovada',
        ]);

        return [$admin, $solicitacao, $veiculo, $motorista];
    }
}
