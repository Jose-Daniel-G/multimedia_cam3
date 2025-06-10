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
        Schema::create('organismos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('depe_codi');
            $table->string('depe_nomb', 255); 
            // $table->string('areaTrabajo')->nullable();
            // $table->string('areaPublicacion')->nullable();
            
            // Foreign keys
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organismos');
    }
};
