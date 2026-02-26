<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = [
        'cancha_id',
        'nombre_cliente',
        'fecha_inicio',
        'fecha_fin',
        'total_pago',
        'estado'
    ];
}
