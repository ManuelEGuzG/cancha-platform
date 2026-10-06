<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CrearUsuarioRequest;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UsuarioAdminController extends Controller
{
    public function index(): JsonResponse
    {
        $usuarios = User::with(['complejos' => function ($query) {
            $query->select('complejos.id', 'complejos.nombre');
        }])
            ->where('is_platform_admin', false)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'telefono', 'activo', 'created_at']);

        return response()->json(['data' => $usuarios]);
    }

    public function store(CrearUsuarioRequest $request): JsonResponse
    {
        $datos = $request->validated();

        $usuario = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'telefono' => $datos['telefono'] ?? null,
        ]);

        $rol = Rol::where('nombre', $datos['rol'])->firstOrFail();

        $usuario->complejos()->attach($datos['complejo_id'], ['rol_id' => $rol->id]);

        return response()->json(['data' => $usuario], 201);
    }

    public function toggleEstado(User $user): JsonResponse
    {
        abort_if($user->is_platform_admin, 403, 'No puedes bloquear a otro administrador.');

        $user->update(['activo' => !$user->activo]);

        if (!$user->activo) {
            $user->tokens()->delete();
        }

        return response()->json(['data' => $user]);
    }

    /**
     * Actividad de auditoría de un propietario/encargado específico,
     * sin importar a qué complejo pertenezca cada acción.
     */
    public function actividad(User $user): JsonResponse
    {
        $actividad = \Spatie\Activitylog\Models\Activity::where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->latest()
            ->paginate(30);

        return response()->json(['data' => $actividad]);
    }
}