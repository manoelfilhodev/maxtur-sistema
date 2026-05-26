<?php

namespace Database\Seeders;

use App\Models\AtrasoViagem;
use App\Models\Cliente;
use App\Models\OcorrenciaViagem;
use App\Models\Passageiro;
use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Support\ViagemStatus;
use Illuminate\Database\Seeder;

class MaxTurDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('MaxTurDemoSeeder ignorado em produção.');

            return;
        }

        $admin = User::query()->where('email', 'dev@systex.com.br')->first();
        $clientes = Cliente::query()
            ->where('operador_id', 1)
            ->whereIn('nome_fantasia', ['Cliente Alpha', 'Cliente Beta', 'Cliente Gamma'])
            ->get()
            ->keyBy('nome_fantasia');

        $veiculos = Veiculo::query()
            ->where('operador_id', 1)
            ->whereIn('placa', ['ABC1D23', 'QWE7R89', 'XYZ4E56'])
            ->get()
            ->keyBy('placa');

        $motoristas = User::query()
            ->where('operador_id', 1)
            ->whereIn('email', ['motorista1@systex.com', 'motorista2@systex.com', 'motorista3@systex.com'])
            ->get()
            ->keyBy('email');

        $solicitada = $this->solicitacao(
            $clientes->get('Cliente Alpha'),
            'Base Cliente Alpha',
            'Aeroporto Internacional',
            now()->addDay()->setTime(9, 0),
            8,
            ViagemStatus::SOLICITADA
        );

        $programada = $this->solicitacao(
            $clientes->get('Cliente Beta'),
            'Hotel Central',
            'Centro de Convenções',
            now()->addDays(2)->setTime(14, 30),
            18,
            ViagemStatus::PROGRAMADA
        );
        $this->atribuir($programada, $veiculos->get('QWE7R89'), $motoristas->get('motorista2@systex.com'), $admin);

        $atrasada = $this->solicitacao(
            $clientes->get('Cliente Gamma'),
            'Garagem Operacional',
            'Terminal Rodoviário',
            now()->subHour(),
            12,
            ViagemStatus::ATRASADA
        );
        $this->atribuir($atrasada, $veiculos->get('XYZ4E56'), $motoristas->get('motorista3@systex.com'), $admin);

        if ($admin && $atrasada) {
            AtrasoViagem::query()->updateOrCreate(
                [
                    'operador_id' => 1,
                    'solicitacao_id' => $atrasada->id,
                    'minutos_atraso' => 20,
                ],
                [
                    'cliente_id' => $atrasada->cliente_id,
                    'motivo' => 'Trânsito intenso no trajeto de saída',
                    'registrado_por' => $admin->id,
                ]
            );

            OcorrenciaViagem::query()->updateOrCreate(
                [
                    'operador_id' => 1,
                    'solicitacao_id' => $atrasada->id,
                    'tipo' => 'Operacional',
                ],
                [
                    'cliente_id' => $atrasada->cliente_id,
                    'descricao' => 'Cliente informado sobre reprogramação de chegada.',
                    'registrado_por' => $admin->id,
                    'registrado_em' => now(),
                ]
            );
        }

        if ($this->command) {
            $this->command->info('Dados de demonstração MaxTur criados/atualizados.');
        }
    }

    private function solicitacao($cliente, string $origem, string $destino, $dataHora, int $passageiros, string $status): ?SolicitacaoViagem
    {
        if (!$cliente) {
            return null;
        }

        $solicitacao = SolicitacaoViagem::query()->updateOrCreate(
            [
                'operador_id' => 1,
                'cliente_id' => $cliente->id,
                'origem' => $origem,
                'destino' => $destino,
            ],
            [
                'data_hora' => $dataHora,
                'passageiros_previstos' => $passageiros,
                'observacao' => 'Registro de demonstração para apresentação do MVP MaxTur.',
                'status' => $status,
            ]
        );

        $passageirosIds = Passageiro::query()
            ->where('operador_id', 1)
            ->where('cliente_id', $cliente->id)
            ->limit(3)
            ->pluck('id')
            ->all();

        if ($passageirosIds) {
            $solicitacao->passageiros()->syncWithPivotValues($passageirosIds, ['operador_id' => 1]);
        }

        return $solicitacao;
    }

    private function atribuir(?SolicitacaoViagem $solicitacao, ?Veiculo $veiculo, ?User $motorista, ?User $admin): void
    {
        if (!$solicitacao || !$veiculo || !$motorista) {
            return;
        }

        SolicitacaoAtribuicao::query()->updateOrCreate(
            [
                'operador_id' => 1,
                'solicitacao_id' => $solicitacao->id,
                'veiculo_id' => $veiculo->id,
                'motorista_id' => $motorista->id,
            ],
            [
                'atribuido_por' => $admin?->id,
                'atribuido_em' => now(),
            ]
        );
    }
}
