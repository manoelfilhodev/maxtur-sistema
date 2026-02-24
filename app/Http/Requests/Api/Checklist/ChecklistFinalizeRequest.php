<?php

namespace App\Http\Requests\Api\Checklist;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistFinalizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}

