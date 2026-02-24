<?php

namespace App\Http\Requests\Api\Solicitacoes;

use Illuminate\Foundation\Http\FormRequest;

class ClienteStoreSolicitacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origem' => ['required', 'string', 'max:255'],
            'destino' => ['required', 'string', 'max:255'],
            'data_hora' => ['required', 'date'],
            'passageiros_previstos' => ['nullable', 'integer', 'min:0'],
            'observacao' => ['nullable', 'string'],
            'passageiro_ids' => ['nullable', 'array'],
            'passageiro_ids.*' => ['integer', 'exists:passageiros,id'],
        ];
    }
}

