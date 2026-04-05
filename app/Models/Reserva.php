<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cancha;

class Reserva extends Model
{
    protected $fillable = [
        'cancha_id',     // <--- ESTA ES LA QUE FALTA EN TU IMAGEN
        'nombre_cliente',
        'fecha_inicio',
        'fecha_fin',
        'total_pago',
        'estado'
    ];

    public function cancha()
    {
        return $this->belongsTo(Cancha::class);
    }
}
