<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('licencias', function (Blueprint $table) {
            $table->tinyIncrements('id_licencia');
            $table->string('categoria', 20)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('licencias');
    }
};
