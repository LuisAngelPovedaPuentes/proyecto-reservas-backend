<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Cancha;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    public static function middleware(): array
    {
        return [
            'auth:api',
        ];
    }

    public function index()
    {
        $user = auth()->user();

        // AJUSTE: Ordenamos por fecha_inicio de forma ASCENDENTE (asc)
        // para que la más cercana aparezca primero.
        $query = Reserva::with('cancha')
                    ->orderByRaw("CASE WHEN estado = 'Cancelada' THEN 1 ELSE 0 END ASC")
                    ->orderBy('fecha_inicio', 'asc');

        if ($user->role === 'admin') {
            return response()->json($query->get(), 200);
        }

        return response()->json($query->where('user_id', $user->id)->get(), 200);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'cancha_id'    => 'required|exists:canchas,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
            'user_id'      => 'nullable|exists:users,id',
            'nombre_manual'=> 'nullable|string|max:255'
        ]);

        $cancha = Cancha::find($request->cancha_id);
        $inicio = new \DateTime($request->fecha_inicio);
        $fin = new \DateTime($request->fecha_fin);
        $ahora = new \DateTime();

        if ((int)$inicio->format('H') < 8 || (int)$fin->format('H') > 22) {
            return response()->json(['message' => 'El centro deportivo solo atiende de 08:00 AM a 10:00 PM'], 400);
        }

        if ($inicio < $ahora) {
            return response()->json(['message' => 'No puedes realizar una reserva para una fecha u hora que ya pasó'], 400);
        }

        $ocupada = Reserva::where('cancha_id', $request->cancha_id)
            ->where('estado', '!=', 'Cancelada')
            ->where(function ($query) use ($request) {
                $query->where('fecha_inicio', '<', $request->fecha_fin)
                      ->where('fecha_fin', '>', $request->fecha_inicio);
            })->exists();

        if ($ocupada) {
            return response()->json(['message' => 'Lo sentimos, esta cancha ya está reservada en ese horario'], 400);
        }

        if ($user->role === 'admin') {
            if ($request->filled('user_id')) {
                $targetUser = User::find($request->user_id);
                $finalUserId = $targetUser->id;
                $finalUserName = $targetUser->name;
            } else {
                $finalUserId = null;
                $finalUserName = $request->nombre_manual ?? 'Cliente General';
            }
        } else {
            $finalUserId = $user->id;
            $finalUserName = $user->name;
        }

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

    public function update(Request $request, $id)
    {
        $reserva = Reserva::find($id);
        $user = auth()->user();

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        if ($user->role !== 'admin' && $reserva->user_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso para modificar esta reserva'], 403);
        }

        $validated = $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after:fecha_inicio',
            'estado'       => 'nullable|string|in:confirmada,Cancelada,completada'
        ]);

        if ($request->has('estado') && !$request->has('fecha_inicio')) {
            $reserva->update(['estado' => $request->estado]);
            return response()->json(['message' => 'Estado de la reserva actualizado', 'reserva' => $reserva], 200);
        }

        $fechaInicio = $request->fecha_inicio ?? $reserva->fecha_inicio;
        $fechaFin = $request->fecha_fin ?? $reserva->fecha_fin;

        $cancha = Cancha::find($reserva->cancha_id);
        $inicio = new \DateTime($fechaInicio);
        $fin = new \DateTime($fechaFin);

        $diferencia = $inicio->diff($fin);
        $horas = $diferencia->h + ($diferencia->i / 60) + ($diferencia->days * 24);
        $totalCalculado = $horas * $cancha->precio_por_hora;

        $ocupada = Reserva::where('cancha_id', $reserva->cancha_id)
            ->where('id', '!=', $id)
            ->where('estado', '!=', 'Cancelada')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<', $fechaFin)
                      ->where('fecha_fin', '>', $fechaInicio);
            })->exists();

        if ($ocupada) {
            return response()->json(['message' => 'El nuevo horario se cruza con otra reserva'], 400);
        }

        $reserva->update([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
            'total_pago'   => $totalCalculado,
            'estado'       => $request->estado ?? $reserva->estado
        ]);

        return response()->json($reserva->load('cancha'), 200);
    }

    public function destroy($id)
    {
        $reserva = Reserva::find($id);
        $user = auth()->user();

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Acceso denegado: Solo el administrador puede eliminar'], 403);
        }

        $reserva->delete();
        return response()->json(['message' => 'Reserva eliminada con éxito'], 200);
    }

    public function reservasPorCancha($cancha_id)
    {
        return response()->json(Reserva::where('cancha_id', $cancha_id)->orderBy('fecha_inicio', 'asc')->get(), 200);
    }

    public function listarUsuarios() {
        return response()->json(User::select('id', 'name', 'email')->get(), 200);
    }
}
