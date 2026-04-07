<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// 1. IMPORTANTE: Importamos la interfaz de JWT
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

// 2. AGREGAMOS "implements JWTSubject" a la clase
class User extends Authenticatable implements JWTSubject
{
    use  HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Lo usaremos para diferenciar Admin de Cliente
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * 3. MÉTODO OBLIGATORIO: Identificador del Token
     * Sirve para que el token sepa a qué ID de usuario pertenece.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * 4. MÉTODO OBLIGATORIO: Datos extra del Token
     * Aquí podemos meter el "rol" para que Angular sepa si eres Admin o no.
     */
    public function getJWTCustomClaims()
{
    return [
        'role' => $this->role, // Enviamos el rol dentro del token
    ];
}
}
