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

    // Dentro de la función store, después de las validaciones iniciales:

    $horaInicio = (int)$inicio->format('H');
    $horaFin = (int)$fin->format('H');

    // Supongamos que el complejo abre de 08:00 a 22:00
    if ($horaInicio < 8 || $horaFin > 22) {
        return response()->json([
            'message' => 'Lo sentimos El centro deportivo solo atiende de 08:00 AM a 10:00 PM'
        ], 400);
    }

        $ahora = new \DateTime();
    if ($inicio < $ahora) {
        return response()->json([
            'message' => 'No puedes realizar una reserva para una fecha o hora que ya pasó'
        ], 400);
    }

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
        return response()->json(['message' => 'Se Cancelo la Reserva con éxito'], 200);
    }

    public function update(Request $request, $id)
{
    $reserva = Reserva::find($id);

    if (!$reserva) {
        return response()->json(['message' => 'Reserva no encontrada'], 404);
    }

    $validated = $request->validate([
        'cancha_id'      => 'exists:canchas,id',
        'nombre_cliente' => 'string',
        'fecha_inicio'   => 'date',
        'fecha_fin'      => 'date|after:fecha_inicio',
    ]);

    // 1. Si cambian las fechas o la cancha, recalculamos el precio
    $canchaId = $request->cancha_id ?? $reserva->cancha_id;
    $fechaInicio = $request->fecha_inicio ?? $reserva->fecha_inicio;
    $fechaFin = $request->fecha_fin ?? $reserva->fecha_fin;

    $cancha = Cancha::find($canchaId);
    $inicio = new \DateTime($fechaInicio);
    $fin = new \DateTime($fechaFin);
    $horas = $inicio->diff($fin)->h + ($inicio->diff($fin)->i / 60) + ($inicio->diff($fin)->days * 24);

    $totalCalculado = $horas * $cancha->precio_por_hora;

    // 2. Validación de disponibilidad (Excluyendo la reserva actual)
    $ocupada = Reserva::where('cancha_id', $canchaId)
        ->where('id', '!=', $id) // IMPORTANTE: No compararse con sí misma
        ->where(function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                  ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin]);
        })->exists();

    if ($ocupada) {
        return response()->json(['message' => 'El nuevo horario se cruza con otra reserva'], 400);
    }

    // 3. Actualizamos los datos
    $reserva->update(array_merge($validated, ['total_pago' => $totalCalculado]));

    return response()->json($reserva->load('cancha'), 200);
}
    public function reservasPorCancha($cancha_id)
    {
    $reservas = Reserva::where('cancha_id', $cancha_id)
                        ->orderBy('fecha_inicio', 'asc')
                        ->get();

    return response()->json($reservas, 200);
    }
}
