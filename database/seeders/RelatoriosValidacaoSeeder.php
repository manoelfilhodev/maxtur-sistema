<?php

namespace Database\Seeders;

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
use App\Support\ViagemStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RelatoriosValidacaoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('RelatoriosValidacaoSeeder ignorado em produção.');

            return;
        }

        $operador = Operador::query()->firstOrFail();
        $admin = User::query()->where('operador_id', $operador->id)->where(function ($query) {
            $query->whereIn('role', ['MASTER', 'ADMIN', 'admin', 'OPERADOR', 'operador'])->orWhere('id', 1);
        })->firstOrFail();

        DB::transaction(function () use ($operador, $admin) {
            $clientes = collect([
                ['nome' => 'Indústria Horizonte Demo', 'cnpj' => '99100001000101', 'email' => 'operacao@horizonte-demo.test'],
                ['nome' => 'Centro Logístico Aurora Demo', 'cnpj' => '99100002000102', 'email' => 'fretamento@aurora-demo.test'],
            ])->map(fn (array $dados) => Cliente::query()->updateOrCreate(
                ['operador_id' => $operador->id, 'cnpj' => $dados['cnpj']],
                ['razao_social' => $dados['nome'].' Ltda.', 'nome_fantasia' => $dados['nome'], 'documento' => $dados['cnpj'], 'email' => $dados['email'], 'telefone' => '(11) 4000-9000', 'cidade' => 'São Paulo', 'uf' => 'SP', 'observacoes' => 'DADO FICTÍCIO — homologação de relatórios', 'ativo' => true]
            ));

            $motoristas = collect([
                ['name' => 'João Validação Demo', 'email' => 'joao.validacao@demo.maxtur.test', 'cpf' => '99100000101', 'tipo_recebimento' => 'por_viagem', 'valor_por_viagem' => 185.00, 'valor_salario' => null],
                ['name' => 'Ana Conferência Demo', 'email' => 'ana.conferencia@demo.maxtur.test', 'cpf' => '99100000202', 'tipo_recebimento' => 'por_viagem', 'valor_por_viagem' => 210.00, 'valor_salario' => null],
                ['name' => 'Carlos Mensalista Demo', 'email' => 'carlos.mensalista@demo.maxtur.test', 'cpf' => '99100000303', 'tipo_recebimento' => 'salario', 'valor_por_viagem' => null, 'valor_salario' => 4200.00],
            ])->map(fn (array $dados) => User::query()->updateOrCreate(
                ['email' => $dados['email']],
                [...$dados, 'operador_id' => $operador->id, 'cargo' => 'motorista', 'role' => 'MOTORISTA', 'telefone' => '(11) 99999-0000', 'ativo' => true, 'password' => Hash::make('Demo@123456'), 'email_verified_at' => now(), 'data_admissao' => today()->subYear(), 'cnh_vencimento' => today()->addYear()]
            ));

            $veiculos = collect([
                ['placa' => 'DEM1A01', 'modelo' => 'Mercedes-Benz Sprinter Demo', 'tipo' => 'Van', 'capacidade' => 16],
                ['placa' => 'DEM2B02', 'modelo' => 'Marcopolo Senior Demo', 'tipo' => 'Micro-ônibus', 'capacidade' => 28],
                ['placa' => 'DEM3C03', 'modelo' => 'Volksbus 17.230 Demo', 'tipo' => 'Ônibus', 'capacidade' => 44],
            ])->map(fn (array $dados) => Veiculo::query()->updateOrCreate(
                ['placa' => $dados['placa']],
                ['operador_id' => $operador->id, 'modelo' => $dados['modelo'], 'tipo' => $dados['tipo'], 'ano' => 2024, 'km_atual' => 48000, 'capacidade_passageiros' => $dados['capacidade'], 'status_operacional' => 'liberado']
            ));

            $passageiros = $clientes->map(fn (Cliente $cliente, int $indice) => Passageiro::query()->updateOrCreate(
                ['operador_id' => $operador->id, 'cliente_id' => $cliente->id, 'documento' => 'DEMO-PASS-'.($indice + 1)],
                ['nome' => 'Passageiro Homologação '.($indice + 1), 'telefone' => '(11) 98888-000'.($indice + 1), 'ativo' => true]
            ));

            $rotas = [
                ['Portaria Norte', 'Unidade Industrial'], ['Estação Central', 'Centro Logístico'],
                ['Terminal Municipal', 'Fábrica Principal'], ['Bairro Primavera', 'Unidade Administrativa'],
                ['Garagem MaxTur', 'Aeroporto de Congonhas'], ['Unidade Industrial', 'Estação Central'],
            ];

            for ($i = 1; $i <= 30; $i++) {
                $cliente = $clientes[($i - 1) % $clientes->count()];
                $motorista = $motoristas[($i - 1) % $motoristas->count()];
                $veiculo = $veiculos[($i - 1) % $veiculos->count()];
                [$origem, $destino] = $rotas[($i - 1) % count($rotas)];
                $dataHora = Carbon::today()->startOfMonth()->subDays(6)->addDays($i - 1)->setTime(6 + ($i % 4) * 3, ($i % 2) * 30);
                $status = $i % 11 === 0 ? ViagemStatus::CANCELADA : ($i > 26 ? ViagemStatus::PROGRAMADA : ViagemStatus::FINALIZADA);
                $codigo = sprintf('DEMO-REL-%03d', $i);

                $viagem = SolicitacaoViagem::query()->updateOrCreate(
                    ['operador_id' => $operador->id, 'observacao' => $codigo],
                    ['cliente_id' => $cliente->id, 'origem' => $origem, 'destino' => $destino, 'data_hora' => $dataHora, 'passageiros_previstos' => 8 + ($i % 18), 'status' => $status, 'natureza' => $i % 4 === 0 ? 'extra' : 'programada', 'tipo_periodo' => ['diario', 'mensal', 'esporadico'][($i - 1) % 3]]
                );
                $viagem->passageiros()->syncWithPivotValues([$passageiros[($i - 1) % $passageiros->count()]->id], ['operador_id' => $operador->id]);

                SolicitacaoAtribuicao::query()->updateOrCreate(
                    ['operador_id' => $operador->id, 'solicitacao_id' => $viagem->id],
                    ['veiculo_id' => $veiculo->id, 'motorista_id' => $motorista->id, 'atribuido_por' => $admin->id, 'atribuido_em' => $dataHora->copy()->subDay()]
                );

                Checklist::query()->updateOrCreate(
                    ['operador_id' => $operador->id, 'solicitacao_id' => $viagem->id],
                    ['veiculo_id' => $veiculo->id, 'motorista_id' => $motorista->id, 'veiculo_identificacao' => $veiculo->placa, 'modelo_veiculo' => $veiculo->modelo, 'placa' => $veiculo->placa, 'data' => $dataHora->toDateString(), 'motorista_nome' => $motorista->name, 'status' => $status === ViagemStatus::FINALIZADA ? 'finalizado' : 'pendente', 'resultado' => $status === ViagemStatus::FINALIZADA ? 'apto' : null, 'started_at' => $dataHora->copy()->subMinutes(35), 'finished_at' => $status === ViagemStatus::FINALIZADA ? $dataHora->copy()->subMinutes(15) : null, 'created_by' => $admin->id]
                );

                if ($status === ViagemStatus::FINALIZADA && $i % 4 === 0) {
                    $atraso = AtrasoViagem::query()->updateOrCreate(
                        ['operador_id' => $operador->id, 'solicitacao_id' => $viagem->id],
                        ['cliente_id' => $cliente->id, 'minutos_atraso' => 8 + $i, 'motivo' => 'Demonstração: trânsito intenso no acesso ao embarque.', 'ocorrido_em' => $dataHora->copy()->addMinutes(5), 'registrado_por' => $admin->id]
                    );
                    $this->ajustarCriacao($atraso, $dataHora->copy()->addHours(3));
                }

                if ($status === ViagemStatus::FINALIZADA && $i % 6 === 0) {
                    $atraso = AtrasoPassageiro::query()->updateOrCreate(
                        ['operador_id' => $operador->id, 'solicitacao_id' => $viagem->id],
                        ['cliente_id' => $cliente->id, 'passageiro_id' => $passageiros[($i - 1) % $passageiros->count()]->id, 'minutos_atraso' => 5 + ($i % 10), 'motivo' => 'Demonstração: passageiro chegou após o horário combinado.', 'ocorrido_em' => $dataHora->copy(), 'registrado_por' => $admin->id]
                    );
                    $this->ajustarCriacao($atraso, $dataHora->copy()->addHours(2));
                }

                if ($status === ViagemStatus::FINALIZADA && $i % 5 === 0) {
                    $ocorrencia = OcorrenciaViagem::query()->updateOrCreate(
                        ['operador_id' => $operador->id, 'solicitacao_id' => $viagem->id],
                        ['cliente_id' => $cliente->id, 'tipo' => 'Homologação', 'descricao' => 'Demonstração: alteração de ponto de desembarque confirmada pelo cliente.', 'ocorrido_em' => $dataHora->copy()->addMinutes(35), 'registrado_por' => $admin->id, 'registrado_em' => $dataHora->copy()->addMinutes(40)]
                    );
                    $this->ajustarCriacao($ocorrencia, $dataHora->copy()->addHours(1));
                }
            }
        });

        $this->command?->info('Massa fictícia de relatórios criada: 2 clientes, 3 motoristas, 3 veículos e 30 viagens.');
        $this->command?->line('Motoristas de teste: João Validação Demo, Ana Conferência Demo e Carlos Mensalista Demo.');
    }

    private function ajustarCriacao($model, Carbon $criadoEm): void
    {
        $model->timestamps = false;
        $model->forceFill(['created_at' => $criadoEm, 'updated_at' => $criadoEm])->save();
        $model->timestamps = true;
    }
}
