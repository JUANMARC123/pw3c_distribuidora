<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('acciones', function (Blueprint $table) {
            $table->tinyIncrements('id_accion');
            $table->string('nombre', 50)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acciones');
    }
};
