<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id('id_permiso');
            $table->tinyInteger('id_modulo')->unsigned();
            $table->tinyInteger('id_accion')->unsigned();

            $table->foreign('id_modulo')
                  ->references('id_modulo')
                  ->on('modulos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_accion')
                  ->references('id_accion')
                  ->on('acciones')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_modulo', 'id_accion']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('permisos');
    }
};
