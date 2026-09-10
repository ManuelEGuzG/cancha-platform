<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class CrearReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización real ocurre vía Policy en el controller
    }

    public function rules(): array
    {
        return [
            'cancha_id' => ['required', 'integer', 'exists:canchas,id'],
            'nombre_cliente' => ['required', 'string', 'max:255'],
            'telefono_cliente' => ['nullable', 'string', 'max:20'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'estado' => ['nullable', 'in:pendiente,confirmada'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }
}