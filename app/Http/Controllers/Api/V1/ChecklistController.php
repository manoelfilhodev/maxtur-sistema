<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistResposta;
use App\Services\Checklist\ChecklistStatusService;
use App\Services\ChecklistWorkflowService;
use App\Services\ImageBase64Service;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChecklistController extends Controller
{
    public function __construct(
        private ChecklistStatusService $statusService,
        private TenantContext $tenantContext,
        private ImageBase64Service $imageBase64Service,
        private ChecklistWorkflowService $workflow
    ) {}

    public function index(Request $request)
    {
        $operadorId = $this->tenantContext->operadorId($request->user());

        $checklists = Checklist::query()
            ->where('operador_id', $operadorId)
            ->when($request->user()->isMotorista(), fn ($query) => $query->where('motorista_id', $request->user()->id))
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $checklists,
        ]);
    }

    public function show(Request $request, Checklist $checklist)
    {
        $operadorId = $this->tenantContext->operadorId($request->user());

        if (! $this->canAccess($request, $checklist, $operadorId)) {
            abort(404);
        }

        $itens = ChecklistItem::query()
            ->where('ativo', 1)
            ->orderBy('codigo')
            ->get();

        $respostas = ChecklistResposta::query()
            ->where('checklist_id', $checklist->id)
            ->get()
            ->keyBy('checklist_item_id');

        $itensComResposta = $itens->map(function (ChecklistItem $item) use ($respostas) {
            $resposta = $respostas->get($item->id);

            return [
                'id' => $item->id,
                'codigo' => $item->codigo,
                'titulo' => $item->titulo,
                'como_verificar' => $item->como_verificar,
                'ativo' => (int) $item->ativo,
                'resposta' => $resposta ? [
                    'status' => $resposta->status,
                    'observacao' => $resposta->observacao,
                    'foto_path' => $resposta->foto_path,
                    'updated_at' => $resposta->updated_at,
                ] : null,
            ];
        });

        return response()->json([
            'ok' => true,
            'data' => [
                'checklist' => $checklist,
                'itens' => $itensComResposta,
            ],
        ]);
    }

    public function storeRespostas(Request $request, Checklist $checklist)
    {
        if (! $this->canAccess($request, $checklist)) {
            abort(404);
        }

        $validated = $request->validate([
            'respostas' => ['required', 'array', 'min:1'],
            'respostas.*.codigo' => ['required', 'integer', 'min:1'],
            'respostas.*.status' => ['required', 'in:ok,falha'],
            'respostas.*.observacao' => ['nullable', 'string', 'max:2000'],
            'respostas.*.foto_base64' => ['nullable', 'string'],
        ]);

        $respostas = collect($validated['respostas']);

        $falhasSemEvidencia = $respostas->filter(function ($r) {
            if (($r['status'] ?? null) !== 'falha') {
                return false;
            }

            $obs = trim((string) ($r['observacao'] ?? ''));
            $foto = trim((string) ($r['foto_base64'] ?? ''));

            return $obs === '' && $foto === '';
        })->values();

        if ($falhasSemEvidencia->isNotEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Itens com status "falha" precisam de observacao ou foto.',
                'falhas_sem_evidencia' => $falhasSemEvidencia,
            ], 422);
        }

        $codigos = $respostas->pluck('codigo')->unique()->values();

        $itens = ChecklistItem::query()
            ->whereIn('codigo', $codigos)
            ->get()
            ->keyBy('codigo');

        $invalidos = $codigos->filter(fn ($c) => ! $itens->has($c))->values();

        if ($invalidos->isNotEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Existem codigos invalidos (nao encontrados em checklist_itens).',
                'codigos_invalidos' => $invalidos,
            ], 422);
        }

        $salvos = [];

        DB::beginTransaction();
        try {
            foreach ($respostas as $r) {
                /** @var ChecklistItem $item */
                $item = $itens[$r['codigo']];

                $fotoPath = null;
                if (! empty($r['foto_base64'])) {
                    $fotoPath = $this->imageBase64Service->savePublicImage(
                        $r['foto_base64'],
                        'checklists/'.$checklist->id.'/itens/'.$item->id
                    );
                }

                $resp = ChecklistResposta::query()->updateOrCreate(
                    [
                        'checklist_id' => $checklist->id,
                        'checklist_item_id' => $item->id,
                    ],
                    [
                        'status' => $r['status'],
                        'observacao' => $r['observacao'] ?? null,
                        'foto_path' => $fotoPath,
                    ]
                );

                $salvos[] = [
                    'id' => $resp->id,
                    'codigo' => $r['codigo'],
                    'checklist_item_id' => $item->id,
                    'status' => $resp->status,
                    'foto_path' => $resp->foto_path,
                ];
            }

            $checklist = $this->statusService->recompute($checklist->id);

            $totalItens = ChecklistItem::query()->where('ativo', 1)->count();
            $respondidos = ChecklistResposta::query()->where('checklist_id', $checklist->id)->count();
            $falhas = ChecklistResposta::query()
                ->where('checklist_id', $checklist->id)
                ->where('status', 'falha')
                ->count();

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Respostas salvas.',
                'data' => [
                    'checklist_id' => $checklist->id,
                    'status' => $checklist->fresh()->status,
                    'total_itens' => $totalItens,
                    'respondidos' => $respondidos,
                    'falhas' => $falhas,
                    'salvos' => $salvos,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'Erro ao salvar respostas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function finalizar(Request $request, Checklist $checklist)
    {
        if (! $this->canAccess($request, $checklist)) {
            abort(404);
        }

        try {
            $checklist = $this->workflow->finalizar($request->user(), $checklist);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['ok' => false, 'message' => $exception->getMessage(), 'data' => null], 422);
        }

        return response()->json(['ok' => true, 'message' => 'Checklist finalizado.', 'data' => $checklist]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'veiculo_identificacao' => ['nullable', 'string', 'max:50'],
            'data' => ['nullable', 'date'],
            'motorista_nome' => ['nullable', 'string', 'max:120'],
            'empresa_fornecedora' => ['nullable', 'string', 'max:120'],
            'inspecionado_por' => ['nullable', 'string', 'max:120'],
            'responsavel_nome' => ['nullable', 'string', 'max:120'],
            'responsavel_funcao' => ['nullable', 'string', 'max:120'],
            'comentarios_motorista' => ['nullable', 'string'],
            'status' => ['nullable', 'in:pendente,aprovado,reprovado'],
            'veiculo_id' => ['nullable', 'integer', 'exists:veiculos,id'],
            'motorista_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $checklist = Checklist::create([
            'operador_id' => $request->user()->operador_id,
            'veiculo_id' => $data['veiculo_id'] ?? null,
            'motorista_id' => $request->user()->isMotorista() ? $request->user()->id : ($data['motorista_id'] ?? null),
            'veiculo_identificacao' => $data['veiculo_identificacao'] ?? null,
            'data' => $data['data'] ?? now()->toDateString(),
            'motorista_nome' => $data['motorista_nome'] ?? null,
            'empresa_fornecedora' => $data['empresa_fornecedora'] ?? null,
            'inspecionado_por' => $data['inspecionado_por'] ?? ($data['motorista_nome'] ?? null),
            'responsavel_nome' => $data['responsavel_nome'] ?? null,
            'responsavel_funcao' => $data['responsavel_funcao'] ?? null,
            'comentarios_motorista' => $data['comentarios_motorista'] ?? null,
            'status' => $data['status'] ?? 'pendente',
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Checklist criado.',
            'data' => [
                'id' => $checklist->id,
            ],
        ], 201);
    }

    private function canAccess(Request $request, Checklist $checklist, ?int $operadorId = null): bool
    {
        $user = $request->user();
        $operadorId ??= $this->tenantContext->operadorId($user);

        if ((int) $checklist->operador_id !== $operadorId) {
            return false;
        }

        return ! $user->isMotorista() || (int) $checklist->motorista_id === (int) $user->id;
    }
}
