<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usuario_roles', function (Blueprint $table) {
            $table->id('id_usuario_rol');
            $table->unsignedBigInteger('id_usuario');
            $table->tinyInteger('id_rol')->unsigned();

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_rol')
                  ->references('id_rol')
                  ->on('roles')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_usuario', 'id_rol']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuario_roles');
    }
};
