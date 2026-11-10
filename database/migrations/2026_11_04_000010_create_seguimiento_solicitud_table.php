<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimiento_solicitud', function (Blueprint $table) {
            $table->id('id_seguimiento');
            $table->unsignedBigInteger('id_solicitud');
            $table->enum('estado', ['RADICADA', 'EN_REVISION', 'APROBADA', 'RECHAZADA', 'DESISTIDA']);
            $table->unsignedBigInteger('id_user');
            $table->text('observacion')->nullable();
            $table->dateTime('fecha_evento')->useCurrent();

            $table->index(['id_solicitud', 'fecha_evento']);

            $table->foreign('id_solicitud')
                ->references('id_solicitud')->on('solicitudes')
                ->restrictOnDelete();
            $table->foreign('id_user')
                ->references('id')->on('users')
                ->restrictOnDelete();
        });

        DB::statement(
            "ALTER TABLE seguimiento_solicitud ADD CONSTRAINT chk_seguimiento_rechazo "
            . "CHECK (estado <> 'RECHAZADA' OR (observacion IS NOT NULL AND observacion <> ''))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_solicitud');
    }
};
