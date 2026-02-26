<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
    Schema::create('reservas', function (Blueprint $table) {
        $table->id(); // Llave Primaria [cite: 3]

        // LLAVES FORÁNEAS: Conectan usuarios y canchas [cite: 3]
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('cancha_id')->constrained('canchas')->onDelete('cascade');

        $table->date('fecha'); // [cite: 3]
        $table->time('hora_inicio'); // [cite: 3]
        $table->time('hora_fin'); // [cite: 3]
        $table->string('estado')->default('confirmada'); // [cite: 3]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
