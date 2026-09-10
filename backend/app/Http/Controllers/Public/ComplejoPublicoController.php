<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComplejoDetalleResource;
use App\Http\Resources\ComplejoResource;
use App\Models\Complejo;
use App\Services\CalculadorDisponibilidadService;
use App\Services\WhatsAppLinkService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplejoPublicoController extends Controller
{
    public function __construct(
        private readonly CalculadorDisponibilidadService $calculadorDisponibilidad,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'distrito_id' => ['nullable', 'integer', 'exists:distritos,id'],
            'canton_id' => ['nullable', 'integer', 'exists:cantones,id'],
            'deporte_id' => ['nullable', 'integer', 'exists:deportes,id'],
        ]);

        $complejos = Complejo::query()
            ->where('activo', true)
            ->with(['distrito.canton', 'canchas' => function ($query) use ($request) {
                if ($request->filled('deporte_id')) {
                    $query->where('deporte_id', $request->integer('deporte_id'));
                }
            }])
            ->withCount('canchas')
            ->when($request->filled('distrito_id'), function ($query) use ($request) {
                $query->where('distrito_id', $request->integer('distrito_id'));
            })
            ->when($request->filled('canton_id'), function ($query) use ($request) {
                $query->whereHas('distrito', function ($q) use ($request) {
                    $q->where('canton_id', $request->integer('canton_id'));
                });
            })
            ->paginate(15);

        return response()->json([
            'data' => ComplejoResource::collection($complejos),
            'meta' => [
                'current_page' => $complejos->currentPage(),
                'last_page' => $complejos->lastPage(),
                'total' => $complejos->total(),
            ],
        ]);
    }

    public function show(Complejo $complejo): JsonResponse
    {
        abort_if(!$complejo->activo, 404);

        $complejo->load(['distrito.canton.provincia', 'canchas.deporte']);

        return response()->json([
            'data' => new ComplejoDetalleResource($complejo),
        ]);
    }

    public function disponibilidad(Request $request, Complejo $complejo): JsonResponse
    {
        abort_if(!$complejo->activo, 404);

        $request->validate([
            'fecha' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->string('fecha')->toString())
            : Carbon::today();

        $canchas = $complejo->canchas()->where('activa', true)->get();

        $disponibilidad = $canchas->map(function ($cancha) use ($fecha) {
            return [
                'cancha_id' => $cancha->id,
                'nombre' => $cancha->nombre,
                'precio_hora' => $cancha->precio_hora,
                'bloques' => $this->calculadorDisponibilidad->calcularParaCancha($cancha, $fecha),
            ];
        });

        return response()->json([
            'data' => [
                'fecha' => $fecha->toDateString(),
                'canchas' => $disponibilidad,
            ],
        ]);
    }

    public function enlaceWhatsApp(Request $request, Complejo $complejo, WhatsAppLinkService $whatsAppLinkService): JsonResponse
    {
        abort_if(!$complejo->activo, 404);

        $request->validate([
            'cancha_id' => ['nullable', 'integer', 'exists:canchas,id'],
            'fecha' => ['nullable', 'date'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
        ]);

        $cancha = $request->filled('cancha_id')
            ? $complejo->canchas()->findOrFail($request->integer('cancha_id'))
            : null;

        $fecha = $request->filled('fecha') ? Carbon::parse($request->string('fecha')->toString()) : null;

        $enlace = $whatsAppLinkService->generarEnlaceConsulta(
            $complejo,
            $cancha,
            $fecha,
            $request->string('hora_inicio')->toString() ?: null,
        );

        if (!$enlace) {
            return response()->json(['message' => 'Este complejo no ha configurado WhatsApp todavía.'], 404);
        }

        return response()->json(['data' => ['enlace' => $enlace]]);
    }
}