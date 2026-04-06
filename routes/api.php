<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\CanchaController;

// RUTAS PÚBLICAS (Cualquiera puede registrarse o ver canchas)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('canchas', [CanchaController::class, 'index']);

// RUTAS PROTEGIDAS (Solo con Token JWT)
Route::group(['middleware' => 'auth:api'], function () {
    // Rutas de Reservas
    Route::apiResource('reservas', ReservaController::class);
    Route::get('canchas/{id}/reservas', [ReservaController::class, 'reservasPorCancha']);

    // Perfil del usuario
    Route::get('me', function() {
        return response()->json(auth()->user());
    });
});
