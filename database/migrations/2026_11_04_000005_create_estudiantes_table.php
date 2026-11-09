<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->increments('id_estudiante');
            $table->unsignedBigInteger('id_user')->unique();
            $table->unsignedSmallInteger('id_programa');
            $table->string('codigo_estudiantil', 20)->unique();
            $table->string('nombres', 80);
            $table->string('apellidos', 80);
            $table->string('tipo_documento', 5);
            $table->string('documento', 20);

            $table->unique(['tipo_documento', 'documento']);

            $table->foreign('id_user')
                ->references('id')->on('users')
                ->restrictOnDelete();
            $table->foreign('id_programa')
                ->references('id_programa')->on('programas')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
