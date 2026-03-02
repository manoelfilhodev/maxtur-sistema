<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Viagem;
use Illuminate\Http\Request;

class FuncionarioTripController extends Controller
{
    public function active(Request $request)
    {
        $user = $request->user();

        $trip = Viagem::query()
            ->with(['veiculo', 'motorista'])
            ->where('operador_id', $user->operador_id)
            ->where('status', 'em_andamento')
            ->orderByDesc('id')
            ->first();

        if (!$trip) {
            return response()->json([
                'ok' => true,
                'message' => 'Nenhuma viagem ativa no momento.',
                'data' => null,
            ], 200);
        }

        // TODO: Integrar com tabelas reais de rota, pontos (stops) e telemetria/posicao.
        $data = [
            'rota' => [
                'id' => (int) $trip->id,
                'nome' => sprintf('Rota %s - %s', $trip->origem, $trip->destino),
            ],
            'motorista' => [
                'nome' => (string) ($trip->motorista->nome ?? 'Motorista nao informado'),
            ],
            'vehicle' => [
                'id' => (int) ($trip->veiculo->id ?? 0),
                'placa' => (string) ($trip->veiculo->placa ?? 'N/A'),
            ],
            'posicaoAtual' => [
                'lat' => -23.45,
                'lng' => -46.58,
            ],
            'horarioSaida' => optional($trip->data_prevista)->format('H:i') ?? '05:30',
            'previsaoChegada' => optional($trip->data_real)->format('H:i') ?? '06:20',
            'stops' => [
                [
                    'id' => 1,
                    'nome' => 'Ponto 1',
                    'lat' => -23.44,
                    'lng' => -46.57,
                    'horarioPrevisto' => '05:40',
                ],
            ],
        ];

        return response()->json([
            'ok' => true,
            'message' => 'ok',
            'data' => $data,
        ], 200);
    }
}

