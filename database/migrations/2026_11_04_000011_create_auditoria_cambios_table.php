<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_cambios', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->string('tabla', 64);
            $table->string('id_registro', 64);
            $table->enum('operacion', ['INSERT', 'UPDATE', 'DELETE']);
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->dateTime('fecha')->useCurrent();

            $table->index(['tabla', 'id_registro']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_cambios');
    }
};
