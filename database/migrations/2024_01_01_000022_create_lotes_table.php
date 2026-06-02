<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->string('numero_lote', 50);
            $table->date('fecha_fabricacion');
            $table->date('fecha_vencimiento');
            $table->timestamps();

            $table->unique(['producto_id', 'numero_lote']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lotes');
    }
};
