<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('modelos', function (Blueprint $table) {
            $table->smallIncrements('id_modelo');
            $table->tinyInteger('id_marca')->unsigned();
            $table->string('nombre_modelo', 100);

            $table->foreign('id_marca')
                  ->references('id_marca')
                  ->on('marcas')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unique(['id_marca', 'nombre_modelo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('modelos');
    }
};
