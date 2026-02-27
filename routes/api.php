<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanchaController;
use App\Http\Controllers\ReservaController; // Esta línea es fundamental

Route::apiResource('canchas', CanchaController::class);
Route::apiResource('reservas', ReservaController::class);
Route::get('canchas/{id}/reservas', [ReservaController::class, 'reservasPorCancha']);
