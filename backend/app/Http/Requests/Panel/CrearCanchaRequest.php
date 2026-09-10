<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class CrearCanchaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'complejo_id' => ['required', 'integer', 'exists:complejos,id'],
            'deporte_id' => ['required', 'integer', 'exists:deportes,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'precio_hora' => ['required', 'integer', 'min:0'],
        ];
    }
}