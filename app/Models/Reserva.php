<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cancha; // Importante para la relación

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

    // Relación para saber a qué cancha pertenece la reserva
    public function cancha()
    {
        return $this->belongsTo(Cancha::class);
    }
}
