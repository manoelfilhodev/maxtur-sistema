<?php

namespace App\Http\Requests\Api\Checklist;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistRespostasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'respostas' => ['required', 'array', 'min:1'],
            'respostas.*.codigo' => ['required', 'integer'],
            'respostas.*.status' => ['required', 'in:ok,falha'],
            'respostas.*.observacao' => ['nullable', 'string'],
            'respostas.*.foto_base64' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $respostas = $this->input('respostas', []);

            foreach ($respostas as $index => $resposta) {
                $status = $resposta['status'] ?? null;
                if ($status !== 'falha') {
                    continue;
                }

                $observacao = trim((string) ($resposta['observacao'] ?? ''));
                $foto = trim((string) ($resposta['foto_base64'] ?? ''));

                if ($observacao === '') {
                    $validator->errors()->add("respostas.$index.observacao", 'Observacao obrigatoria quando status for falha.');
                }

                if ($foto === '') {
                    $validator->errors()->add("respostas.$index.foto_base64", 'Foto obrigatoria quando status for falha.');
                }
            }
        });
    }
}
