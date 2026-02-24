<?php

namespace App\Http\Requests\Api\Atrasos;

use Illuminate\Foundation\Http\FormRequest;

class StoreAtrasoViagemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'minutos_atraso' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string'],
        ];
    }
}

