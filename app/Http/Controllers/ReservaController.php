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
        // Ya no validamos total_pago porque lo calcularemos nosotros
    ]);

    // 1. Buscar la cancha para obtener el precio
    $cancha = Cancha::find($request->cancha_id);

    // 2. Calcular la diferencia de horas
    $inicio = new \DateTime($request->fecha_inicio);
    $fin = new \DateTime($request->fecha_fin);
    $diferencia = $inicio->diff($fin);

    // Convertimos la diferencia a horas totales (incluyendo minutos como decimales)
    $horas = $diferencia->h + ($diferencia->i / 60) + ($diferencia->days * 24);

    // 3. Calcular el total
    $totalCalculado = $horas * $cancha->precio_por_hora;

    // 4. Validación de disponibilidad (la que ya tenías)
    $ocupada = Reserva::where('cancha_id', $request->cancha_id)
        ->where(function ($query) use ($request) {
            $query->whereBetween('fecha_inicio', [$request->fecha_inicio, $request->fecha_fin])
                  ->orWhereBetween('fecha_fin', [$request->fecha_inicio, $request->fecha_fin]);
        })->exists();

    if ($ocupada) {
        return response()->json(['message' => 'Horario no disponible'], 400);
    }

    // 5. Crear la reserva con el total calculado automáticamente
    $reserva = Reserva::create([
        'cancha_id'      => $request->cancha_id,
        'nombre_cliente' => $request->nombre_cliente,
        'fecha_inicio'   => $request->fecha_inicio,
        'fecha_fin'      => $request->fecha_fin,
        'total_pago'     => $totalCalculado,
        'estado'         => 'confirmada'
    ]);

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
