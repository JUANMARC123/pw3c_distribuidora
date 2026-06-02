<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ubicaciones_gps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repartidor_id')->constrained('repartidores')->onDelete('cascade');
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->decimal('velocidad', 6, 2)->default(0);
            $table->dateTime('fecha_hora');
            $table->timestamps();

            $table->index(['repartidor_id', 'fecha_hora']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ubicaciones_gps');
    }
};
