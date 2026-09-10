<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Panel\BloqueoController;
use App\Http\Controllers\Panel\ReservaController;
use App\Http\Controllers\Public\ComplejoPublicoController;
use App\Http\Controllers\Public\GeografiaPublicaController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::prefix('complejos')->group(function () {
    Route::get('/', [ComplejoPublicoController::class, 'index']);
    Route::get('/{complejo}', [ComplejoPublicoController::class, 'show']);
    Route::get('/{complejo}/disponibilidad', [ComplejoPublicoController::class, 'disponibilidad']);
});

Route::prefix('geografia')->group(function () {
    Route::get('/provincias', [GeografiaPublicaController::class, 'provincias']);
    Route::get('/cantones', [GeografiaPublicaController::class, 'cantones']);
    Route::get('/distritos', [GeografiaPublicaController::class, 'distritos']);
});

Route::prefix('panel')->middleware('auth:sanctum')->group(function () {
    Route::get('/reservas', [ReservaController::class, 'index']);
    Route::post('/reservas', [ReservaController::class, 'store']);
    Route::delete('/reservas/{reserva}', [ReservaController::class, 'destroy']);

    Route::post('/bloqueos', [BloqueoController::class, 'store']);
    Route::delete('/bloqueos/{bloqueo}', [BloqueoController::class, 'destroy']);
});