<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna coordinador_id a users para definir qué docentes
     * coordina cada Coordinador DAC (permite restringir la gestión de horarios).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('coordinador_id')
                ->nullable()
                ->after('estado')
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Coordinador DAC responsable de este docente');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['coordinador_id']);
            $table->dropColumn('coordinador_id');
        });
    }
};
