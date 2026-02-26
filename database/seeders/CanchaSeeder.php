<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cancha; // Importamos el modelo

class CanchaSeeder extends Seeder
{
    public function run(): void
    {
        // Creamos varias canchas de una vez
        Cancha::create([
            'nombre' => 'Cancha Sintética Central',
            'tipo_deporte' => 'Fútbol 5',
            'precio_por_hora' => 60000,
            'esta_activa' => true
        ]);

        Cancha::create([
            'nombre' => 'Polideportivo Norte',
            'tipo_deporte' => 'Baloncesto',
            'precio_por_hora' => 45000,
            'esta_activa' => true
        ]);

        Cancha::create([
            'nombre' => 'Cancha De Futbol De Salon',
            'tipo_deporte' => 'Micro Futbol',
            'precio_por_hora' => 80000,
            'esta_activa' => true
        ]);

        Cancha::create([
            'nombre' => 'Campo Nuñez',
            'tipo_deporte' => 'Micro Futbol',
            'precio_por_hora' => 80000,
            'esta_activa' => true
        ]);

        Cancha::create([
            'nombre' => 'Coliseo',
            'tipo_deporte' => 'Futbol de Salon',
            'precio_por_hora' => 90000,
            'esta_activa' => true
        ]);
    }
}
