<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_ruta_paradas', function (Blueprint $table) {
            $table->id('id_control_ruta_parada');
            $table->unsignedBigInteger('id_control_ruta');
            $table->unsignedBigInteger('id_pedido')->nullable();
            $table->unsignedBigInteger('id_farmacia');
            $table->integer('orden_parada');
            $table->time('hora_estimada')->nullable();
            $table->time('hora_llegada_real')->nullable();
            $table->timestamps();

            $table->foreign('id_control_ruta')
                ->references('id_control_ruta')
                ->on('control_rutas')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_pedido')
                ->references('id_pedido')
                ->on('pedidos')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_farmacia')
                ->references('id_farmacia')
                ->on('farmacias')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->unique(['id_control_ruta', 'orden_parada'], 'control_ruta_paradas_control_orden_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_ruta_paradas');
    }
};
