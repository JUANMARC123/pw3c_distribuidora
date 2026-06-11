<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->unsignedBigInteger('id_usuario');
            $table->tinyInteger('id_accion')->unsigned();
            $table->tinyInteger('id_tabla')->unsigned();
            $table->unsignedBigInteger('registro_id');
            $table->timestamp('fecha_hora')->useCurrent();

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_accion')
                  ->references('id_accion')
                  ->on('acciones')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_tabla')
                  ->references('id_tabla')
                  ->on('tablas_sistema')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditorias');
    }
};
