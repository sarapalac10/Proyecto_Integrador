<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prerrequisitos', function (Blueprint $table) {
            $table->unsignedInteger('id_asignatura');
            $table->unsignedInteger('id_asignatura_requisito');

            $table->primary(['id_asignatura', 'id_asignatura_requisito']);

            $table->foreign('id_asignatura')
                ->references('id_asignatura')->on('asignaturas')
                ->restrictOnDelete();
            $table->foreign('id_asignatura_requisito')
                ->references('id_asignatura')->on('asignaturas')
                ->restrictOnDelete();
        });

        DB::statement(
            'ALTER TABLE prerrequisitos ADD CONSTRAINT chk_prerrequisitos_distinto '
            . 'CHECK (id_asignatura <> id_asignatura_requisito)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('prerrequisitos');
    }
};
