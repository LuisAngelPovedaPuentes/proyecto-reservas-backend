<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use Illuminate\Http\Request;
use App\Models\Reserva;

class CanchaController extends Controller
{
    // 1. LISTAR: Devuelve todas las canchas de PostgreSQL
    public function index()
    {
        $canchas = Cancha::all();
        return response()->json($canchas, 200);
    }

    // 2. CREAR: Guarda una nueva cancha en la DB
    public function store(Request $request)
    {
        // Validamos que los datos lleguen correctamente desde Angular
        $validated = $request->validate([
            'nombre'          => 'required|string|max:255',
            'tipo_deporte'    => 'required|string',
            'precio_por_hora' => 'required|numeric|min:0',
            'imagen'          => 'nullable|string',
            'esta_activa'     => 'boolean'
        ]);

        // Si "esta_activa" no viene en el request, le ponemos true por defecto
        if (!isset($validated['esta_activa'])) {
            $validated['esta_activa'] = true;
        }

        $cancha = Cancha::create($validated);

        return response()->json([
            'message' => 'Cancha creada con éxito en la base de datos',
            'data' => $cancha
        ], 201);
    }

    // 3. MOSTRAR: Ver detalles de una sola cancha por ID
    public function show($id)
    {
        $cancha = Cancha::find($id);

        if (!$cancha) {
            return response()->json(['message' => 'Error: La cancha no existe'], 404);
        }

        return response()->json($cancha, 200);
    }

    // 4. ACTUALIZAR: Modificar datos desde el panel de Admin
    public function update(Request $request, $id)
    {
        $cancha = Cancha::find($id);

        if (!$cancha) {
            return response()->json(['message' => 'Error: Cancha no encontrada'], 404);
        }

        // 'sometimes' permite que solo se validen los campos que el usuario envió
        $validated = $request->validate([
            'nombre'          => 'sometimes|string|max:255',
            'tipo_deporte'    => 'sometimes|string',
            'precio_por_hora' => 'sometimes|numeric|min:0',
            'imagen'          => 'nullable|string',
            'esta_activa'     => 'sometimes|boolean'
        ]);

        $cancha->update($validated);

        return response()->json([
            'message' => 'Cancha actualizada correctamente',
            'data' => $cancha
        ], 200);
    }

    // 5. ELIMINAR: Borrar la cancha definitivamente
    public function destroy($id)
    {
        $cancha = Cancha::find($id);

        if (!$cancha) {
            return response()->json(['message' => 'Error: La cancha no existe'], 404);
        }

        // OPCIONAL: Lógica de seguridad para evitar borrar canchas con reservas activas
        // Si tienes una relación en el modelo Cancha llamada 'reservas'
        // if ($cancha->reservas()->exists()) {
        //     return response()->json(['message' => 'No se puede borrar: Esta cancha tiene reservas registradas'], 400);
        // }

        $cancha->delete();

        return response()->json([
            'message' => 'Cancha eliminada del sistema correctamente'
        ], 200);
    }
}
