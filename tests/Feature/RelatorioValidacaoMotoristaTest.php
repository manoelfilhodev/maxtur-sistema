<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Operador;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioValidacaoMotoristaTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_validation_report_calculates_only_finalized_per_trip_records_and_exports(): void
    {
        $operador = Operador::create(['nome' => 'MaxTur', 'ativo' => true]);
        $admin = User::factory()->create(['operador_id' => $operador->id, 'role' => 'admin', 'ativo' => true]);
        $motorista = User::factory()->create([
            'operador_id' => $operador->id, 'name' => 'João Conferência', 'role' => 'MOTORISTA', 'cargo' => 'motorista',
            'ativo' => true, 'tipo_recebimento' => 'por_viagem', 'valor_por_viagem' => 185,
        ]);
        $cliente = Cliente::create(['operador_id' => $operador->id, 'razao_social' => 'Cliente Teste', 'ativo' => true]);
        $veiculo = Veiculo::create(['operador_id' => $operador->id, 'placa' => 'VAL1A23', 'modelo' => 'Van', 'capacidade_passageiros' => 15, 'status_operacional' => 'liberado']);

        foreach (['finalizada', 'cancelada'] as $indice => $status) {
            $viagem = SolicitacaoViagem::create([
                'operador_id' => $operador->id, 'cliente_id' => $cliente->id, 'origem' => 'Base', 'destino' => 'Cliente',
                'data_hora' => '2026-06-'.(25 + $indice).' 08:00:00', 'status' => $status, 'natureza' => 'programada', 'tipo_periodo' => 'diario',
            ]);
            SolicitacaoAtribuicao::create([
                'operador_id' => $operador->id, 'solicitacao_id' => $viagem->id, 'veiculo_id' => $veiculo->id,
                'motorista_id' => $motorista->id, 'atribuido_por' => $admin->id, 'atribuido_em' => $viagem->data_hora,
            ]);
        }

        $filtros = ['data_inicio' => '2026-06-01', 'data_fim' => '2026-06-30', 'motorista_id' => $motorista->id, 'status' => ''];

        $this->actingAs($admin)->get(route('painel.relatorios.motoristas.index', $filtros))
            ->assertOk()->assertSee('João Conferência')->assertSee('R$ 185,00')->assertSee('Regra cadastrada')->assertSee('Cancelada');

        $csv = $this->actingAs($admin)->get(route('painel.relatorios.motoristas.csv', $filtros));
        $csv->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('RELATÓRIO DE VALIDAÇÃO DO MOTORISTA', $csv->streamedContent());

        $pdf = $this->actingAs($admin)->get(route('painel.relatorios.motoristas.pdf', $filtros));
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());

        $pdfConsolidado = $this->actingAs($admin)->get(route('painel.relatorios.motoristas.pdf', [
            'data_inicio' => '2026-06-01', 'data_fim' => '2026-06-30', 'status' => '',
        ]));
        $pdfConsolidado->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfConsolidado->getContent());
    }
}
