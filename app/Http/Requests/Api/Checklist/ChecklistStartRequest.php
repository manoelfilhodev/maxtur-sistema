<?php

namespace App\Http\Requests\Api\Checklist;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistStartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solicitacao_id' => ['nullable', 'integer', 'exists:solicitacoes_viagem,id'],
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'motorista_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
