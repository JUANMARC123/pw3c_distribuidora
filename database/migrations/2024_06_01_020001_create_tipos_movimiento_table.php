<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipos_movimiento', function (Blueprint $table) {
            $table->tinyIncrements('id_tipo_movimiento');
            $table->string('nombre_tipo', 50)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipos_movimiento');
    }
};
