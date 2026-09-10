<?php

namespace App\Services;

use App\Models\Cancha;
use App\Models\Complejo;
use Carbon\Carbon;

class WhatsAppLinkService
{
    /**
     * Genera un enlace de WhatsApp (wa.me) con un mensaje pre-armado,
     * consultando disponibilidad de una cancha en una fecha/hora específica.
     */
    public function generarEnlaceConsulta(Complejo $complejo, ?Cancha $cancha = null, ?Carbon $fecha = null, ?string $horaInicio = null): ?string
    {
        if (!$complejo->whatsapp_numero) {
            return null; // el complejo no configuró WhatsApp todavía
        }

        $mensaje = $this->construirMensaje($complejo, $cancha, $fecha, $horaInicio);
        $numero = $this->normalizarNumero($complejo->whatsapp_numero);

        return sprintf('https://wa.me/%s?text=%s', $numero, rawurlencode($mensaje));
    }

    private function construirMensaje(Complejo $complejo, ?Cancha $cancha, ?Carbon $fecha, ?string $horaInicio): string
    {
        if ($cancha && $fecha && $horaInicio) {
            return sprintf(
                'Hola, quiero consultar la disponibilidad de la %s de %s para el %s a las %s.',
                $cancha->nombre,
                $complejo->nombre,
                $fecha->translatedFormat('d/m/Y'),
                $horaInicio,
            );
        }

        return sprintf('Hola, quiero consultar la disponibilidad de %s.', $complejo->nombre);
    }

    /**
     * wa.me requiere el número en formato internacional, solo dígitos
     * (sin +, espacios ni guiones). Costa Rica: código de país 506.
     */
    private function normalizarNumero(string $numero): string
    {
        $soloDigitos = preg_replace('/\D/', '', $numero);

        if (!str_starts_with($soloDigitos, '506') && strlen($soloDigitos) === 8) {
            $soloDigitos = '506' . $soloDigitos;
        }

        return $soloDigitos;
    }
}