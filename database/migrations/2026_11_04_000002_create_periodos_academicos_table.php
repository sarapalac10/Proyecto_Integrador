<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->smallIncrements('id_periodo');
            $table->string('codigo', 7)->unique();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_limite_cancelacion');
        });

        DB::statement(
            'ALTER TABLE periodos_academicos ADD CONSTRAINT chk_periodos_fechas '
            . 'CHECK (fecha_inicio < fecha_limite_cancelacion AND fecha_limite_cancelacion <= fecha_fin)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_academicos');
    }
};
