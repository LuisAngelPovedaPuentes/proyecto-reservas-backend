<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\CanchaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- RUTAS PÚBLICAS ---
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// --- RUTAS PROTEGIDAS (Requieren Token JWT) ---
Route::group(['middleware' => 'auth:api'], function () {

    // Rutas para Canchas (CRUD completo para el Admin)
    Route::get('canchas', [CanchaController::class, 'index']);      // Listar todas
    Route::post('canchas', [CanchaController::class, 'store']);     // Crear nueva
    Route::put('canchas/{id}', [CanchaController::class, 'update']); // Modificar existente
    Route::delete('canchas/{id}', [CanchaController::class, 'destroy']); // Eliminar

    // Rutas para Reservas (Usando apiResource para abreviar)
    Route::apiResource('reservas', ReservaController::class);
    Route::get('canchas/{id}/reservas', [ReservaController::class, 'reservasPorCancha']);
    Route::get('/usuarios-lista', [ReservaController::class, 'listarUsuarios'])->middleware('auth:api');

    // Perfil del usuario autenticado
    Route::get('me', function() {
        return response()->json(auth()->user());
    });

    // Cerrar sesión
    Route::post('logout', [AuthController::class, 'logout']);
});
