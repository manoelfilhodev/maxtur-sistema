<?php

namespace App\Services;

use App\Models\SolicitacaoAtribuicao;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Support\ViagemStatus;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ViagemOperacionalService
{
    public function atribuir(SolicitacaoViagem $solicitacao, Veiculo $veiculo, User $motorista, User $responsavel): SolicitacaoAtribuicao
    {
        $this->validarAtribuicao($solicitacao, $veiculo, $motorista);

        $atribuicao = SolicitacaoAtribuicao::create([
            'operador_id' => $solicitacao->operador_id,
            'solicitacao_id' => $solicitacao->id,
            'veiculo_id' => $veiculo->id,
            'motorista_id' => $motorista->id,
            'atribuido_por' => $responsavel->id,
            'atribuido_em' => now(),
        ]);

        if (!in_array($solicitacao->status, ViagemStatus::terminal(), true)) {
            $solicitacao->update(['status' => ViagemStatus::CHECKLIST_PENDENTE]);
        }

        return $atribuicao;
    }

    public function validarAtribuicao(SolicitacaoViagem $solicitacao, Veiculo $veiculo, User $motorista): void
    {
        if ((int) $veiculo->operador_id !== (int) $solicitacao->operador_id) {
            throw ValidationException::withMessages(['veiculo_id' => 'Veículo fora do escopo do operador.']);
        }

        if ($veiculo->status_operacional === 'bloqueado') {
            throw ValidationException::withMessages(['veiculo_id' => 'Veículo bloqueado por checklist não pode ser atribuído.']);
        }

        if (!in_array($veiculo->status_operacional, ['liberado', 'disponivel', 'ativo', null], true)) {
            throw ValidationException::withMessages(['veiculo_id' => 'Veículo não está disponível para programação.']);
        }

        if ((int) $veiculo->capacidade_passageiros < (int) $solicitacao->passageiros_previstos) {
            throw ValidationException::withMessages(['veiculo_id' => 'Capacidade do veículo insuficiente para a quantidade prevista de passageiros.']);
        }

        if ((int) $motorista->operador_id !== (int) $solicitacao->operador_id || !$motorista->isMotorista()) {
            throw ValidationException::withMessages(['motorista_id' => 'Motorista fora do escopo do operador.']);
        }

        if (isset($motorista->ativo) && !((bool) $motorista->ativo)) {
            throw ValidationException::withMessages(['motorista_id' => 'Motorista inativo não pode receber viagem.']);
        }

        $inicio = Carbon::parse($solicitacao->data_hora)->subHours(2);
        $fim = Carbon::parse($solicitacao->data_hora)->addHours(2);

        $conflitoVeiculo = SolicitacaoAtribuicao::query()
            ->where('veiculo_id', $veiculo->id)
            ->where('solicitacao_id', '!=', $solicitacao->id)
            ->whereHas('solicitacao', function ($query) use ($inicio, $fim) {
                $query->whereBetween('data_hora', [$inicio, $fim])
                    ->whereNotIn('status', ViagemStatus::terminal());
            })
            ->exists();

        if ($conflitoVeiculo) {
            throw ValidationException::withMessages(['veiculo_id' => 'Veículo já possui programação próxima a esse horário.']);
        }

        $conflitoMotorista = SolicitacaoAtribuicao::query()
            ->where('motorista_id', $motorista->id)
            ->where('solicitacao_id', '!=', $solicitacao->id)
            ->whereHas('solicitacao', function ($query) use ($inicio, $fim) {
                $query->whereBetween('data_hora', [$inicio, $fim])
                    ->whereNotIn('status', ViagemStatus::terminal());
            })
            ->exists();

        if ($conflitoMotorista) {
            throw ValidationException::withMessages(['motorista_id' => 'Motorista já possui programação próxima a esse horário.']);
        }
    }
}
