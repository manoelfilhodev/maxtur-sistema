<?php

namespace Tests\Feature;

use App\Models\AtrasoPassageiro;
use App\Models\AtrasoViagem;
use App\Models\Checklist;
use App\Models\Cliente;
use App\Models\OcorrenciaViagem;
use App\Models\Operador;
use App\Models\Passageiro;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioViagensGerencialTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_combines_filters_totals_details_csv_and_pdf_exports(): void
    {
        $operador = Operador::create(['nome' => 'MaxTur', 'ativo' => true]);
        $admin = User::factory()->create(['operador_id' => $operador->id, 'role' => 'admin', 'ativo' => true]);
        $motorista = User::factory()->create(['operador_id' => $operador->id, 'name' => 'Motorista Relatório', 'role' => 'MOTORISTA', 'ativo' => true]);
        $cliente = Cliente::create(['operador_id' => $operador->id, 'razao_social' => 'Cliente Relatório', 'nome_fantasia' => 'Cliente Relatório', 'ativo' => true]);
        $veiculo = Veiculo::create(['operador_id' => $operador->id, 'placa' => 'REL1A23', 'modelo' => 'Van', 'capacidade_passageiros' => 15, 'status_operacional' => 'liberado']);
        $passageiro = Passageiro::create(['operador_id' => $operador->id, 'cliente_id' => $cliente->id, 'nome' => 'Passageiro Relatório', 'ativo' => true]);
        $viagem = SolicitacaoViagem::create([
            'operador_id' => $operador->id, 'cliente_id' => $cliente->id,
            'origem' => 'Garagem', 'destino' => 'Cliente', 'data_hora' => '2026-06-25 08:00:00',
            'status' => 'finalizada', 'natureza' => 'extra', 'tipo_periodo' => 'diario',
        ]);
        SolicitacaoAtribuicao::create([
            'operador_id' => $operador->id, 'solicitacao_id' => $viagem->id,
            'veiculo_id' => $veiculo->id, 'motorista_id' => $motorista->id,
            'atribuido_por' => $admin->id, 'atribuido_em' => '2026-06-25 07:00:00',
        ]);
        Checklist::create(['operador_id' => $operador->id, 'solicitacao_id' => $viagem->id, 'status' => 'finalizado', 'resultado' => 'apto']);
        AtrasoViagem::create([
            'operador_id' => $operador->id, 'cliente_id' => $cliente->id, 'solicitacao_id' => $viagem->id,
            'minutos_atraso' => 15, 'motivo' => 'Trânsito', 'ocorrido_em' => '2026-06-25 08:10:00', 'registrado_por' => $admin->id,
        ]);
        AtrasoPassageiro::create([
            'operador_id' => $operador->id, 'cliente_id' => $cliente->id, 'solicitacao_id' => $viagem->id,
            'passageiro_id' => $passageiro->id, 'minutos_atraso' => 5, 'motivo' => 'Embarque',
            'ocorrido_em' => '2026-06-25 08:05:00', 'registrado_por' => $admin->id,
        ]);
        OcorrenciaViagem::create([
            'operador_id' => $operador->id, 'cliente_id' => $cliente->id, 'solicitacao_id' => $viagem->id,
            'tipo' => 'Operacional', 'descricao' => 'Parada não programada', 'registrado_por' => $admin->id,
            'registrado_em' => '2026-06-25 08:20:00',
        ]);

        $filtros = [
            'data_inicio' => '2026-06-25', 'data_fim' => '2026-06-25',
            'cliente_id' => $cliente->id, 'motorista_id' => $motorista->id, 'veiculo_id' => $veiculo->id,
            'status' => 'finalizada', 'natureza' => 'extra', 'tipo_periodo' => 'diario',
        ];

        $this->actingAs($admin)->get(route('painel.relatorios.index', $filtros))
            ->assertOk()
            ->assertSee('Cliente Relatório')
            ->assertSee('Motorista Relatório')
            ->assertSee('REL1A23')
            ->assertSee('20 min')
            ->assertSee('Parada não programada')
            ->assertSee('25/06/2026 08:10');

        $response = $this->actingAs($admin)->get(route('painel.relatorios.viagens.csv', $filtros));
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('RELATÓRIO GERENCIAL DE VIAGENS', $csv);
        $this->assertStringContainsString('Cliente Relatório', $csv);
        $this->assertStringContainsString('Motorista Relatório', $csv);
        $this->assertStringContainsString(';20;', $csv);

        $pdf = $this->actingAs($admin)->get(route('painel.relatorios.viagens.pdf', $filtros));
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->assertStringContainsString('attachment;', (string) $pdf->headers->get('content-disposition'));
    }
}
