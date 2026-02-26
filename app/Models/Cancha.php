<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancha extends Model
{
    // Esto es lo que evita errores de "Mass Assignment"
    protected $fillable = [
        'nombre',
        'tipo_deporte',
        'precio_por_hora',
        'imagen',
        'esta_activa'
    ];
}
