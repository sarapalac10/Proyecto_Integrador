<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programas', function (Blueprint $table) {
            $table->smallIncrements('id_programa');
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 120);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programas');
    }
};
