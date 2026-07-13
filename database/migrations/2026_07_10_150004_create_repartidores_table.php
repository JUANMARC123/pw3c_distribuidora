<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('repartidores', function (Blueprint $table) {
            $table->bigIncrements('id_repartidor');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('ci')->unique();
            $table->enum('categoria_licencia', ['A', 'B', 'C'])->default('B');
            $table->enum('estado', ['activo', 'inactivo', 'en_ruta'])->default('activo');
            $table->string('firebase_uid')->unique()->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repartidores');
    }
};
