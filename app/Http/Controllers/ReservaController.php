<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            'nombre_cliente' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'total_pago' => 'required|numeric'
        ]);

        // VALIDACIÓN MÁGICA: ¿Está ocupada la cancha en ese horario?
        $ocupada = Reserva::where('cancha_id', $request->cancha_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('fecha_inicio', [$request->fecha_inicio, $request->fecha_fin])
                      ->orWhereBetween('fecha_fin', [$request->fecha_inicio, $request->fecha_fin]);
            })->exists();

        if ($ocupada) {
            return response()->json(['message' => 'Lo siento, esta cancha ya está reservada en ese horario'], 400);
        }

        $reserva = Reserva::create($validated);
        return response()->json($reserva, 201);
    }
}
