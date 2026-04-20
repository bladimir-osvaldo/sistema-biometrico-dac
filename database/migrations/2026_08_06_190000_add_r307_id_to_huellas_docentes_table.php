<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el ID numérico del sensor R307 a las huellas docentes.
     * El sensor R307 guarda las plantillas en su memoria interna con un
     * ID numérico (1 a 300). Este ID es el que envía el ESP32 al servidor.
     */
    public function up(): void
    {
        Schema::table('huellas_docentes', function (Blueprint $table) {
            $table->integer('r307_id')->nullable()->after('dedo_numero')->unique()->comment('ID numérico interno del sensor R307');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('huellas_docentes', function (Blueprint $table) {
            $table->dropColumn('r307_id');
        });
    }
};
