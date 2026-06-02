<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_ruta', 50)->unique();
            $table->date('fecha');
            $table->decimal('distancia_total', 8, 2)->default(0);
            $table->integer('tiempo_estimado')->default(0);
            $table->string('estado', 50)->default('planificada');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rutas');
    }
};
