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
            Schema::table('reservas', function (Blueprint $table) {
                // Creamos la columna user_id que apunta a la tabla users
                // 'constrained' asegura que no se cree una reserva con un usuario que no existe
                // 'onDelete(cascade)' significa que si borras al usuario, se borran sus reservas (integridad)
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            });
        }

        public function down(): void
        {
            Schema::table('reservas', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
};
