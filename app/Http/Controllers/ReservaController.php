<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Cancha;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    // ELIMINA EL __construct() VIEJO Y PON ESTO:
    public static function middleware(): array
    {
        return [
            'auth:api',
        ];
    }

    /**
     * LISTAR RESERVAS
     * Admin: Ve todas las reservas del sistema con datos del cliente.
     * Cliente: Solo ve sus propias reservas.
     */
    public function index()
{
    $user = auth()->user();

    if ($user->role === 'admin') {
        // Trae todas las reservas con los datos de la cancha asociada
        return response()->json(Reserva::with('cancha')->get(), 200);
    }

    // Trae solo las reservas del usuario que tiene la sesión iniciada
    return response()->json(Reserva::where('user_id', $user->id)->with('cancha')->get(), 200);
}
    /**
     * CREAR RESERVA
     * Admin: Puede enviar un 'user_id' para reservar a nombre de un cliente.
     * Cliente: Siempre se le asigna su propio ID automáticamente.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'cancha_id'    => 'required|exists:canchas,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
            'user_id'      => 'nullable|exists:users,id' // Solo lo procesaremos si es Admin
        ]);

        $cancha = Cancha::find($request->cancha_id);
        $inicio = new \DateTime($request->fecha_inicio);
        $fin = new \DateTime($request->fecha_fin);
        $ahora = new \DateTime();

        // 1. VALIDACIÓN: Horario de atención (8 AM - 10 PM)
        if ((int)$inicio->format('H') < 8 || (int)$fin->format('H') > 22) {
            return response()->json(['message' => 'El centro deportivo solo atiende de 08:00 AM a 10:00 PM'], 400);
        }

        // 2. VALIDACIÓN: No reservar en el pasado
        if ($inicio < $ahora) {
            return response()->json(['message' => 'No puedes realizar una reserva para una fecha u hora que ya pasó'], 400);
        }

        // 3. VALIDACIÓN: Disponibilidad (Evitar choques)
        $ocupada = Reserva::where('cancha_id', $request->cancha_id)
            ->where(function ($query) use ($request) {
                $query->where('fecha_inicio', '<', $request->fecha_fin)
                      ->where('fecha_fin', '>', $request->fecha_inicio);
            })->exists();

        if ($ocupada) {
            return response()->json(['message' => 'Lo sentimos, esta cancha ya está reservada en ese horario'], 400);
        }

        // 4. LÓGICA DE ASIGNACIÓN DE USUARIO (Punto 2)
        // Si es admin y mandó un user_id, buscamos a ese cliente. Si no, es el admin mismo.
        if ($user->role === 'admin' && $request->filled('user_id')) {
            $targetUser = User::find($request->user_id);
            $finalUserId = $targetUser->id;
            $finalUserName = $targetUser->name;
        } else {
            // Si es cliente, ignoramos cualquier user_id enviado por seguridad
            $finalUserId = $user->id;
            $finalUserName = $user->name;
        }

        // 5. CÁLCULO DE TOTAL
        $diferencia = $inicio->diff($fin);
        $horas = $diferencia->h + ($diferencia->i / 60) + ($diferencia->days * 24);
        $totalCalculado = $horas * $cancha->precio_por_hora;

        $reserva = Reserva::create([
            'cancha_id'      => $request->cancha_id,
            'user_id'        => $finalUserId,
            'nombre_cliente' => $finalUserName,
            'fecha_inicio'   => $request->fecha_inicio,
            'fecha_fin'      => $request->fecha_fin,
            'total_pago'     => $totalCalculado,
            'estado'         => 'confirmada'
        ]);

        return response()->json($reserva->load(['cancha', 'user']), 201);
    }

    /**
     * ACTUALIZAR RESERVA
     * Admin: Modifica cualquier reserva.
     * Cliente: Solo la suya (Punto 2).
     */
    public function update(Request $request, $id)
    {
        $reserva = Reserva::find($id);
        $user = auth()->user();

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        // SEGURIDAD: Un cliente solo puede editar su propia reserva
        if ($user->role !== 'admin' && $reserva->user_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso para modificar esta reserva'], 403);
        }

        $validated = $request->validate([
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date|after:fecha_inicio',
        ]);

        $fechaInicio = $request->fecha_inicio ?? $reserva->fecha_inicio;
        $fechaFin = $request->fecha_fin ?? $reserva->fecha_fin;

        // Recalcular precio
        $cancha = Cancha::find($reserva->cancha_id);
        $inicio = new \DateTime($fechaInicio);
        $fin = new \DateTime($fechaFin);
        $horas = $inicio->diff($fin)->h + ($inicio->diff($fin)->i / 60) + ($inicio->diff($fin)->days * 24);
        $totalCalculado = $horas * $cancha->precio_por_hora;

        // Disponibilidad excluyendo la actual
        $ocupada = Reserva::where('cancha_id', $reserva->cancha_id)
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

    /**
     * ELIMINAR (CANCELAR) RESERVA
     * Solo el Admin puede eliminar físicamente.
     * (Punto 2: El cliente solo puede modificar/cancelar estado, pero no borrar)
     */
    public function destroy($id)
    {
        $reserva = Reserva::find($id);
        $user = auth()->user();

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        // SEGURIDAD REFORZADA: Solo el Admin borra de la DB
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Acceso denegado: Solo el administrador puede eliminar registros de reserva'], 403);
        }

        $reserva->delete();
        return response()->json(['message' => 'Reserva eliminada del sistema con éxito'], 200);
    }

    public function reservasPorCancha($cancha_id)
    {
        return response()->json(Reserva::where('cancha_id', $cancha_id)->orderBy('fecha_inicio', 'asc')->get(), 200);
    }
}
