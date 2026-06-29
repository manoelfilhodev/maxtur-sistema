<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\AtrasoPassageiro;
use App\Models\AtrasoViagem;
use App\Models\OcorrenciaViagem;
use App\Models\SolicitacaoViagem;
use App\Services\TenantContext;
use App\Support\ViagemStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class MotoristaPagamentoController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function extrato(Request $request)
    {
        [$inicio, $fim] = $this->periodo($request);
        $user = $request->user();
        $query = $this->query($request, $inicio, $fim);
        $totais = $this->totais(clone $query, $user);
        $viagens = $this->detalhes(clone $query)->orderByDesc('data_hora')->paginate(30)->withQueryString();
        $viagens->through(fn ($viagem) => $this->item($viagem, $user));

        return response()->json([
            'ok' => true,
            'message' => 'Extrato do motorista',
            'data' => [
                'periodo' => ['inicio' => $inicio, 'fim' => $fim],
                'motorista' => $this->motorista($user),
                'resumo' => $totais,
                'viagens' => $viagens,
                'observacao' => $user->tipo_recebimento === 'salario'
                    ? 'O salário é contratual; adicionais, descontos e proporcionalidade dependem da folha.'
                    : 'Somente viagens finalizadas compõem o valor calculado.',
            ],
        ]);
    }

    public function pdf(Request $request): Response
    {
        [$inicio, $fim] = $this->periodo($request);
        $user = $request->user();
        $query = $this->query($request, $inicio, $fim);
        $viagens = $this->detalhes(clone $query)->orderBy('data_hora')->get();
        $totais = $this->totais(clone $query, $user);
        $logoPath = public_path('images/logo.png');

        return Pdf::loadView('painel.relatorios.motoristas.pdf', [
            'motorista' => $user,
            'viagens' => $viagens,
            'totais' => $totais,
            'periodoLabel' => Carbon::parse($inicio)->format('d/m/Y').' a '.Carbon::parse($fim)->format('d/m/Y'),
            'geradoEm' => now(),
            'logoDataUri' => is_file($logoPath) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath)) : null,
        ])->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false, 'isPhpEnabled' => true])
            ->download('extrato-motorista-'.now()->format('Y-m-d-His').'.pdf');
    }

    private function periodo(Request $request): array
    {
        $request->merge([
            'data_inicio' => $request->input('data_inicio') ?: today()->startOfMonth()->toDateString(),
            'data_fim' => $request->input('data_fim') ?: today()->toDateString(),
        ]);
        $data = $request->validate([
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'status' => ['nullable', 'in:'.implode(',', ViagemStatus::all())],
        ]);

        return [$data['data_inicio'], $data['data_fim']];
    }

    private function query(Request $request, string $inicio, string $fim): Builder
    {
        return SolicitacaoViagem::query()
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->whereDate('data_hora', '>=', $inicio)->whereDate('data_hora', '<=', $fim)
            ->whereHas('ultimaAtribuicao', fn (Builder $query) => $query->where('motorista_id', $request->user()->id))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')));
    }

    private function detalhes(Builder $query): Builder
    {
        return $query->with([
            'cliente:id,nome_fantasia,razao_social',
            'ultimaAtribuicao.motorista:id,name,cpf,tipo_recebimento,valor_salario,valor_por_viagem',
            'ultimaAtribuicao.veiculo:id,placa,modelo',
        ])->withSum('atrasosViagem as atraso_viagem_total', 'minutos_atraso')
            ->withSum('atrasosPassageiro as atraso_passageiro_total', 'minutos_atraso')
            ->withCount('ocorrencias');
    }

    private function totais(Builder $query, $user): array
    {
        $ids = (clone $query)->select('solicitacoes_viagem.id');
        $finalizadas = (clone $query)->where('status', ViagemStatus::FINALIZADA)->count();
        $valor = $user->tipo_recebimento === 'por_viagem' ? $finalizadas * (float) $user->valor_por_viagem : 0;

        return [
            'viagens' => (clone $query)->count(),
            'finalizadas' => $finalizadas,
            'extras' => (clone $query)->where('natureza', 'extra')->count(),
            'canceladas' => (clone $query)->where('status', ViagemStatus::CANCELADA)->count(),
            'minutos_atraso' => (int) AtrasoViagem::query()->whereIn('solicitacao_id', clone $ids)->sum('minutos_atraso')
                + (int) AtrasoPassageiro::query()->whereIn('solicitacao_id', clone $ids)->sum('minutos_atraso'),
            'ocorrencias' => OcorrenciaViagem::query()->whereIn('solicitacao_id', clone $ids)->count(),
            'valor_calculado' => $valor,
            'valor_contratual' => $user->tipo_recebimento === 'salario' ? (float) $user->valor_salario : null,
        ];
    }

    private function item($viagem, $user): array
    {
        $elegivel = $viagem->status === ViagemStatus::FINALIZADA && $user->tipo_recebimento === 'por_viagem';

        return [
            'id' => $viagem->id,
            'data_hora' => $viagem->data_hora?->toIso8601String(),
            'cliente' => $viagem->cliente?->nome_fantasia ?: $viagem->cliente?->razao_social,
            'origem' => $viagem->origem, 'destino' => $viagem->destino,
            'veiculo' => $viagem->ultimaAtribuicao?->veiculo,
            'natureza' => $viagem->natureza, 'tipo_periodo' => $viagem->tipo_periodo,
            'status' => $viagem->status, 'status_label' => $viagem->statusLabel(),
            'atraso_minutos' => (int) $viagem->atraso_viagem_total + (int) $viagem->atraso_passageiro_total,
            'ocorrencias' => $viagem->ocorrencias_count,
            'elegivel_pagamento' => $elegivel,
            'valor' => $elegivel ? (float) $user->valor_por_viagem : 0,
        ];
    }

    private function motorista($user): array
    {
        return [
            'id' => $user->id, 'nome' => $user->name, 'cpf' => $user->cpf,
            'tipo_recebimento' => $user->tipo_recebimento,
            'valor_por_viagem' => $user->valor_por_viagem !== null ? (float) $user->valor_por_viagem : null,
            'valor_salario' => $user->valor_salario !== null ? (float) $user->valor_salario : null,
        ];
    }
}
