<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id('id_almacen');
            $table->unsignedBigInteger('id_farmacia');
            $table->string('nombre', 100);

            $table->unique(['id_farmacia', 'nombre']);

            $table->foreign('id_farmacia')
                  ->references('id_farmacia')
                  ->on('farmacias')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });

        Schema::create('ubicaciones_almacen', function (Blueprint $table) {
            $table->id('id_ubicacion');
            $table->unsignedBigInteger('id_almacen');
            $table->string('pasillo', 20);
            $table->string('estante', 20);

            $table->unique(['id_almacen', 'pasillo', 'estante']);

            $table->foreign('id_almacen')
                  ->references('id_almacen')
                  ->on('almacenes')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });

        Schema::table('inventario', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ubicacion')->nullable()->after('id_lote');
            $table->decimal('precio_venta', 10, 2)->nullable()->after('stock_minimo');
            $table->dateTime('fecha_actualizacion')->nullable()->after('precio_venta');

            $table->foreign('id_ubicacion')
                  ->references('id_ubicacion')
                  ->on('ubicaciones_almacen')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->dropForeign(['id_ubicacion']);
            $table->dropColumn(['id_ubicacion', 'precio_venta', 'fecha_actualizacion']);
        });

        Schema::dropIfExists('ubicaciones_almacen');
        Schema::dropIfExists('almacenes');
    }
};
