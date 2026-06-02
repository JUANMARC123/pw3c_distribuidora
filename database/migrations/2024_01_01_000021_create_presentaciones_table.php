<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('presentaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('forma_farmaceutica_id')->constrained('formas_farmaceuticas')->onDelete('restrict');
            $table->string('descripcion', 200);
            $table->string('contenido', 100);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('presentaciones');
    }
};
