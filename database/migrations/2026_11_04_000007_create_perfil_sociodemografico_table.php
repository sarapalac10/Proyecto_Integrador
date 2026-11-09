<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil_sociodemografico', function (Blueprint $table) {
            $table->unsignedInteger('id_estudiante')->primary();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo', 15)->nullable();
            $table->unsignedTinyInteger('estrato')->nullable();
            $table->string('municipio_residencia', 80)->nullable();
            $table->boolean('trabaja')->nullable()->default(false);

            $table->foreign('id_estudiante')
                ->references('id_estudiante')->on('estudiantes')
                ->restrictOnDelete();
        });

        DB::statement(
            'ALTER TABLE perfil_sociodemografico ADD CONSTRAINT chk_perfil_estrato '
            . 'CHECK (estrato BETWEEN 1 AND 6)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_sociodemografico');
    }
};
