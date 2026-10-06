<?php

namespace App\Http\Controllers\Public;

use App\Exceptions\HorarioNoDisponibleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\CrearSolicitudReservaRequest;
use App\Models\Cancha;
use App\Models\Complejo;
use App\Services\ReservaService;
use App\Services\WhatsAppLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SolicitudReservaPublicaController extends Controller
{
    public function __construct(
        private readonly ReservaService $reservaService,
        private readonly WhatsAppLinkService $whatsAppLinkService,
    ) {
    }

    public function store(CrearSolicitudReservaRequest $request, Complejo $complejo): JsonResponse
    {
        abort_if(!$complejo->activo, 404);
        abort_if(!$complejo->whatsapp_numero, 422, 'El complejo no tiene WhatsApp configurado.');

        $datos = $request->validated();
        $captchaError = $this->verificarTurnstile($request, $datos['captcha_token'] ?? null);
        if ($captchaError) {
            return $captchaError;
        }

        $cancha = $complejo->canchas()
            ->where('activa', true)
            ->findOrFail($datos['cancha_id']);

        try {
            $reservas = $this->reservaService->crearSolicitud($cancha, $datos);
        } catch (HorarioNoDisponibleException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        return response()->json([
            'data' => [
                'solicitud_id' => $reservas->first()->solicitud_id,
                'expira_en' => $reservas->first()->expira_en->toIso8601String(),
                'whatsapp_url' => $this->whatsAppLinkService->generarEnlaceSolicitud($complejo, $cancha, $datos),
            ],
        ], 201);
    }

    private function verificarTurnstile(Request $request, ?string $token): ?JsonResponse
    {
        $secret = config('services.turnstile.secret');
        if (!$secret) {
            return app()->environment(['local', 'testing'])
                ? null
                : response()->json(['message' => 'La verificación anti-spam no está configurada.'], 503);
        }

        try {
            $respuesta = Http::asForm()->timeout(5)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ],
            );
        } catch (ConnectionException) {
            return response()->json(['message' => 'No se pudo verificar el CAPTCHA. Inténtalo de nuevo.'], 503);
        }

        $hostnameEsperado = config('services.turnstile.hostname');
        $hostnameRecibido = $respuesta->json('hostname');
        $accionRecibida = $respuesta->json('action');

        if (!$respuesta->successful()
            || !$respuesta->json('success')
            || ($hostnameEsperado && $hostnameRecibido !== $hostnameEsperado)
            || ($accionRecibida && $accionRecibida !== 'reserva')) {
            return response()->json(['message' => 'La verificación anti-spam no fue válida.'], 422);
        }

        return null;
    }
}