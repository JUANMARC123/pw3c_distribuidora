<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accesos_sistema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('modulo', 100);
            $table->string('accion', 100);
            $table->dateTime('fecha');
            $table->timestamps();

            $table->index(['user_id', 'fecha']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('accesos_sistema');
    }
};
