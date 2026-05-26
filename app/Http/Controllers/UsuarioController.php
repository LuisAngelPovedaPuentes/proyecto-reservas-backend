<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // Listar todos los usuarios
    public function index()
    {
        return response()->json(User::select('id', 'name', 'email', 'role')->get(), 200);
    }

    // Crear un nuevo usuario (Admin o Cliente)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:admin,cliente',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password), // Encriptación directa sin tokens de correo
        ]);

        return response()->json(['message' => 'Usuario creado con éxito', 'user' => $user], 201);
    }

    // Modificar un usuario existente
    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|string|in:admin,cliente',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'Usuario actualizado con éxito', 'user' => $user], 200);
    }

    // Eliminar un usuario con restricciones críticas de seguridad
    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        // 1. Evitar que el administrador en sesión se elimine a sí mismo
        if ((int)auth()->id() === (int)$user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario'], 400);
        }

        // 2. Evitar que se elimine a cualquier otro usuario que posea el rol de administrador
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Por motivos de seguridad, no se pueden eliminar cuentas de tipo Administrador'], 400);
        }

        $user->delete();
        return response()->json(['message' => 'Usuario eliminado con éxito'], 200);
    }
}
