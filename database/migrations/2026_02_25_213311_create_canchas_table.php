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
    Schema::create('canchas', function (Blueprint $table) {
        $table->id(); // Llave Primaria [cite: 2]
        $table->string('nombre'); // [cite: 2]
        $table->string('tipo_deporte'); // [cite: 2]
        $table->decimal('precio_por_hora', 8, 2); // [cite: 2]
        $table->string('imagen')->nullable(); // [cite: 2]
        $table->boolean('esta_activa')->default(true); // [cite: 2]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canchas');
    }
};
