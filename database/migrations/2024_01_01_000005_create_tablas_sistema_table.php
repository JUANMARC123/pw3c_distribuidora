<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tablas_sistema', function (Blueprint $table) {
            $table->tinyIncrements('id_tabla');
            $table->string('nombre', 100)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tablas_sistema');
    }
};
