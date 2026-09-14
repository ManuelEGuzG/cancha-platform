<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\Facades\Activity;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
{
    $credentials = $request->validated();

    if (!Auth::attempt($credentials)) {
        throw ValidationException::withMessages([
            'email' => ['Las credenciales proporcionadas son incorrectas.'],
        ]);
    }

    /** @var User $user */
    $user = Auth::user();

    if (!$user->activo) {
        Auth::logout();
        throw ValidationException::withMessages([
            'email' => ['Esta cuenta ha sido bloqueada. Contacta al administrador.'],
        ]);
    }

    $token = $user->createToken('panel-token')->plainTextToken;

    activity('auth')->causedBy($user)->log('login');

    return response()->json([
        'user' => $this->formatearUsuario($user),
        'token' => $token,
    ]);
}

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        activity('auth')
            ->causedBy($user)
            ->log('logout');

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => $this->formatearUsuario($user),
        ]);
    }

    private function formatearUsuario(User $user): array
    {
        $complejos = $user->complejos()->get()->map(function ($complejo) {
            return [
                'id' => $complejo->id,
                'nombre' => $complejo->nombre,
                'slug' => $complejo->slug,
                'rol' => Rol::find($complejo->pivot->rol_id)?->nombre,
            ];
        });

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_platform_admin' => $user->is_platform_admin,
            'complejos' => $complejos,
        ];
    }
}