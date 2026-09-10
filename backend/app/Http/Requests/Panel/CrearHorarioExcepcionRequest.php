<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class CrearHorarioExcepcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancha_id' => ['required', 'integer', 'exists:canchas,id'],
            'fecha' => ['required', 'date'],
            'hora_apertura' => ['nullable', 'date_format:H:i', 'required_with:hora_cierre'],
            'hora_cierre' => ['nullable', 'date_format:H:i', 'required_with:hora_apertura', 'after:hora_apertura'],
            'motivo' => ['required', 'string', 'max:255'],
        ];
    }
}