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
        Schema::create('notificaciones_avisos', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->integer('publi_notificacion');
            $table->unsignedBigInteger('fk_idorganismo');
            $table->unsignedBigInteger('fk_idusuario');
            $table->unsignedBigInteger('fk_idtp_imp');
            $table->unsignedBigInteger('fk_tipo_plantilla');
            $table->text('ruta_archivos');
            $table->string('nombre_ciudadano', 100);
            $table->string('cedula_identificacion', 100);
            $table->date('fecha_publicacion');
            $table->date('fecha_desfijacion');
            $table->string('id_predio', 100)->nullable();
            $table->string('objeto_contrato', 100)->nullable();
            $table->string('num_predial', 100)->nullable();
            $table->unsignedBigInteger('fk_publi_noti')->nullable();
            $table->unsignedBigInteger('fk_tipo_acto_tramite')->nullable();
            $table->unsignedBigInteger('fk_estado_publicacion')->nullable();
            $table->unsignedBigInteger('fk_tipo_causa_devolucion')->nullable();
            $table->json('json_plantilla');
            $table->unsignedBigInteger('id_estado_auditoria');

            $table->foreign('fk_idorganismo')->references('id')->on('organismos');
            $table->foreign('fk_idusuario')->references('id')->on('users');
            $table->foreign('fk_idtp_imp')->references('id_tp_imp')->on('tipo_impuesto');
            $table->foreign('fk_tipo_plantilla')->references('id_tipo_plantilla')->on('tipo_plantilla');
            $table->foreign('fk_tipo_acto_tramite')->references('id_tipo_acto_tramite')->on('tipo_acto_tramite');
            $table->foreign('fk_estado_publicacion')->references('id_tipo_ind_pub')->on('tipo_estado_publicacion');
            $table->foreign('fk_tipo_causa_devolucion')->references('id_tipo_causa_devoluciones')->on('tipo_causa_devolucion');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_avisos');
    }
};
