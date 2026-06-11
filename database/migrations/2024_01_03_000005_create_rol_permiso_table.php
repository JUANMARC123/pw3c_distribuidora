<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->id('id_rol_permiso');
            $table->tinyInteger('id_rol')->unsigned();
            $table->unsignedBigInteger('id_permiso');

            $table->foreign('id_rol')
                  ->references('id_rol')
                  ->on('roles')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_permiso')
                  ->references('id_permiso')
                  ->on('permisos')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unique(['id_rol', 'id_permiso']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rol_permiso');
    }
};
