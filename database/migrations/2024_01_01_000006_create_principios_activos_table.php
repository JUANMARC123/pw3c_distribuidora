<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('principios_activos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('principios_activos');
    }
};
