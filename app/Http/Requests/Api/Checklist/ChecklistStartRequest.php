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
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'motorista_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
