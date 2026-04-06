<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Cancha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    /**
     * Constructor para proteger las rutas.
     * Solo el index y reservasPorCancha podrían ser públicos si quisieras,
     * pero para reservar o cancelar SIEMPRE debe estar logueado.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        // Si es Admin ve todas, si es Cliente solo las suyas
        if (auth()->user()->role === 'admin') {
            return response()->json(Reserva::with('cancha')->get(), 200);
        }

        return response()->json(Reserva::where('user_id', auth()->id())->with('cancha')->get(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cancha_id'    => 'required|exists:canchas,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
        ]);

        // 1. Datos de la cancha
        $cancha = Cancha::find($request->cancha_id);

        // 2. Cálculos de tiempo
        $inicio = new \DateTime($request->fecha_inicio);
        $fin = new \DateTime($request->fecha_fin);
        $ahora = new \DateTime();

        // VALIDACIÓN: Horario de atención (8 AM - 10 PM)
        $horaInicio = (int)$inicio->format('H');
        $horaFin = (int)$fin->format('H');
        if ($horaInicio < 8 || $horaFin > 22) {
            return response()->json(['message' => 'El centro deportivo solo atiende de 08:00 AM a 10:00 PM'], 400);
        }

        // VALIDACIÓN: No reservar en el pasado
        if ($inicio < $ahora) {
            return response()->json(['message' => 'No puedes realizar una reserva para una fecha o hora que ya pasó'], 400);
        }

        // 3. VALIDACIÓN DE DISPONIBILIDAD (Evitar choques de horario)
        $ocupada = Reserva::where('cancha_id', $request->cancha_id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('fecha_inicio', '<', $request->fecha_fin)
                      ->where('fecha_fin', '>', $request->fecha_inicio);
                });
            })->exists();

        if ($ocupada) {
            return response()->json(['message' => 'Lo sentimos, esta cancha ya está reservada en ese horario'], 400);
        }

        // 4. Calcular Total
        $diferencia = $inicio->diff($fin);
        $horas = $diferencia->h + ($diferencia->i / 60) + ($diferencia->days * 24);
        $totalCalculado = $horas * $cancha->precio_por_hora;

        // 5. CREAR RESERVA (Asociada al usuario logueado)
        $reserva = Reserva::create([
            'cancha_id'      => $request->cancha_id,
            'user_id'        => auth()->id(), // ID del usuario que inició sesión
            'nombre_cliente' => auth()->user()->name, // Nombre automático desde su cuenta
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

        // SEGURIDAD: Solo el dueño de la reserva o el Admin pueden cancelar
        if (auth()->user()->role !== 'admin' && $reserva->user_id !== auth()->id()) {
            return response()->json(['message' => 'No tienes permiso para cancelar esta reserva'], 403);
        }

        $reserva->delete();
        return response()->json(['message' => 'Reserva cancelada con éxito'], 200);
    }

    public function update(Request $request, $id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        // SEGURIDAD: Un cliente solo puede editar su propia reserva
        if (auth()->user()->role !== 'admin' && $reserva->user_id !== auth()->id()) {
            return response()->json(['message' => 'No tienes permiso para modificar esta reserva'], 403);
        }

        $validated = $request->validate([
            'cancha_id'    => 'exists:canchas,id',
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date|after:fecha_inicio',
        ]);

        $canchaId = $request->cancha_id ?? $reserva->cancha_id;
        $fechaInicio = $request->fecha_inicio ?? $reserva->fecha_inicio;
        $fechaFin = $request->fecha_fin ?? $reserva->fecha_fin;

        // Recalcular precio
        $cancha = Cancha::find($canchaId);
        $inicio = new \DateTime($fechaInicio);
        $fin = new \DateTime($fechaFin);
        $horas = $inicio->diff($fin)->h + ($inicio->diff($fin)->i / 60) + ($inicio->diff($fin)->days * 24);
        $totalCalculado = $horas * $cancha->precio_por_hora;

        // Validación de disponibilidad (Excluyendo la actual)
        $ocupada = Reserva::where('cancha_id', $canchaId)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<', $fechaFin)
                      ->where('fecha_fin', '>', $fechaInicio);
            })->exists();

        if ($ocupada) {
            return response()->json(['message' => 'El nuevo horario se cruza con otra reserva'], 400);
        }

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
