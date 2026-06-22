<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id('id_orden_compra');
            $table->string('codigo_orden', 50)->unique();
            $table->unsignedBigInteger('id_proveedor');
            $table->unsignedBigInteger('id_usuario');
            $table->tinyInteger('id_estado_orden_compra')->unsigned();
            $table->date('fecha_orden')->useCurrent();
            $table->date('fecha_estimada_recibido')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_proveedor')
                  ->references('id_proveedor')
                  ->on('proveedores')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_orden_compra')
                  ->references('id_estado_orden_compra')
                  ->on('estados_orden_compra')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ordenes_compra');
    }
};
