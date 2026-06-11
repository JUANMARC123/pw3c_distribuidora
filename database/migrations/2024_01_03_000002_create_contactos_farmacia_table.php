<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contactos_farmacia', function (Blueprint $table) {
            $table->id('id_contacto');
            $table->unsignedBigInteger('id_farmacia');
            $table->string('nombre_contacto', 150);
            $table->tinyInteger('id_cargo')->unsigned();
            $table->string('telefono', 20);
            $table->string('email', 180)->nullable();

            $table->foreign('id_farmacia')
                  ->references('id_farmacia')
                  ->on('farmacias')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_cargo')
                  ->references('id_cargo')
                  ->on('cargos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contactos_farmacia');
    }
};
