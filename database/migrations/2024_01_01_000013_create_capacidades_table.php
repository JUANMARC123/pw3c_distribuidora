<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('capacidades', function (Blueprint $table) {
            $table->tinyIncrements('id_capacidad');
            $table->decimal('capacidad_kg', 8, 2)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('capacidades');
    }
};
