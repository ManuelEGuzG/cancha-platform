<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class AdminTwoFactorService
{
    public function __construct(private readonly Google2FA $totp)
    {
    }

    public function iniciar(User $user): array
    {
        $secreto = $this->totp->generateSecretKey(32);
        $user->forceFill([
            'two_factor_secret' => $secreto,
            'two_factor_enabled_at' => null,
            'two_factor_last_used_step' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return [
            'secret' => $secreto,
            'otpauth_url' => $this->totp->getQRCodeUrl(config('app.name'), $user->email, $secreto),
        ];
    }

    public function confirmar(User $user, string $codigo): ?array
    {
        $pasoValidado = $user->two_factor_secret
            ? $this->totp->verifyKeyNewer(
                $user->two_factor_secret,
                $codigo,
                $user->two_factor_last_used_step ?? 0,
            )
            : false;

        if (!$pasoValidado) {
            return null;
        }

        $codigos = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->all();

        $user->forceFill([
            'two_factor_enabled_at' => now(),
            'two_factor_last_used_step' => (int) $pasoValidado,
            'two_factor_recovery_codes' => array_map(
                fn (string $recoveryCode) => Hash::make(str_replace('-', '', $recoveryCode)),
                $codigos,
            ),
        ])->save();

        return $codigos;
    }

    public function verificar(User $user, string $codigo): bool
    {
        return DB::transaction(function () use ($user, $codigo): bool {
            $usuarioBloqueado = User::query()->lockForUpdate()->find($user->id);

            if (!$usuarioBloqueado?->two_factor_enabled_at || !$usuarioBloqueado->two_factor_secret) {
                return false;
            }

            if (preg_match('/^\d{6}$/', $codigo)) {
                $pasoValidado = $this->totp->verifyKeyNewer(
                    $usuarioBloqueado->two_factor_secret,
                    $codigo,
                    $usuarioBloqueado->two_factor_last_used_step ?? 0,
                );

                if ($pasoValidado) {
                    $usuarioBloqueado->forceFill(['two_factor_last_used_step' => (int) $pasoValidado])->save();

                    return true;
                }
            }

            $codigos = $usuarioBloqueado->two_factor_recovery_codes ?? [];
            $normalizado = str_replace('-', '', $codigo);

            foreach ($codigos as $indice => $hash) {
                if (Hash::check($normalizado, $hash)) {
                    unset($codigos[$indice]);
                    $usuarioBloqueado->forceFill(['two_factor_recovery_codes' => array_values($codigos)])->save();

                    return true;
                }
            }

            return false;
        }, attempts: 3);
    }

    public function desactivar(User $user, string $codigo): bool
    {
        if (!$this->verificar($user, $codigo)) {
            return false;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled_at' => null,
            'two_factor_last_used_step' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return true;
    }
}