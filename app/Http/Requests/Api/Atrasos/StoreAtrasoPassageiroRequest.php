<?php

namespace App\Http\Requests\Api\Atrasos;

use Illuminate\Foundation\Http\FormRequest;

class StoreAtrasoPassageiroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'passageiro_id' => ['required', 'integer', 'exists:passageiros,id'],
            'minutos_atraso' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string'],
        ];
    }
}

