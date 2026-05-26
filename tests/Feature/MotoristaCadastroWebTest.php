<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Operador;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotoristaCadastroWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_motorista_created_in_module_appears_for_trip_assignment(): void
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

        $admin = User::factory()->create([
            'operador_id' => $operador->id,
            'role' => 'admin',
            'ativo' => true,
        ]);

        Veiculo::query()->create([
            'operador_id' => $operador->id,
            'placa' => 'DEM1O23',
            'modelo' => 'Sprinter',
            'capacidade_passageiros' => 18,
            'status_operacional' => 'liberado',
        ]);

        $solicitacao = SolicitacaoViagem::query()->create([
            'operador_id' => $operador->id,
            'cliente_id' => $cliente->id,
            'origem' => 'Base',
            'destino' => 'Aeroporto',
            'data_hora' => now()->addDay(),
            'passageiros_previstos' => 10,
            'status' => 'aprovada',
        ]);

        $this->actingAs($admin)
            ->post(route('master.motoristas.store'), [
                'name' => 'Motorista Demo',
                'email' => 'motorista.demo@systex.com',
                'cpf' => 'CNH-DEMO-01',
                'telefone' => '(11) 90000-9090',
                'ativo' => '1',
                'password' => 'secret123',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'motorista.demo@systex.com',
            'role' => 'MOTORISTA',
            'cargo' => 'MOTORISTA',
            'ativo' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('painel.operador.solicitacoes.show', $solicitacao->id))
            ->assertOk()
            ->assertSee('Motorista Demo');
    }
}
