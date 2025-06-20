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
        Schema::create('publi_noti_hacienda', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_pdf');
            $table->string('folder');
            $table->integer('plantilla');
            $table->string('username');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publi_noti_hacienda');
    }
};
