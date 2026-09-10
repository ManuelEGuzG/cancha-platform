<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CantonResource;
use App\Http\Resources\DistritoResource;
use App\Http\Resources\ProvinciaResource;
use App\Models\Canton;
use App\Models\Distrito;
use App\Models\Provincia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeografiaPublicaController extends Controller
{
    public function provincias(): JsonResponse
    {
        $provincias = Provincia::orderBy('nombre')->get();

        return response()->json([
            'data' => ProvinciaResource::collection($provincias),
        ]);
    }

    public function cantones(Request $request): JsonResponse
    {
        $request->validate([
            'provincia_id' => ['required', 'integer', 'exists:provincias,id'],
        ]);

        $cantones = Canton::where('provincia_id', $request->integer('provincia_id'))
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'data' => CantonResource::collection($cantones),
        ]);
    }

    public function distritos(Request $request): JsonResponse
    {
        $request->validate([
            'canton_id' => ['required', 'integer', 'exists:cantones,id'],
        ]);

        $distritos = Distrito::where('canton_id', $request->integer('canton_id'))
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'data' => DistritoResource::collection($distritos),
        ]);
    }
}