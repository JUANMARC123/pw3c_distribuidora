<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos_principios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('principio_activo_id')->constrained('principios_activos')->onDelete('cascade');
            $table->string('cantidad', 50);
            $table->timestamps();

            $table->unique(['producto_id', 'principio_activo_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos_principios');
    }
};
