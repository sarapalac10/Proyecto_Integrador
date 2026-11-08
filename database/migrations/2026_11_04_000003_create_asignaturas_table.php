<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaturas', function (Blueprint $table) {
            $table->increments('id_asignatura');
            $table->unsignedSmallInteger('id_programa');
            $table->string('codigo', 15)->unique();
            $table->string('nombre', 120);
            $table->unsignedTinyInteger('creditos');
            $table->unsignedTinyInteger('semestre');

            $table->foreign('id_programa')
                ->references('id_programa')->on('programas')
                ->restrictOnDelete();
        });

        DB::statement(
            'ALTER TABLE asignaturas ADD CONSTRAINT chk_asignaturas_creditos '
            . 'CHECK (creditos BETWEEN 1 AND 10)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaturas');
    }
};
