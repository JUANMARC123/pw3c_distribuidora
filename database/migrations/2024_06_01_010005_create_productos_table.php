<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->string('codigo_producto', 50)->unique();
            $table->string('nombre_producto', 200);
            $table->text('descripcion')->nullable();
            $table->tinyInteger('id_categoria')->unsigned();
            $table->smallInteger('id_laboratorio')->unsigned();
            $table->tinyInteger('id_presentacion')->unsigned();
            $table->tinyInteger('id_unidad_medida')->unsigned();
            $table->string('concentracion', 100)->nullable();
            $table->decimal('precio_unitario', 10, 2);
            $table->boolean('requiere_receta')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_categoria')
                  ->references('id_categoria')
                  ->on('categorias')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_laboratorio')
                  ->references('id_laboratorio')
                  ->on('laboratorios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_presentacion')
                  ->references('id_presentacion')
                  ->on('presentaciones')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_unidad_medida')
                  ->references('id_unidad_medida')
                  ->on('unidades_medida')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};
