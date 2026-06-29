<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Veiculo;
use App\Models\VeiculoManutencao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VeiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = Veiculo::query()
            ->withCount([
                'manutencoes',
                'manutencoes as manutencoes_atencao_count' => fn ($query) => $query->whereIn('status', ['proximo_vencimento', 'vencido']),
            ])
            ->orderBy('placa');

        if ($request->input('manutencao') === 'atencao') {
            $query->whereHas('manutencoes', fn ($query) => $query->whereIn('status', ['proximo_vencimento', 'vencido']));
        } elseif ($request->input('manutencao') === 'cadastrada') {
            $query->has('manutencoes');
        } elseif ($request->input('manutencao') === 'sem_registro') {
            $query->doesntHave('manutencoes');
        }

        $veiculos = $query->paginate(20)->withQueryString();

        return view('master.veiculos.index', compact('veiculos'));
    }

    public function create()
    {
        return view('master.veiculos.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedVehicle($request);

        Veiculo::query()->create([
            'operador_id' => $request->user()->operador_id ?: 1,
            ...$data,
        ]);

        return redirect()->route('master.veiculos.index')->with('success', 'Veículo cadastrado com sucesso.');
    }

    public function edit(Veiculo $veiculo)
    {
        return view('master.veiculos.edit', compact('veiculo'));
    }

    public function update(Request $request, Veiculo $veiculo)
    {
        $veiculo->update($this->validatedVehicle($request, $veiculo));

        foreach ($veiculo->manutencoes as $manutencao) {
            $manutencao->update(['status' => $manutencao->calcularStatus((int) $veiculo->km_atual)]);
        }

        return redirect()->route('master.veiculos.show', $veiculo)->with('success', 'Veículo atualizado com sucesso.');
    }

    private function validatedVehicle(Request $request, ?Veiculo $veiculo = null): array
    {
        return $request->validate([
            'placa' => ['required', 'string', 'max:15', Rule::unique('veiculos', 'placa')->ignore($veiculo?->id)],
            'modelo' => ['required', 'string', 'max:120'],
            'tipo' => ['required', Rule::in(['proprio', 'parceiro'])],
            'ano' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'data_documento' => ['nullable', 'date'],
            'km_atual' => ['required', 'integer', 'min:0'],
            'capacidade_passageiros' => ['required', 'integer', 'min:1'],
            'status_operacional' => ['required', 'in:liberado,bloqueado'],
        ]);
    }

    public function show(Veiculo $veiculo)
    {
        $veiculo->load('manutencoes');
        foreach ($veiculo->manutencoes as $manutencao) {
            $status = $manutencao->calcularStatus((int) $veiculo->km_atual);
            if ($status !== $manutencao->status) {
                $manutencao->update(['status' => $status]);
            }
        }

        return view('master.veiculos.show', compact('veiculo'));
    }

    public function storeManutencao(Request $request, Veiculo $veiculo)
    {
        $data = $this->validatedMaintenance($request);
        $manutencao = $veiculo->manutencoes()->create([...$data, 'status' => 'em_dia']);
        $manutencao->update(['status' => $manutencao->calcularStatus((int) $veiculo->km_atual)]);

        return back()->with('success', 'Item de manutenção cadastrado.');
    }

    public function updateManutencao(Request $request, Veiculo $veiculo, VeiculoManutencao $manutencao)
    {
        $this->ensureMaintenanceOwner($veiculo, $manutencao);
        $manutencao->update($this->validatedMaintenance($request));
        $manutencao->update(['status' => $manutencao->calcularStatus((int) $veiculo->km_atual)]);

        return back()->with('success', 'Item de manutenção atualizado.');
    }

    public function destroyManutencao(Veiculo $veiculo, VeiculoManutencao $manutencao)
    {
        $this->ensureMaintenanceOwner($veiculo, $manutencao);
        $manutencao->delete();

        return back()->with('success', 'Item de manutenção excluído.');
    }

    private function validatedMaintenance(Request $request): array
    {
        return $request->validate([
            'item' => ['required', 'string', 'max:120'],
            'km_referencia' => ['required', 'integer', 'min:0'],
            'km_vencimento' => ['required', 'integer', 'gte:km_referencia'],
            'data_vencimento' => ['nullable', 'date'],
            'observacao' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function ensureMaintenanceOwner(Veiculo $veiculo, VeiculoManutencao $manutencao): void
    {
        abort_unless((int) $manutencao->veiculo_id === (int) $veiculo->id, 404);
    }
}
