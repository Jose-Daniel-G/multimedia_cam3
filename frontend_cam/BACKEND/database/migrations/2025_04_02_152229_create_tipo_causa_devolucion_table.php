<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipo_causa_devolucion', function (Blueprint $table) {
            $table->id('id_tipo_causa_devoluciones');
            $table->string('nombre_causa_devolucion', 100)->collation('utf8mb4_spanish_ci');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_causa_devolucion');
    }
};
