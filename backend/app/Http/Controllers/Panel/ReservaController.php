<?php

namespace App\Http\Controllers\Panel;

use App\Exceptions\HorarioNoDisponibleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\CrearReservaRequest;
use App\Models\Cancha;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // 1. Importar el trait
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    use AuthorizesRequests; // 2. Usar el trait aquí

    public function __construct(
        private readonly ReservaService $reservaService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'fecha' => ['nullable', 'date'],
            'cancha_id' => ['nullable', 'integer', 'exists:canchas,id'],
        ]);

        $reservas = Reserva::query()
            ->with('cancha')
            ->when($request->filled('fecha'), fn ($q) => $q->whereDate('fecha', $request->string('fecha')->toString()))
            ->when($request->filled('cancha_id'), fn ($q) => $q->where('cancha_id', $request->integer('cancha_id')))
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->paginate(20);

        return response()->json(['data' => $reservas]);
    }

    public function store(CrearReservaRequest $request): JsonResponse
    {
        $cancha = Cancha::findOrFail($request->integer('cancha_id'));

        $this->authorize('gestionar', $cancha);

        try {
            $reserva = $this->reservaService->crear($cancha, [
                ...$request->validated(),
                'creado_por' => $request->user()->id,
                'origen' => 'manual',
            ]);
        } catch (HorarioNoDisponibleException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['data' => $reserva], 201);
    }

    public function destroy(Reserva $reserva): JsonResponse
    {
        $this->authorize('gestionar', $reserva->cancha);

        $reserva->update(['estado' => 'cancelada']);

        return response()->json(['message' => 'Reserva cancelada correctamente.']);
    }
}