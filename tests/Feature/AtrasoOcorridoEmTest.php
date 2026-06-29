<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Operador;
use App\Models\Passageiro;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtrasoOcorridoEmTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_register_retroactive_trip_and_passenger_delays(): void
    {
        $operador = Operador::create(['nome' => 'MaxTur', 'ativo' => true]);
        $cliente = Cliente::create([
            'operador_id' => $operador->id,
            'razao_social' => 'Cliente Teste',
            'nome_fantasia' => 'Cliente Teste',
            'ativo' => true,
        ]);
        $admin = User::factory()->create(['operador_id' => $operador->id, 'role' => 'admin', 'ativo' => true]);
        $passageiro = Passageiro::create([
            'operador_id' => $operador->id,
            'cliente_id' => $cliente->id,
            'nome' => 'Passageiro Teste',
            'ativo' => true,
        ]);
        $solicitacao = SolicitacaoViagem::create([
            'operador_id' => $operador->id,
            'cliente_id' => $cliente->id,
            'origem' => 'Base',
            'destino' => 'Terminal',
            'data_hora' => now(),
            'status' => 'programada',
        ]);

        $this->actingAs($admin)->post(route('painel.operador.atrasos.viagem.store', $solicitacao), [
            'data_ocorrencia' => '2026-06-25',
            'hora_ocorrencia' => '08:30',
            'minutos_atraso' => 20,
            'motivo' => 'Trânsito intenso',
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->post(route('painel.operador.atrasos.passageiro.store', $solicitacao), [
            'passageiro_id' => $passageiro->id,
            'data_ocorrencia' => '2026-06-24',
            'hora_ocorrencia' => '17:45',
            'minutos_atraso' => 10,
            'motivo' => 'Atraso no embarque',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('atrasos_viagem', [
            'solicitacao_id' => $solicitacao->id,
            'ocorrido_em' => '2026-06-25 08:30:00',
        ]);
        $this->assertDatabaseHas('atrasos_passageiro', [
            'passageiro_id' => $passageiro->id,
            'ocorrido_em' => '2026-06-24 17:45:00',
        ]);

        $this->actingAs($admin)->get(route('painel.operador.atrasos.index'))
            ->assertOk()
            ->assertSee('25/06/2026 08:30')
            ->assertSee('24/06/2026 17:45')
            ->assertSee('Registrado em');
    }

    public function test_occurrence_date_and_time_are_required(): void
    {
        $operador = Operador::create(['nome' => 'MaxTur', 'ativo' => true]);
        $cliente = Cliente::create(['operador_id' => $operador->id, 'razao_social' => 'Cliente', 'ativo' => true]);
        $admin = User::factory()->create(['operador_id' => $operador->id, 'role' => 'admin']);
        $solicitacao = SolicitacaoViagem::create([
            'operador_id' => $operador->id,
            'cliente_id' => $cliente->id,
            'origem' => 'A',
            'destino' => 'B',
            'data_hora' => now(),
        ]);

        $this->actingAs($admin)->post(route('painel.operador.ocorrencias.store', $solicitacao), [
            'tipo' => 'Operacional',
            'descricao' => 'Alteração do ponto de embarque',
        ])->assertSessionHasErrors(['data_ocorrencia', 'hora_ocorrencia']);

        $this->actingAs($admin)->post(route('painel.operador.ocorrencias.store', $solicitacao), [
            'tipo' => 'Operacional',
            'descricao' => 'Alteração do ponto de embarque',
            'data_ocorrencia' => '2026-06-20',
            'hora_ocorrencia' => '07:15',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ocorrencias_viagem', [
            'solicitacao_id' => $solicitacao->id,
            'ocorrido_em' => '2026-06-20 07:15:00',
        ]);
    }
}
