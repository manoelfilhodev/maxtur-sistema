<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Operador;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdministrativeImprovementsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): array
    {
        $operador = Operador::create(['nome' => 'MaxTur', 'ativo' => true]);
        $admin = User::factory()->create(['operador_id' => $operador->id, 'role' => 'admin', 'ativo' => true]);

        return [$operador, $admin];
    }

    public function test_vehicle_can_be_edited_and_receive_maintenance_with_computed_alert(): void
    {
        [$operador, $admin] = $this->admin();
        $veiculo = Veiculo::create([
            'operador_id' => $operador->id, 'placa' => 'ABC1D23', 'modelo' => 'Sprinter',
            'tipo' => 'proprio', 'capacidade_passageiros' => 20, 'status_operacional' => 'liberado', 'km_atual' => 10000,
        ]);

        $this->actingAs($admin)->put(route('master.veiculos.update', $veiculo), [
            'placa' => 'ABC1D23', 'modelo' => 'Sprinter 416', 'tipo' => 'parceiro', 'ano' => 2024,
            'data_documento' => '2026-06-01', 'km_atual' => 10000,
            'capacidade_passageiros' => 20, 'status_operacional' => 'liberado',
        ])->assertRedirect(route('master.veiculos.show', $veiculo));

        $this->actingAs($admin)->post(route('master.veiculos.manutencoes.store', $veiculo), [
            'item' => 'Troca de óleo', 'km_referencia' => 9000, 'km_vencimento' => 10500,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('veiculos', ['id' => $veiculo->id, 'tipo' => 'parceiro', 'ano' => 2024]);
        $this->assertDatabaseHas('veiculo_manutencoes', ['veiculo_id' => $veiculo->id, 'status' => 'proximo_vencimento']);
    }

    public function test_driver_remuneration_and_private_document_are_saved(): void
    {
        Storage::fake('local');
        [, $admin] = $this->admin();

        $this->actingAs($admin)->post(route('master.motoristas.store'), [
            'name' => 'Maria Motorista', 'email' => 'maria@example.com', 'cpf' => '12345678901',
            'telefone' => '11999999999', 'ativo' => 1, 'password' => 'secret123',
            'cnh_vencimento' => '2027-01-10', 'data_admissao' => '2026-01-10',
            'tipo_recebimento' => 'por_viagem', 'valor_por_viagem' => '250.50',
        ])->assertSessionHasNoErrors();

        $motorista = User::where('email', 'maria@example.com')->firstOrFail();
        $this->actingAs($admin)->post(route('master.motoristas.documentos.store', $motorista), [
            'tipo' => 'cnh', 'documento' => UploadedFile::fake()->image('cnh.jpg'),
        ])->assertSessionHasNoErrors();

        $documento = $motorista->documentosMotorista()->firstOrFail();
        Storage::disk('local')->assertExists($documento->caminho);
        $this->assertEquals('250.50', $motorista->fresh()->valor_por_viagem);
    }

    public function test_trip_type_and_nature_can_be_filtered_in_report(): void
    {
        [$operador, $admin] = $this->admin();
        $cliente = Cliente::create(['operador_id' => $operador->id, 'razao_social' => 'Cliente A', 'nome_fantasia' => 'Cliente A', 'ativo' => true]);
        $veiculo = Veiculo::create(['operador_id' => $operador->id, 'placa' => 'XYZ9A99', 'modelo' => 'Van', 'capacidade_passageiros' => 12, 'status_operacional' => 'liberado']);
        $motorista = User::factory()->create(['operador_id' => $operador->id, 'name' => 'João', 'role' => 'MOTORISTA', 'cargo' => 'MOTORISTA', 'ativo' => true]);
        $solicitacao = SolicitacaoViagem::create([
            'operador_id' => $operador->id, 'cliente_id' => $cliente->id,
            'origem' => 'A', 'destino' => 'B', 'data_hora' => now(),
            'status' => 'programada', 'tipo_periodo' => 'esporadico', 'natureza' => 'extra',
        ]);
        SolicitacaoAtribuicao::create([
            'operador_id' => $operador->id, 'solicitacao_id' => $solicitacao->id,
            'veiculo_id' => $veiculo->id, 'motorista_id' => $motorista->id,
            'atribuido_por' => $admin->id, 'atribuido_em' => now(),
        ]);

        $this->actingAs($admin)->get(route('painel.relatorios.index', ['natureza' => 'extra']))
            ->assertOk()->assertSee('Cliente A')->assertSee('Extra');
    }

    public function test_operational_control_center_renders_for_admin(): void
    {
        [, $admin] = $this->admin();

        $this->actingAs($admin)
            ->get(route('painel.dashboard'))
            ->assertOk()
            ->assertSee('Centro de Controle Operacional')
            ->assertSee('Indicadores operacionais')
            ->assertSee('Timeline operacional');
    }
}
