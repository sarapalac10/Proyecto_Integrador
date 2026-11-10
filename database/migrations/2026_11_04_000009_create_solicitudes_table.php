<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id('id_solicitud');
            $table->unsignedBigInteger('id_inscripcion');
            $table->enum('motivo', ['SALUD', 'ECONOMICO', 'LABORAL', 'ACADEMICO', 'OTRO']);
            $table->text('justificacion');
            $table->dateTime('fecha_radicacion')->useCurrent();

            $table->foreign('id_inscripcion')
                ->references('id_inscripcion')->on('inscripciones')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
