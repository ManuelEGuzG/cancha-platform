<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarComplejoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorización real vía Policy en el controller
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'direccion_texto' => ['sometimes', 'nullable', 'string', 'max:500'],
            'latitud' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'telefono' => ['sometimes', 'nullable', 'string', 'max:20'],
            'whatsapp_numero' => ['sometimes', 'nullable', 'string', 'max:20'],
            'logo_url' => ['sometimes', 'nullable', 'url', 'max:500'],
        ];
    }
}