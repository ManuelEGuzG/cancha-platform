<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\Rol;
use App\Services\AdminTwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\Facades\Activity;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AdminTwoFactorService $twoFactor): JsonResponse
{
    $credentials = Arr::except($request->validated(), ['code']);
    $user = User::query()->where('email', $credentials['email'])->first();

    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Las credenciales proporcionadas son incorrectas.'],
        ]);
    }

    if (!$user->activo) {
        throw ValidationException::withMessages([
            'email' => ['Esta cuenta ha sido bloqueada. Contacta al administrador.'],
        ]);
    }

    if ($user->is_platform_admin && $user->two_factor_enabled_at
        && !$twoFactor->verificar($user, (string) $request->validated('code'))) {
        throw ValidationException::withMessages([
            'code' => ['El código de autenticación es inválido o ya fue utilizado.'],
        ]);
    }

    $token = $user->createToken('panel-token')->plainTextToken;

    activity('auth')->causedBy($user)->log('login');

    return response()->json([
        'user' => $this->formatearUsuario($user),
        'token' => $token,
    ])->header('Cache-Control', 'no-store, private');
}

    public function iniciarDosFactores(Request $request, AdminTwoFactorService $twoFactor): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $datos = $request->validate(['password' => ['required', 'string']]);

        abort_unless(Hash::check($datos['password'], $user->password), 422, 'La contraseña no es válida.');
        abort_if((bool) $user->two_factor_enabled_at, 409, 'La verificación de dos pasos ya está activa.');

        $setup = $twoFactor->iniciar($user);
        activity('security')->causedBy($user)->log('admin_2fa_setup_started');

        return response()->json(['data' => $setup])->header('Cache-Control', 'no-store, private');
    }

    public function confirmarDosFactores(Request $request, AdminTwoFactorService $twoFactor): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $datos = $request->validate(['code' => ['required', 'digits:6']]);
        $codigos = $twoFactor->confirmar($user, $datos['code']);

        if ($codigos === null) {
            throw ValidationException::withMessages(['code' => ['El código de autenticación no es válido.']]);
        }

        $user->tokens()->delete();
        activity('security')->causedBy($user)->log('admin_2fa_enabled');

        return response()->json([
            'message' => 'Verificación de dos pasos activada.',
            'data' => ['recovery_codes' => $codigos],
        ])->header('Cache-Control', 'no-store, private');
    }

    public function desactivarDosFactores(Request $request, AdminTwoFactorService $twoFactor): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $datos = $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'regex:/^[A-Za-z0-9-]{6,64}$/'],
        ]);

        abort_unless(Hash::check($datos['password'], $user->password), 422, 'La contraseña no es válida.');
        abort_unless($twoFactor->desactivar($user, $datos['code']), 422, 'El código de autenticación no es válido.');
        activity('security')->causedBy($user)->log('admin_2fa_disabled');

        return response()->json(['message' => 'Verificación de dos pasos desactivada.'])
            ->header('Cache-Control', 'no-store, private');
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