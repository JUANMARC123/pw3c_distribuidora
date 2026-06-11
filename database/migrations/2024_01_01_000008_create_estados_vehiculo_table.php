<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estados_vehiculo', function (Blueprint $table) {
            $table->tinyIncrements('id_estado_vehiculo');
            $table->string('nombre_estado', 50)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estados_vehiculo');
    }
};
