<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contactos_farmacia', function (Blueprint $table) {
            $table->bigIncrements('id_contacto');
            $table->unsignedBigInteger('id_farmacia');
            $table->string('nombre_contacto');
            $table->string('cargo')->nullable();
            $table->string('telefono');
            $table->string('email')->nullable();
            $table->timestamps();

            $table->foreign('id_farmacia')
                ->references('id_farmacia')->on('farmacias')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contactos_farmacia');
    }
};
