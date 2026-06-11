<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('repartidores', function (Blueprint $table) {
            $table->id('id_repartidor');
            $table->unsignedBigInteger('id_usuario');
            $table->string('ci', 20)->unique();
            $table->tinyInteger('id_extension_ci')->unsigned();
            $table->tinyInteger('id_licencia')->unsigned();
            $table->tinyInteger('id_estado_repartidor')->unsigned();

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_extension_ci')
                  ->references('id_extension_ci')
                  ->on('extensiones_ci')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_licencia')
                  ->references('id_licencia')
                  ->on('licencias')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_repartidor')
                  ->references('id_estado_repartidor')
                  ->on('estados_repartidor')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repartidores');
    }
};
