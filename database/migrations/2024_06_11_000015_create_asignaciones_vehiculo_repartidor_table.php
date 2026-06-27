<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_vehiculo_repartidor', function (Blueprint $table) {
            $table->id('id_asignacion');
            $table->unsignedBigInteger('id_repartidor');
            $table->unsignedBigInteger('id_vehiculo');
            $table->dateTime('fecha_inicio')->useCurrent();
            $table->dateTime('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_repartidor')
                ->references('id_repartidor')
                ->on('repartidores')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')
                ->on('vehiculos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index(['id_repartidor', 'activo'], 'asignaciones_repartidor_activo_index');
            $table->index(['id_vehiculo', 'activo'], 'asignaciones_vehiculo_activo_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_vehiculo_repartidor');
    }
};
