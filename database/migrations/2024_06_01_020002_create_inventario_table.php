<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_inventario');
            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_lote')->nullable();
            $table->decimal('stock_actual', 10, 2)->default(0);
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['id_producto', 'id_lote']);

            $table->foreign('id_producto')
                  ->references('id_producto')
                  ->on('productos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_lote')
                  ->references('id_lote')
                  ->on('lotes')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventario');
    }
};
