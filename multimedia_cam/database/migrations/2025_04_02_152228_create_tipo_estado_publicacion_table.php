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
        Schema::create('tipo_estado_publicacion', function (Blueprint $table) {
            $table->id('id_tipo_ind_pub');
            $table->string('nombre_estado_publicacion', 100)->collation('utf8mb4_spanish_ci');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_estado_publicacion');
    }
};
