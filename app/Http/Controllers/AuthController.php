<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <--- ESTA ES LA LÍNEA QUE FALTA
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // 1. REGISTRO DE NUEVOS USUARIOS
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'cliente', // Por seguridad, siempre nacen como clientes
        ]);

        return response()->json([
            'message' => 'Usuario creado con éxito',
            'user'    => $user
        ], 201);
    }

    // 2. INICIO DE SESIÓN (LOGIN)
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    // Intentamos autenticar con JWT
    if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['message' => 'Credenciales incorrectas'], 401);
    }

    // Usamos JWTAuth::user() directamente para evitar errores del editor
    /** @var \App\Models\User $user */
    $user = JWTAuth::user();

    return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]
    ]);
}
}
