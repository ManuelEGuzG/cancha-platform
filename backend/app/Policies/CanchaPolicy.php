<?php

namespace App\Policies;

use App\Models\Cancha;
use App\Models\User;

class CanchaPolicy
{
    public function gestionar(User $user, Cancha $cancha): bool
    {
        if ($user->is_platform_admin) {
            return true;
        }

        return $user->perteneceAComplejo($cancha->complejo_id);
    }
}