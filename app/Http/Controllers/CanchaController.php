<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use Illuminate\Http\Request;
// Importamos Reserva por si en el futuro necesitas validar si una cancha tiene reservas antes de borrarla
use App\Models\Reserva;

class CanchaController extends Controller
{
    // 1. LISTAR: Muestra todas las canchas
    public function index()
    {
        // Es mejor práctica devolverlo así para asegurar que siempre sea un array JSON
        $canchas = Cancha::all();
        return response()->json($canchas, 200);
    }

    // 2. CREAR: Guarda una nueva cancha
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:255',
            'tipo_deporte'    => 'required|string',
            'precio_por_hora' => 'required|numeric|min:0',
            'imagen'          => 'nullable|string',
            'esta_activa'     => 'boolean'
        ]);

        $cancha = Cancha::create($validated);

        return response()->json([
            'message' => 'Cancha creada con éxito',
            'data' => $cancha
        ], 201);
    }

    // 3. MOSTRAR: Ver detalles de una sola cancha
    public function show($id)
    {
        $cancha = Cancha::find($id);

        if (!$cancha) {
            return response()->json(['message' => 'Error: La cancha no existe'], 404);
        }

        return response()->json($cancha, 200);
    }

    // 4. ACTUALIZAR: Modificar datos de la cancha
    public function update(Request $request, $id)
    {
        $cancha = Cancha::find($id);

        if (!$cancha) {
            return response()->json(['message' => 'Error: Cancha no encontrada'], 404);
        }

        $validated = $request->validate([
            'nombre'          => 'sometimes|string|max:255',
            'tipo_deporte'    => 'sometimes|string',
            'precio_por_hora' => 'sometimes|numeric|min:0',
            'imagen'          => 'nullable|string',
            'esta_activa'     => 'boolean'
        ]);

        $cancha->update($validated);

        return response()->json([
            'message' => 'Cancha actualizada correctamente',
            'data' => $cancha
        ], 200);
    }

    // 5. ELIMINAR: Borrar la cancha
    public function destroy($id)
    {
        $cancha = Cancha::find($id);

        if (!$cancha) {
            return response()->json(['message' => 'Error: La cancha no existe'], 404);
        }

        // OPCIONAL: Podrías verificar si tiene reservas antes de borrar
        // if ($cancha->reservas()->count() > 0) { ... }

        $cancha->delete();
        return response()->json(['message' => 'Cancha eliminada del sistema'], 200);
    }
}
