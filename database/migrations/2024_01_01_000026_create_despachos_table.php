<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmacia_id')->constrained('farmacias')->onDelete('restrict');
            $table->foreignId('repartidor_id')->constrained('repartidores')->onDelete('restrict');
            $table->foreignId('ruta_id')->constrained('rutas')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('codigo_despacho', 50)->unique();
            $table->dateTime('fecha_salida');
            $table->dateTime('fecha_entrega')->nullable();
            $table->string('estado', 50)->default('pendiente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('despachos');
    }
};
