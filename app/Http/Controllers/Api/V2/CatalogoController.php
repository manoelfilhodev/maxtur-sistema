<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Passageiro;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function clientes(Request $request)
    {
        $items = Cliente::query()
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->where('ativo', true)->orderBy('razao_social')
            ->get(['id', 'razao_social', 'nome_fantasia']);

        return $this->response('Clientes listados', $items);
    }

    public function motoristas(Request $request)
    {
        $items = User::query()
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->where('ativo', true)
            ->where(function (Builder $query) {
                $query->whereIn('role', ['motorista', 'MOTORISTA'])->orWhereIn('cargo', ['motorista', 'MOTORISTA']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'telefone', 'tipo_recebimento']);

        return $this->response('Motoristas listados', $items);
    }

    public function veiculos(Request $request)
    {
        $items = Veiculo::query()
            ->where('operador_id', $this->tenantContext->operadorId($request->user()))
            ->orderBy('placa')
            ->get(['id', 'placa', 'modelo', 'tipo', 'capacidade_passageiros', 'status_operacional']);

        return $this->response('Veículos listados', $items);
    }

    public function passageiros(Request $request)
    {
        $request->validate(['cliente_id' => ['nullable', 'integer', 'exists:clientes,id']]);
        $user = $request->user();
        $operadorId = $this->tenantContext->operadorId($user);
        $clienteId = $user->isCliente() ? $user->cliente_id : $request->integer('cliente_id');

        $query = Passageiro::query()->where('operador_id', $operadorId)->where('ativo', true);
        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return $this->response('Passageiros listados', $query->orderBy('nome')->get(['id', 'cliente_id', 'nome', 'telefone']));
    }

    private function response(string $message, $data)
    {
        return response()->json(['ok' => true, 'message' => $message, 'data' => $data]);
    }
}
