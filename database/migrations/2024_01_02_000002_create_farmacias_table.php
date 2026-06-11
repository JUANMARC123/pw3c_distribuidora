<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('farmacias', function (Blueprint $table) {
            $table->id('id_farmacia');
            $table->string('nombre', 150);
            $table->text('direccion');
            $table->string('telefono', 20);
            $table->string('email', 180)->nullable()->unique();
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('farmacias');
    }
};
