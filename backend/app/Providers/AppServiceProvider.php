<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('reserva-publica', function ($request) {
            $telefono = preg_replace('/\D+/', '', (string) $request->input('telefono_cliente'));

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(10)->by('telefono:'.hash('sha256', $telefono)),
            ];
        });
    }
}