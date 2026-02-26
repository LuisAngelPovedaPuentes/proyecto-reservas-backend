<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use Illuminate\Http\Request;

class CanchaController extends Controller
{
    // 1. LISTAR: Muestra todas las canchas en el panel de Alejandra
    public function index()
    {
        return response()->json(Cancha::all(), 200);
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

    // 4. ACTUALIZAR: Modificar nombre, precio o estado (Semana 5)
    public function update(Request $request, $id)
    {
        $cancha = Cancha::find($id);
        if (!$cancha) {
            return response()->json(['message' => 'Error: Cancha no encontrada para actualizar'], 404);
        }

        // Validamos solo lo que se envía para permitir actualizaciones parciales
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

    // 5. ELIMINAR: Borrar permanentemente el escenario (Semana 5)
    public function destroy($id)
    {
        $cancha = Cancha::find($id);
        if (!$cancha) {
            return response()->json(['message' => 'Error: La cancha ya no existe'], 404);
        }

        $cancha->delete();
        return response()->json(['message' => 'Cancha eliminada del sistema'], 200);
    }
}
