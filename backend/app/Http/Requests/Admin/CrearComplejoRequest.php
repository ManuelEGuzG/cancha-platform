<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CrearComplejoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'distrito_id' => ['required', 'integer', 'exists:distritos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'whatsapp_numero' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ];
    }
}