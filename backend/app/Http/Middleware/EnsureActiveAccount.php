<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user('sanctum') && !$request->user('sanctum')->activo, 403, 'Esta cuenta está bloqueada.');

        return $next($request);
    }
}