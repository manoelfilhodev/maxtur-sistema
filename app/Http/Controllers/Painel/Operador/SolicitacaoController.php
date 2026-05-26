<?php

namespace App\Http\Controllers\Painel\Operador;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Passageiro;
use App\Models\SolicitacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\TenantContext;
use App\Services\ViagemOperacionalService;
use App\Support\ViagemStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SolicitacaoController extends Controller
{
    public function __construct(
        private TenantContext $tenantContext,
        private ViagemOperacionalService $viagemOperacional
    ) {}

    public function index(Request $request)
    {
        $operadorId = $this->tenantContext->operadorId($request->user());
        $query = SolicitacaoViagem::query()
            ->with(['cliente:id,nome_fantasia,razao_social', 'atribuicoes.veiculo:id,placa', 'atribuicoes.motorista:id,name'])
            ->where('operador_id', $operadorId)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $solicitacoes = $query->paginate(20);

        $statusOptions = ViagemStatus::labels();

        return view('painel.operador.solicitacoes.index', compact('solicitacoes', 'statusOptions'));
    }

    public function create(Request $request)
    {
        $operadorId = $this->tenantContext->operadorId($request->user());
        $clientes = Cliente::query()
            ->where('operador_id', $operadorId)
            ->orderBy('nome_fantasia')
            ->get(['id', 'nome_fantasia', 'razao_social']);
        $passageiros = Passageiro::query()
            ->where('operador_id', $operadorId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'cliente_id', 'nome']);

        return view('painel.operador.solicitacoes.create', compact('clientes', 'passageiros'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'origem' => ['required', 'string', 'max:255'],
            'destino' => ['required', 'string', 'max:255'],
            'data_hora' => ['required', 'date'],
            'passageiros_previstos' => ['nullable', 'integer', 'min:0'],
            'observacao' => ['nullable', 'string'],
            'passageiro_ids' => ['nullable', 'array'],
            'passageiro_ids.*' => ['integer', 'exists:passageiros,id'],
        ]);

        $operadorId = $this->tenantContext->operadorId($request->user());
        Cliente::query()->where('operador_id', $operadorId)->findOrFail($data['cliente_id']);

        $solicitacao = SolicitacaoViagem::create([
            'operador_id' => $operadorId,
            'cliente_id' => $data['cliente_id'],
            'origem' => $data['origem'],
            'destino' => $data['destino'],
            'data_hora' => $data['data_hora'],
            'passageiros_previstos' => $data['passageiros_previstos'] ?? 0,
            'observacao' => $data['observacao'] ?? null,
            'status' => ViagemStatus::SOLICITADA,
        ]);

        $passageiroIds = collect($data['passageiro_ids'] ?? [])->unique()->values();
        if ($passageiroIds->isNotEmpty()) {
            $validIds = Passageiro::query()
                ->where('operador_id', $operadorId)
                ->where('cliente_id', $solicitacao->cliente_id)
                ->whereIn('id', $passageiroIds)
                ->pluck('id')
                ->all();
            $solicitacao->passageiros()->syncWithPivotValues($validIds, [
                'operador_id' => $operadorId,
            ]);
        }

        return redirect()
            ->route('painel.operador.solicitacoes.show', $solicitacao->id)
            ->with('success', 'Solicitação de viagem criada com sucesso.');
    }

    public function show(Request $request, int $id)
    {
        $operadorId = $this->tenantContext->operadorId($request->user());

        $solicitacao = SolicitacaoViagem::query()
            ->with([
                'cliente:id,nome_fantasia,razao_social',
                'passageiros:id,nome',
                'atribuicoes.veiculo:id,placa,modelo,capacidade_passageiros,status_operacional',
                'atribuicoes.motorista:id,name,role,ativo',
                'checklists.respostas.item:id,codigo,titulo',
                'atrasosViagem.responsavel:id,name',
                'atrasosPassageiro.passageiro:id,nome',
                'ocorrencias.responsavel:id,name',
            ])
            ->where('operador_id', $operadorId)
            ->findOrFail($id);

        $veiculos = Veiculo::query()->where('operador_id', $operadorId)->orderBy('placa')->get(['id', 'placa', 'modelo', 'capacidade_passageiros', 'status_operacional']);
        $motoristas = User::query()
            ->where('operador_id', $operadorId)
            ->whereIn('role', ['motorista', 'MOTORISTA'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'ativo']);
        $statusOptions = ViagemStatus::labels();

        return view('painel.operador.solicitacoes.show', compact('solicitacao', 'veiculos', 'motoristas', 'statusOptions'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'in:'.implode(',', ViagemStatus::all())],
        ]);

        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->findOrFail($id);

        $solicitacao->update(['status' => $request->string('status')]);

        return back()->with('success', 'Status atualizado com sucesso.');
    }

    public function atribuir(Request $request, int $id)
    {
        $request->validate([
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'motorista_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $operadorId = $this->tenantContext->operadorId($request->user());

        $solicitacao = SolicitacaoViagem::query()
            ->where('operador_id', $operadorId)
            ->findOrFail($id);

        $veiculo = Veiculo::query()->where('operador_id', $operadorId)->findOrFail($request->integer('veiculo_id'));
        $motorista = User::query()
            ->where('operador_id', $operadorId)
            ->whereIn('role', ['motorista', 'MOTORISTA'])
            ->findOrFail($request->integer('motorista_id'));

        try {
            $this->viagemOperacional->atribuir($solicitacao, $veiculo, $motorista, $request->user());
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        }

        return back()->with('success', 'Atribuição registrada. A viagem agora aguarda checklist do motorista.');
    }
}
