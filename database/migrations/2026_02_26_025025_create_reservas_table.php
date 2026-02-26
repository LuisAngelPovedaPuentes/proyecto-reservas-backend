<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            // Relación con canchas
            $table->foreignId('cancha_id')->constrained('canchas')->onDelete('cascade');

            $table->string('nombre_cliente');
            $table->dateTime('fecha_inicio'); // Esta es la que te falta según el error
            $table->dateTime('fecha_fin');    // Esta también es necesaria
            $table->decimal('total_pago', 10, 2);
            $table->string('estado')->default('confirmada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
