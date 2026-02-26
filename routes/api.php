<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanchaController; // Importamos tu controlador

// Esta ruta permite: Listar, Crear, Ver, Editar y Borrar canchas
Route::apiResource('canchas', CanchaController::class);

