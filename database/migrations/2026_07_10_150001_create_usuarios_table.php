<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigIncrements('id_usuario');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('telefono')->nullable();
            $table->enum('estado', ['activo', 'bloqueado'])->default('activo');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
};
