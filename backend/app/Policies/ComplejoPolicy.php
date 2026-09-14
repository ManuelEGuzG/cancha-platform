<?php

namespace App\Policies;

use App\Models\Complejo;
use App\Models\User;

class ComplejoPolicy
{
    public function gestionar(User $user, Complejo $complejo): bool
    {
        if ($user->is_platform_admin) {
            return true;
        }

        if (!$complejo->activo) {
            return false; // complejo bloqueado por el admin
        }

        return $user->perteneceAComplejo($complejo->id);
    }
}