<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipos_devolucion', function (Blueprint $table) {
            $table->tinyIncrements('id_tipo_devolucion');
            $table->string('nombre_tipo', 100)->unique();
        });

        Schema::create('estados_devolucion', function (Blueprint $table) {
            $table->tinyIncrements('id_estado_devolucion');
            $table->string('nombre_estado', 50)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estados_devolucion');
        Schema::dropIfExists('tipos_devolucion');
    }
};
