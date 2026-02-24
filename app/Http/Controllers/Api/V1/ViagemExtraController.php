<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ViagemExtraController extends Controller
{
    // GET /api/v1/viagens/funcionarios
    public function funcionarios()
    {
        $rows = DB::table('viagem_funcionarios')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get(['id', 'nome', 'telefone', 'endereco']);

        return response()->json([
            'ok' => true,
            'data' => $rows
        ]);
    }

    // POST /api/v1/viagens/solicitacoes
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'data' => ['required', 'date'],
            'hora' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'rota' => ['required', 'string', 'max:120'],
            'funcionario_id' => ['required', 'integer', 'min:1'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:50'],
            'empresa' => ['required', 'string', 'max:255'],
            'observacao' => ['nullable', 'string'],
            // solicitante (MVP sem login)
            'solicitante_nome' => ['nullable', 'string', 'max:255'],
            'solicitante_email' => ['nullable', 'string', 'max:255'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Dados inválidos.',
                'errors' => $v->errors()
            ], 422);
        }

        // Confere funcionário
        $func = DB::table('viagem_funcionarios')
            ->where('id', (int)$request->funcionario_id)
            ->where('ativo', 1)
            ->first();

        if (!$func) {
            return response()->json([
                'ok' => false,
                'message' => 'Funcionário inválido ou inativo.'
            ], 422);
        }

        $now = now();
        $id = DB::table('viagem_solicitacoes')->insertGetId([
            'solicitante_nome' => $request->solicitante_nome,
            'solicitante_email' => $request->solicitante_email,
            'funcionario_id' => (int)$request->funcionario_id,
            'data' => $request->data,
            'hora' => $request->hora,
            'rota' => $request->rota,
            'endereco' => $request->endereco ?? $func->endereco,
            'telefone' => $request->telefone ?? $func->telefone,
            'empresa' => $request->empresa,
            'observacao' => $request->observacao,
            'status' => 'pendente',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Solicitação enviada.',
            'data' => [
                'id' => $id,
                'status' => 'pendente'
            ]
        ], 201);
    }

    // GET /api/v1/viagens/minhas
    // MVP sem login: filtra por solicitante_email (querystring) OU retorna as últimas 50
    // Ex: /api/v1/viagens/minhas?email=cliente@x.com
    public function minhas(Request $request)
    {
        $q = DB::table('viagem_solicitacoes as s')
            ->join('viagem_funcionarios as f', 'f.id', '=', 's.funcionario_id')
            ->select([
                's.id', 's.data', 's.hora', 's.rota', 's.endereco', 's.telefone',
                's.empresa', 's.status', 's.created_at',
                'f.id as funcionario_id', 'f.nome as funcionario_nome'
            ])
            ->orderByDesc('s.id');

        if ($request->filled('email')) {
            $q->where('s.solicitante_email', $request->query('email'));
        }

        $rows = $q->limit(50)->get();

        return response()->json([
            'ok' => true,
            'data' => $rows
        ]);
    }

    // (Opcional) GET /api/v1/viagens/solicitacoes  (painel Adriano)
    public function indexAdmin(Request $request)
    {
        $q = DB::table('viagem_solicitacoes as s')
            ->join('viagem_funcionarios as f', 'f.id', '=', 's.funcionario_id')
            ->select([
                's.*',
                'f.nome as funcionario_nome'
            ])
            ->orderByDesc('s.id');

        if ($request->filled('status')) {
            $q->where('s.status', $request->query('status'));
        }

        if ($request->filled('data_de')) {
            $q->where('s.data', '>=', $request->query('data_de'));
        }
        if ($request->filled('data_ate')) {
            $q->where('s.data', '<=', $request->query('data_ate'));
        }

        return response()->json([
            'ok' => true,
            'data' => $q->limit(200)->get()
        ]);
    }

    // (Opcional) POST /api/v1/viagens/solicitacoes/{id}/status (painel Adriano)
    public function updateStatus($id, Request $request)
    {
        $v = Validator::make($request->all(), [
            'status' => ['required', 'in:pendente,aprovado,negado,cancelado'],
            'admin_nome' => ['nullable', 'string', 'max:255'],
            'admin_observacao' => ['nullable', 'string'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Dados inválidos.',
                'errors' => $v->errors()
            ], 422);
        }

        $updated = DB::table('viagem_solicitacoes')
            ->where('id', (int)$id)
            ->update([
                'status' => $request->status,
                'admin_nome' => $request->admin_nome,
                'admin_observacao' => $request->admin_observacao,
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitação não encontrada.'
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Status atualizado.'
        ]);
    }
}
