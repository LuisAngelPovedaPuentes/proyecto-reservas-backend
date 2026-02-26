<?php

namespace App\Http\Controllers;

use App\Models\Cancha; // IMPORTANTE: Asegúrate de que esta línea esté aquí
use Illuminate\Http\Request;

class CanchaController extends Controller
{
    // Listar todas las canchas (Para que Angular las muestre después)
    public function index()
    {
        return response()->json(Cancha::all(), 200);
    }

    // Guardar una nueva cancha (Lo que ya probaste)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_deporte' => 'required|string',
            'precio_por_hora' => 'required|numeric',
        ]);

        $cancha = Cancha::create($validated);
        return response()->json($cancha, 201);
    }

    // Mostrar una sola cancha por ID
    public function show($id)
    {
        $cancha = Cancha::find($id);
        if (!$cancha) return response()->json(['message' => 'No encontrada'], 404);
        return response()->json($cancha, 200);
    }

    // Actualizar datos de una cancha (Semana 5: Gestión de canchas)
    public function update(Request $request, $id)
    {
        $cancha = Cancha::find($id);
        if (!$cancha) return response()->json(['message' => 'No encontrada'], 404);

        $cancha->update($request->all());
        return response()->json($cancha, 200);
    }

    // Eliminar o dar de baja (Semana 5: Dar de baja escenarios)
    public function destroy($id)
    {
        $cancha = Cancha::find($id);
        if (!$cancha) return response()->json(['message' => 'No encontrada'], 404);

        $cancha->delete();
        return response()->json(['message' => 'Cancha eliminada'], 200);
    }
}
