<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CanchaSeeder::class);

        // 👤 Administrador (Con método directo)
        if (!User::where('email', 'admin@gmail.com')->exists()) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]);
        }

        // 👥 Cliente (Con método directo - AQUÍ FORZAMOS LA CREACIÓN)
        if (!User::where('email', 'cliente@gmail.com')->exists()) {
            User::create([
                'name' => 'Cliente General',
                'email' => 'cliente@gmail.com',
                'password' => Hash::make('cliente123'),
                'role' => 'cliente',
            ]);
        }
    }
}
