<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_asignatura');
            $table->unsignedSmallInteger('id_periodo');
            $table->enum('estado', ['CURSANDO', 'FINALIZADA', 'CANCELADA'])->default('CURSANDO');
            $table->decimal('nota_final', 2, 1)->nullable();

            $table->unique(['id_estudiante', 'id_asignatura', 'id_periodo']);
            $table->index(['id_periodo', 'estado']);

            $table->foreign('id_estudiante')
                ->references('id_estudiante')->on('estudiantes')
                ->restrictOnDelete();
            $table->foreign('id_asignatura')
                ->references('id_asignatura')->on('asignaturas')
                ->restrictOnDelete();
            $table->foreign('id_periodo')
                ->references('id_periodo')->on('periodos_academicos')
                ->restrictOnDelete();
        });

        DB::statement(
            'ALTER TABLE inscripciones ADD CONSTRAINT chk_inscripciones_nota '
            . 'CHECK (nota_final BETWEEN 0.0 AND 5.0)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
