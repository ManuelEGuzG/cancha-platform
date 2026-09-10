<?php

namespace App\Enums;

enum EstadoDisponibilidad: string
{
    case DISPONIBLE = 'disponible';
    case OCUPADA = 'ocupada';
    case PENDIENTE = 'pendiente';
    case BLOQUEADA = 'bloqueada';
    case FUERA_DE_HORARIO = 'fuera_de_horario';

    public function emoji(): string
    {
        return match ($this) {
            self::DISPONIBLE => '🟢',
            self::OCUPADA => '🔴',
            self::PENDIENTE => '🟡',
            self::BLOQUEADA => '⚫',
            self::FUERA_DE_HORARIO => '⚪',
        };
    }
}