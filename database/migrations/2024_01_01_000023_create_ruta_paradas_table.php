<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ruta_paradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained('rutas')->onDelete('cascade');
            $table->foreignId('farmacia_id')->constrained('farmacias')->onDelete('cascade');
            $table->integer('orden_parada');
            $table->timestamps();

            $table->unique(['ruta_id', 'farmacia_id', 'orden_parada']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ruta_paradas');
    }
};
