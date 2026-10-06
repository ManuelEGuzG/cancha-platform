<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
            'two_factor_recovery_codes' => null,
        ])->save();

        return [
            'secret' => $secreto,
            'otpauth_url' => $this->totp->getQRCodeUrl(config('app.name'), $user->email, $secreto),
        ];
    }

    public function confirmar(User $user, string $codigo): ?array
    {
        if (!$user->two_factor_secret || !$this->totp->verifyKey($user->two_factor_secret, $codigo)) {
            return null;
        }

        $codigos = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->all();

        $user->forceFill([
            'two_factor_enabled_at' => now(),
            'two_factor_recovery_codes' => array_map(
                fn (string $recoveryCode) => Hash::make(str_replace('-', '', $recoveryCode)),
                $codigos,
            ),
        ])->save();

        return $codigos;
    }

    public function verificar(User $user, string $codigo): bool
    {
        if (!$user->two_factor_enabled_at || !$user->two_factor_secret) {
            return false;
        }

        if (preg_match('/^\d{6}$/', $codigo) && $this->totp->verifyKey($user->two_factor_secret, $codigo)) {
            return true;
        }

        $codigos = $user->two_factor_recovery_codes ?? [];
        $normalizado = str_replace('-', '', $codigo);

        foreach ($codigos as $indice => $hash) {
            if (Hash::check($normalizado, $hash)) {
                unset($codigos[$indice]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codigos)])->save();

                return true;
            }
        }

        return false;
    }

    public function desactivar(User $user, string $codigo): bool
    {
        if (!$this->verificar($user, $codigo)) {
            return false;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return true;
    }
}