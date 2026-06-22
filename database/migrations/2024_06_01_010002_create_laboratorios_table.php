<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->smallIncrements('id_laboratorio');
            $table->string('nombre_laboratorio', 150)->unique();
            $table->string('telefono', 20)->nullable();
            $table->text('direccion')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('laboratorios');
    }
};
