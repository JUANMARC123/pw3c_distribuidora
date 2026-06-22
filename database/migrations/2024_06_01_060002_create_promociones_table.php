<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promociones', function (Blueprint $table) {
            $table->id('id_promocion');
            $table->string('nombre_promocion', 200);
            $table->text('descripcion')->nullable();
            $table->unsignedTinyInteger('id_tipo_promocion');
            $table->decimal('descuento', 10, 2);
            $table->boolean('es_porcentual')->default(true);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_usuario');

            $table->foreign('id_tipo_promocion')
                  ->references('id_tipo_promocion')
                  ->on('tipos_promocion')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('promociones');
    }
};
