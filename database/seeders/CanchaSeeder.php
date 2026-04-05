<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cancha; // Importamos el modelo

class CanchaSeeder extends Seeder
{
    public function run(): void
        {
            // Datos exactos de tu tabla actual
            $canchas = [
                [
                    'nombre' => 'Cancha Sintética Central',
                    'tipo_deporte' => 'Fútbol 5',
                    'precio_por_hora' => 60000,
                    'esta_activa' => true
                ],
                [
                    'nombre' => 'Cancha De Futbol De Salón',
                    'tipo_deporte' => 'Micro Fútbol',
                    'precio_por_hora' => 80000,
                    'esta_activa' => true
                ],
                [
                    'nombre' => 'Polideportivo Norte',
                    'tipo_deporte' => 'Baloncesto',
                    'precio_por_hora' => 45000,
                    'esta_activa' => true
                ],
                [
                    'nombre' => 'Campo Nuñez',
                    'tipo_deporte' => 'Micro Futbol',
                    'precio_por_hora' => 80000,
                    'esta_activa' => true
                ],
                [
                    'nombre' => 'Coliseo',
                    'tipo_deporte' => 'Futbol de Salon',
                    'precio_por_hora' => 90000,
                    'esta_activa' => true
                ],
            ];

            foreach ($canchas as $cancha) {
                \App\Models\Cancha::create($cancha);
            }
        }
}
