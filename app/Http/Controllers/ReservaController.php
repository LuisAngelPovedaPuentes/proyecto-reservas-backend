<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Cancha;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function index()
    {
        // Traemos las reservas con la información de la cancha
        return response()->json(Reserva::with('cancha')->get(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cancha_id'      => 'required|exists:canchas,id',
            'nombre_cliente' => 'required|string',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after:fecha_inicio',
            'total_pago'     => 'required|numeric'
        ]);

        // Validación de disponibilidad (Corregido el guion bajo _)
        $ocupada = Reserva::where('cancha_id', $request->cancha_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('fecha_inicio', [$request->fecha_inicio, $request->fecha_fin])
                      ->orWhereBetween('fecha_fin', [$request->fecha_inicio, $request->fecha_fin]);
            })->exists();

        if ($ocupada) {
            return response()->json(['message' => 'Horario no disponible para esta cancha'], 400);
        }

        $reserva = Reserva::create($validated);
        // En lugar de retornar solo $reserva, cargamos la relación 'cancha'
        return response()->json($reserva->load('cancha'), 201);
    }

    public function destroy($id)
    {
        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        $reserva->delete();
        return response()->json(['message' => 'Reserva eliminada con éxito'], 200);
    }
}
