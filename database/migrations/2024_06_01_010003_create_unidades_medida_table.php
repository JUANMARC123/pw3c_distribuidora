<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->tinyIncrements('id_unidad_medida');
            $table->string('nombre_unidad', 50)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unidades_medida');
    }
};
