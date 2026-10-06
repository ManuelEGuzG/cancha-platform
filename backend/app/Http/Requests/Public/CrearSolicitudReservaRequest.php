<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class CrearSolicitudReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancha_id' => ['required', 'integer', 'exists:canchas,id'],
            'nombre_cliente' => ['required', 'string', 'max:255'],
            'cedula_cliente' => ['required', 'string', 'min:5', 'max:30'],
            'telefono_cliente' => ['required', 'string', 'regex:/^\+?[0-9()\s-]{8,20}$/'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'horas' => ['required', 'array', 'min:1', 'max:8'],
            'horas.*' => ['required', 'date_format:H:i', 'distinct:strict'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'prohibited'],
            'captcha_token' => [config('services.turnstile.secret') ? 'required' : 'nullable', 'string', 'max:2048'],
        ];
    }
}