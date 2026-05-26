<?php

namespace App\Http\Requests\Api\Solicitacoes;

use App\Support\ViagemStatus;
use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateSolicitacaoStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:'.implode(',', ViagemStatus::all())],
        ];
    }
}
