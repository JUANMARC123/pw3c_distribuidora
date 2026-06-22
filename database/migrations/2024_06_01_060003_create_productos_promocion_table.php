<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos_promocion', function (Blueprint $table) {
            $table->id('id_producto_promocion');
            $table->unsignedBigInteger('id_promocion');
            $table->unsignedBigInteger('id_producto');
            $table->decimal('cantidad_minima', 10, 2)->default(1);

            $table->foreign('id_promocion')
                  ->references('id_promocion')
                  ->on('promociones')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_producto')
                  ->references('id_producto')
                  ->on('productos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_promocion', 'id_producto']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos_promocion');
    }
};
