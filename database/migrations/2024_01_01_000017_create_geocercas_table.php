<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('geocercas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre', 150);
            $table->string('tipo', 50);
            $table->json('coordenadas');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('geocercas');
    }
};
