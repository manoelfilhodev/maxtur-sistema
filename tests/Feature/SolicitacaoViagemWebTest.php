<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Operador;
use App\Models\Passageiro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolicitacaoViagemWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_create_trip_with_passengers(): void
    {
        $operador = Operador::query()->create([
            'nome' => 'MaxTur',
            'ativo' => true,
        ]);

        $cliente = Cliente::query()->create([
            'operador_id' => $operador->id,
            'razao_social' => 'Cliente Demo',
            'nome_fantasia' => 'Cliente Demo',
            'ativo' => true,
        ]);

        $passageiro = Passageiro::query()->create([
            'operador_id' => $operador->id,
            'cliente_id' => $cliente->id,
            'nome' => 'Passageiro Demo',
            'documento' => 'PASS-001',
            'ativo' => true,
        ]);

        $admin = User::factory()->create([
            'operador_id' => $operador->id,
            'role' => 'admin',
            'ativo' => true,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('painel.operador.solicitacoes.store'), [
                'cliente_id' => $cliente->id,
                'origem' => 'Base Cliente',
                'destino' => 'Aeroporto',
                'data_hora' => now()->addDay()->format('Y-m-d H:i:s'),
                'passageiros_previstos' => 1,
                'passageiro_ids' => [$passageiro->id],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('solicitacoes_viagem', [
            'operador_id' => $operador->id,
            'cliente_id' => $cliente->id,
            'origem' => 'Base Cliente',
            'destino' => 'Aeroporto',
            'status' => 'solicitada',
        ]);

        $this->assertDatabaseHas('solicitacao_passageiros', [
            'operador_id' => $operador->id,
            'passageiro_id' => $passageiro->id,
        ]);
    }
}
