<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('reservas', function (Blueprint $table) {
        $table->id();
        // Relación con la cancha (ID de la cancha que reservan)
        $table->foreignId('cancha_id')->constrained('canchas')->onDelete('cascade');

        // Datos de la reserva
        $table->string('nombre_cliente');
        $table->dateTime('fecha_inicio'); // Ejemplo: 2026-02-26 14:00:00
        $table->dateTime('fecha_fin');    // Ejemplo: 2026-02-26 15:00:00
        $table->decimal('total_pago', 10, 2);
        $table->enum('estado', ['pendiente', 'confirmada', 'cancelada'])->default('pendiente');

        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
