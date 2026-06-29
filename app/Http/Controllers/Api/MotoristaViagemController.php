<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AtrasoViagem;
use App\Models\OcorrenciaViagem;
use App\Models\SolicitacaoViagem;
use App\Services\ImageBase64Service;
use App\Services\TenantContext;
use App\Support\ViagemStatus;
use Illuminate\Http\Request;

class MotoristaViagemController extends Controller
{
    public function __construct(
        private TenantContext $tenantContext,
        private ImageBase64Service $imageBase64Service
    ) {}

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:'.implode(',', ViagemStatus::all())],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $user = $request->user();

        $query = SolicitacaoViagem::query()
            ->with(['cliente:id,nome_fantasia,razao_social', 'ultimaAtribuicao.veiculo:id,placa,modelo'])
            ->where('operador_id', $this->tenantContext->operadorId($user))
            ->whereHas('ultimaAtribuicao', fn ($query) => $query->where('motorista_id', $user->id))
            ->whereNotIn('status', [ViagemStatus::CANCELADA]);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_hora', '>=', $request->date('data_inicio'));
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_hora', '<=', $request->date('data_fim'));
        }
        $viagens = $query->orderBy('data_hora')->paginate($request->integer('per_page', 20));

        return response()->json([
            'ok' => true,
            'message' => 'Viagens do motorista listadas',
            'data' => $viagens,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $viagem = $this->findViagem($request, $id);
        if (! $viagem) {
            return $this->notFound();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Detalhes da viagem',
            'data' => $viagem->load([
                'cliente:id,nome_fantasia,razao_social',
                'passageiros:id,nome',
                'ultimaAtribuicao.veiculo:id,placa,modelo',
                'checklists.respostas',
                'atrasosViagem',
                'ocorrencias',
            ]),
        ]);
    }

    public function iniciar(Request $request, int $id)
    {
        $viagem = $this->findViagem($request, $id);
        if (! $viagem) {
            return $this->notFound();
        }

        if ($viagem->status !== ViagemStatus::PRONTA_PARA_EXECUCAO) {
            return response()->json([
                'ok' => false,
                'message' => 'A viagem só pode ser iniciada após checklist aprovado.',
                'data' => ['status_atual' => $viagem->status],
            ], 422);
        }

        $viagem->update(['status' => ViagemStatus::EM_ANDAMENTO, 'iniciada_em' => now()]);

        return response()->json([
            'ok' => true,
            'message' => 'Viagem iniciada com sucesso',
            'data' => $viagem->fresh(),
        ]);
    }

    public function finalizar(Request $request, int $id)
    {
        $viagem = $this->findViagem($request, $id);
        if (! $viagem) {
            return $this->notFound();
        }

        if (! in_array($viagem->status, [ViagemStatus::EM_ANDAMENTO, ViagemStatus::ATRASADA], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'A viagem precisa estar em andamento ou atrasada para ser finalizada.',
                'data' => ['status_atual' => $viagem->status],
            ], 422);
        }

        $viagem->update(['status' => ViagemStatus::FINALIZADA, 'finalizada_em' => now()]);

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
            'ocorrido_em' => ['required', 'date'],
        ]);

        $viagem = $this->findViagem($request, $id);
        if (! $viagem) {
            return $this->notFound();
        }

        $atraso = AtrasoViagem::create([
            'operador_id' => $viagem->operador_id,
            'cliente_id' => $viagem->cliente_id,
            'solicitacao_id' => $viagem->id,
            'minutos_atraso' => $data['minutos_atraso'],
            'motivo' => $data['motivo'] ?? null,
            'ocorrido_em' => $data['ocorrido_em'],
            'registrado_por' => $request->user()->id,
        ]);

        if (! in_array($viagem->status, ViagemStatus::terminal(), true)) {
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
            'ocorrido_em' => ['required', 'date'],
            'evidencia_base64' => ['nullable', 'string'],
        ]);

        $viagem = $this->findViagem($request, $id);
        if (! $viagem) {
            return $this->notFound();
        }

        $evidenciaPath = null;
        if (! empty($data['evidencia_base64'])) {
            try {
                $evidenciaPath = $this->imageBase64Service->savePublicImage(
                    $data['evidencia_base64'],
                    'ocorrencias/'.$viagem->id
                );
            } catch (\InvalidArgumentException $exception) {
                return response()->json(['ok' => false, 'message' => $exception->getMessage(), 'data' => null], 422);
            }
        }

        $ocorrencia = OcorrenciaViagem::create([
            'operador_id' => $viagem->operador_id,
            'cliente_id' => $viagem->cliente_id,
            'solicitacao_id' => $viagem->id,
            'tipo' => $data['tipo'],
            'descricao' => $data['descricao'],
            'ocorrido_em' => $data['ocorrido_em'],
            'evidencia_path' => $evidenciaPath,
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
            ->whereHas('ultimaAtribuicao', fn ($query) => $query->where('motorista_id', $user->id))
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
