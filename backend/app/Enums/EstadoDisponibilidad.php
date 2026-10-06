<?php

namespace App\Enums;

enum EstadoDisponibilidad: string
{
    case DISPONIBLE = 'disponible';
    case RESERVADO = 'reservado';
    case EN_TRAMITE = 'en_tramite';
    case CERRADA = 'cerrada';
    case EN_MANTENIMIENTO = 'en_mantenimiento';

    public function emoji(): string
    {
        return match ($this) {
            self::DISPONIBLE => '🟢',
            self::RESERVADO => '🔴',
            self::EN_TRAMITE => '🟡',
            self::CERRADA => '⚫',
            self::EN_MANTENIMIENTO => '🟠',
        };
    }
}