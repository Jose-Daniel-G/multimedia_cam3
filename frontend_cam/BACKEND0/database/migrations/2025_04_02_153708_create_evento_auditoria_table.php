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
        Schema::create('evento_auditoria', function (Blueprint $table) {
            $table->id('id_evento');
            $table->unsignedBigInteger('id_publi_noti');
            $table->unsignedBigInteger('idusuario');
            $table->unsignedBigInteger('id_plantilla');
            $table->integer('cont_registros');
            $table->enum('estado_auditoria', ['E', 'P', 'F']); // 'E': En Proceso, 'P': Publicado, 'F': Fallido
            $table->json('datos_adicionales');
            $table->timestamp('fecha_auditoria')->useCurrent();
            $table->timestamps();
            $table->foreign('idusuario')->references('id')->on('users');
            $table->foreign('id_plantilla')->references('id_tipo_plantilla')->on('tipo_plantilla');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_auditoria');
    }
};
