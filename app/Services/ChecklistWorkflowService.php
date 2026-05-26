<?php

namespace App\Services;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistResposta;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Support\ViagemStatus;
use Illuminate\Support\Facades\DB;

class ChecklistWorkflowService
{
    public function __construct(
        private ImageBase64Service $imageBase64Service,
        private NotificationService $notificationService,
        private TenantContext $tenantContext
    ) {}

    public function iniciar(User $user, array $data): Checklist
    {
        $operadorId = $this->tenantContext->operadorId($user);

        return Checklist::create([
            'operador_id' => $operadorId,
            'solicitacao_id' => $data['solicitacao_id'] ?? null,
            'veiculo_id' => $data['veiculo_id'],
            'motorista_id' => $data['motorista_id'],
            'status' => 'em_andamento',
            'started_at' => now(),
            'created_by' => $user->id,
        ]);
    }

    public function salvarRespostas(User $user, Checklist $checklist, array $respostas): void
    {
        $operadorId = $this->tenantContext->operadorId($user);
        $itens = ChecklistItem::query()->where('ativo', 1)->get()->keyBy('codigo');

        DB::transaction(function () use ($checklist, $respostas, $itens, $operadorId) {
            foreach ($respostas as $r) {
                $codigo = (int) $r['codigo'];
                $status = $r['status'];
                $observacao = $r['observacao'] ?? null;
                $foto = $r['foto_base64'] ?? null;

                if (!isset($itens[$codigo])) {
                    continue;
                }

                if ($status === 'falha') {
                    if (!$observacao || !$foto) {
                        throw new \InvalidArgumentException('Falha exige observacao e foto.');
                    }
                }

                $fotoPath = null;
                if ($foto) {
                    $fotoPath = $this->imageBase64Service->saveChecklistItemBase64($checklist->id, (string) $codigo, $foto);
                }

                ChecklistResposta::updateOrCreate(
                    [
                        'checklist_id' => $checklist->id,
                        'checklist_item_id' => $itens[$codigo]->id,
                    ],
                    [
                        'operador_id' => $operadorId,
                        'codigo' => $codigo,
                        'status' => $status,
                        'observacao' => $observacao,
                        'foto_path' => $fotoPath,
                    ]
                );
            }
        });
    }

    public function finalizar(User $user, Checklist $checklist): Checklist
    {
        $totalItens = ChecklistItem::query()->where('ativo', 1)->count();
        $respostas = ChecklistResposta::query()->where('checklist_id', $checklist->id)->get();

        if ($respostas->count() < $totalItens) {
            throw new \InvalidArgumentException('Nao e possivel finalizar checklist com itens sem resposta.');
        }

        $falhas = $respostas->where('status', 'falha');
        $resultado = $falhas->isNotEmpty() ? 'nao_conforme' : 'apto';

        $checklist->update([
            'status' => 'finalizado',
            'resultado' => $resultado,
            'finished_at' => now(),
        ]);

        if ($checklist->veiculo_id) {
            Veiculo::query()->whereKey($checklist->veiculo_id)->update([
                'status_operacional' => $resultado === 'apto' ? 'liberado' : 'bloqueado',
            ]);
        }

        if ($checklist->solicitacao_id) {
            SolicitacaoViagem::query()
                ->whereKey($checklist->solicitacao_id)
                ->update([
                    'status' => $resultado === 'apto'
                        ? ViagemStatus::PRONTA_PARA_EXECUCAO
                        : ViagemStatus::BLOQUEADA,
                ]);
        }

        if ($resultado === 'nao_conforme') {
            $this->notificationService->notifyAdmins(
                (int) $checklist->operador_id,
                'CHECKLIST_REPROVADO',
                'Checklist reprovado',
                'Checklist '.$checklist->id.' finalizado com nao conformidades.',
                ['checklist_id' => $checklist->id]
            );
        }

        return $checklist->fresh();
    }
}
