<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistResposta;
use App\Services\Checklist\ChecklistStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChecklistController extends Controller
{
    public function __construct(private ChecklistStatusService $statusService) {}

    public function index(Request $request)
    {
        $checklists = Checklist::query()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $checklists,
        ]);
    }

    public function show(Checklist $checklist)
    {
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

        $invalidos = $codigos->filter(fn ($c) => !$itens->has($c))->values();

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
                if (!empty($r['foto_base64'])) {
                    $fotoPath = $this->saveBase64Image(
                        $r['foto_base64'],
                        folder: 'checklists/'.$checklist->id.'/itens/'.$item->id
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
        return response()->json([
            'ok' => true,
            'message' => 'Checklist finalizado.',
            'data' => [
                'checklist_id' => $checklist->id,
            ],
        ]);
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
            'created_by' => ['nullable', 'integer'],
        ]);

        $checklist = Checklist::create([
            'veiculo_identificacao' => $data['veiculo_identificacao'] ?? null,
            'data' => $data['data'] ?? now()->toDateString(),
            'motorista_nome' => $data['motorista_nome'] ?? null,
            'empresa_fornecedora' => $data['empresa_fornecedora'] ?? null,
            'inspecionado_por' => $data['inspecionado_por'] ?? ($data['motorista_nome'] ?? null),
            'responsavel_nome' => $data['responsavel_nome'] ?? null,
            'responsavel_funcao' => $data['responsavel_funcao'] ?? null,
            'comentarios_motorista' => $data['comentarios_motorista'] ?? null,
            'status' => $data['status'] ?? 'pendente',
            'created_by' => $data['created_by'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Checklist criado.',
            'data' => [
                'id' => $checklist->id,
            ],
        ], 201);
    }

    private function saveBase64Image(string $base64, string $folder): string
    {
        if (str_contains($base64, ',')) {
            [$meta, $data] = explode(',', $base64, 2);

            $ext = 'jpg';
            if (str_contains($meta, 'image/png')) {
                $ext = 'png';
            }
            if (str_contains($meta, 'image/webp')) {
                $ext = 'webp';
            }
            if (str_contains($meta, 'image/jpg') || str_contains($meta, 'image/jpeg')) {
                $ext = 'jpg';
            }
        } else {
            $data = $base64;
            $ext = 'jpg';
        }

        $binary = base64_decode($data);
        if ($binary === false) {
            throw new \RuntimeException('Base64 invalido (decode falhou).');
        }

        $filename = now()->format('Ymd_His').'_'.\Illuminate\Support\Str::random(10).'.'.$ext;

        $publicBase = public_path('storage');
        $fullDir = $publicBase.DIRECTORY_SEPARATOR.trim($folder, '/');

        if (!is_dir($fullDir)) {
            @mkdir($fullDir, 0755, true);
        }

        $fullPath = $fullDir.DIRECTORY_SEPARATOR.$filename;

        if (file_put_contents($fullPath, $binary) === false) {
            throw new \RuntimeException('Falha ao salvar imagem em: '.$fullPath);
        }

        return trim($folder, '/').'/'.$filename;
    }
}