<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cancha;

class Reserva extends Model
{
    protected $fillable = [
    'cancha_id',
    'user_id',        // <--- AGREGA ESTA LÍNEA
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

    // Dentro de App\Models\Reserva.php

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
