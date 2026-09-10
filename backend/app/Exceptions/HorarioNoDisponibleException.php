<?php

namespace App\Exceptions;

use Exception;

class HorarioNoDisponibleException extends Exception
{
    public function __construct(string $message = 'Este horario ya no está disponible.')
    {
        parent::__construct($message);
    }
}