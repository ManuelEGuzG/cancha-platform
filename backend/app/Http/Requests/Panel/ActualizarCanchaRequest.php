<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarCanchaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:100'],
            'precio_hora' => ['sometimes', 'integer', 'min:0'],
            'activa' => ['sometimes', 'boolean'],
        ];
    }
}