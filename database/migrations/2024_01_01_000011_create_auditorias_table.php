<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('accion', 150);
            $table->string('tabla_afectada', 100);
            $table->unsignedBigInteger('registro_id');
            $table->timestamps();

            $table->index(['tabla_afectada', 'registro_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditorias');
    }
};
