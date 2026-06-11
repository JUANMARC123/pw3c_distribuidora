<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 180)->unique();
            $table->string('password_hash', 255);
            $table->string('telefono', 20);
            $table->tinyInteger('id_estado_usuario')->unsigned();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_bloqueo')->nullable();
            $table->timestamp('ultimo_acceso')->nullable();
            $table->rememberToken();

            $table->foreign('id_estado_usuario')
                  ->references('id_estado_usuario')
                  ->on('estados_usuario')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
};
