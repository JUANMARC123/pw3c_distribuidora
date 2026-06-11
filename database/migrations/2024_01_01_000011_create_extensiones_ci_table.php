<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('extensiones_ci', function (Blueprint $table) {
            $table->tinyIncrements('id_extension_ci');
            $table->string('nombre_extension', 10)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('extensiones_ci');
    }
};
