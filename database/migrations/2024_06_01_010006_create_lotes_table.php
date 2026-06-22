<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id('id_lote');
            $table->unsignedBigInteger('id_producto');
            $table->string('codigo_lote', 50)->unique();
            $table->date('fecha_fabricacion');
            $table->date('fecha_vencimiento');
            $table->decimal('precio_compra', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_producto')
                  ->references('id_producto')
                  ->on('productos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lotes');
    }
};
