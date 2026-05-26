<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AtrasoViagem;
use App\Models\OcorrenciaViagem;
use App\Models\SolicitacaoViagem;
use App\Services\TenantContext;
use App\Support\ViagemStatus;
use Illuminate\Http\Request;

class MotoristaViagemController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $viagens = SolicitacaoViagem::query()
            ->with(['cliente:id,nome_fantasia,razao_social', 'atribuicoes.veiculo:id,placa,modelo'])
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->whereHas('atribuicoes', fn ($query) => $query->where('motorista_id', $user->id))
            ->whereNotIn('status', [ViagemStatus::CANCELADA])
            ->orderBy('data_hora')
            ->paginate(20);

        return response()->json([
            'ok' => true,
            'message' => 'Viagens do motorista listadas',
            'data' => $viagens,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $viagem = $this->findViagem($request, $id);
        if (!$viagem) {
            return $this->notFound();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Detalhes da viagem',
            'data' => $viagem->load([
                'cliente:id,nome_fantasia,razao_social',
                'passageiros:id,nome',
                'atribuicoes.veiculo:id,placa,modelo',
                'checklists.respostas',
                'atrasosViagem',
                'ocorrencias',
            ]),
        ]);
    }

    public function iniciar(Request $request, int $id)
    {
        $viagem = $this->findViagem($request, $id);
        if (!$viagem) {
            return $this->notFound();
        }

        if ($viagem->status !== ViagemStatus::PRONTA_PARA_EXECUCAO) {
            return response()->json([
                'ok' => false,
                'message' => 'A viagem só pode ser iniciada após checklist aprovado.',
                'data' => ['status_atual' => $viagem->status],
            ], 422);
        }

        $viagem->update(['status' => ViagemStatus::EM_ANDAMENTO]);

        return response()->json([
            'ok' => true,
            'message' => 'Viagem iniciada com sucesso',
            'data' => $viagem->fresh(),
        ]);
    }

    public function finalizar(Request $request, int $id)
    {
        $viagem = $this->findViagem($request, $id);
        if (!$viagem) {
            return $this->notFound();
        }

        if (!in_array($viagem->status, [ViagemStatus::EM_ANDAMENTO, ViagemStatus::ATRASADA], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'A viagem precisa estar em andamento ou atrasada para ser finalizada.',
                'data' => ['status_atual' => $viagem->status],
            ], 422);
        }

        $viagem->update(['status' => ViagemStatus::FINALIZADA]);

        return response()->json([
            'ok' => true,
            'message' => 'Viagem finalizada com sucesso',
            'data' => $viagem->fresh(),
        ]);
    }

    public function atraso(Request $request, int $id)
    {
        $data = $request->validate([
            'minutos_atraso' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string'],
        ]);

        $viagem = $this->findViagem($request, $id);
        if (!$viagem) {
            return $this->notFound();
        }

        $atraso = AtrasoViagem::create([
            'operador_id' => $viagem->operador_id,
            'cliente_id' => $viagem->cliente_id,
            'solicitacao_id' => $viagem->id,
            'minutos_atraso' => $data['minutos_atraso'],
            'motivo' => $data['motivo'] ?? null,
            'registrado_por' => $request->user()->id,
        ]);

        if (!in_array($viagem->status, ViagemStatus::terminal(), true)) {
            $viagem->update(['status' => ViagemStatus::ATRASADA]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Atraso registrado com sucesso',
            'data' => $atraso,
        ], 201);
    }

    public function ocorrencia(Request $request, int $id)
    {
        $data = $request->validate([
            'tipo' => ['required', 'string', 'max:80'],
            'descricao' => ['required', 'string'],
            'evidencia_path' => ['nullable', 'string', 'max:255'],
        ]);

        $viagem = $this->findViagem($request, $id);
        if (!$viagem) {
            return $this->notFound();
        }

        $ocorrencia = OcorrenciaViagem::create([
            'operador_id' => $viagem->operador_id,
            'cliente_id' => $viagem->cliente_id,
            'solicitacao_id' => $viagem->id,
            'tipo' => $data['tipo'],
            'descricao' => $data['descricao'],
            'evidencia_path' => $data['evidencia_path'] ?? null,
            'registrado_por' => $request->user()->id,
            'registrado_em' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Ocorrência registrada com sucesso',
            'data' => $ocorrencia,
        ], 201);
    }

    private function findViagem(Request $request, int $id): ?SolicitacaoViagem
    {
        $user = $request->user();

        return SolicitacaoViagem::query()
            ->whereKey($id)
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->whereHas('atribuicoes', fn ($query) => $query->where('motorista_id', $user->id))
            ->first();
    }

    private function notFound()
    {
        return response()->json([
            'ok' => false,
            'message' => 'Viagem não encontrada para este motorista.',
            'data' => null,
        ], 404);
    }
}
